<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\PdfScrapedRecord;
use App\Models\Profile;
use App\Models\ScrapedRecord;
use App\Models\ScraperLog;
use DOMDocument;
use DOMXPath;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ScraperService
{
    protected Client $client;

    public function __construct()
    {
        // 100% Free & Standalone Guzzle HTTP Client (No external paid APIs or keys needed)
        $this->client = new Client([
            'verify'          => false, // Avoid SSL failures on government/chamber websites
            'timeout'         => 30,
            'connect_timeout' => 15,
            'headers'         => [
                'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
            ],
        ]);
    }

    /**
     * Fetch raw HTML of a webpage using Guzzle.
     */
    public function fetchHtml(string $url): string
    {
        $response = $this->client->get($url);
        return (string) $response->getBody();
    }

    /**
     * Scrape a URL and save extracted records.
     */
    public function scrape(string $url, ?string $sourceName = null, ?int $userId = null): array
    {
        $sourceName = $sourceName ?: $this->detectSourceName($url);

        $log = ScraperLog::create([
            'url'              => $url,
            'source_name'      => $sourceName,
            'status'           => 'processing',
            'records_found'    => 0,
            'records_imported' => 0,
            'user_id'          => $userId,
        ]);

        try {
            $html = $this->fetchHtml($url);

            // Choose parser: SCCI parser if scci or generic directory parser
            if (str_contains(strtolower($url), 'scci.org.pk') || str_contains($html, 'scci') || str_contains($html, 'Sargodha Chamber')) {
                $parsed = $this->parseScci($html, $url, $sourceName);
            } else {
                $parsed = $this->parseGeneric($html, $url, $sourceName);
            }

            $imported = 0;
            foreach ($parsed as $item) {
                // Minimum requirement: at least one of business_name, contact_person, phone, or email
                if (!ScrapedRecord::hasMinimumFields($item)) {
                    continue;
                }

                $this->saveRecord($item, $url, $sourceName, $userId);
                $imported++;
            }

            $log->update([
                'status'           => 'completed',
                'records_found'    => count($parsed),
                'records_imported' => $imported,
            ]);

            return [
                'status'           => true,
                'message'          => "Scraped successfully! Found " . count($parsed) . " items and saved {$imported} records.",
                'records_found'    => count($parsed),
                'records_imported' => $imported,
            ];
        } catch (Exception $e) {
            Log::error('SCRAPER ERROR: ' . $e->getMessage());

            $log->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return [
                'status'           => false,
                'message'          => 'Scraping failed: ' . $e->getMessage(),
                'records_found'    => 0,
                'records_imported' => 0,
            ];
        }
    }

    /**
     * Scrape records from a PDF URL or local file path.
     */
    public function scrapePdf(string $urlOrPath, ?string $sourceName = null, ?int $userId = null): array
    {
        $isUrl = str_starts_with(strtolower($urlOrPath), 'http://') || str_starts_with(strtolower($urlOrPath), 'https://');
        $sourceName = $sourceName ?: ($isUrl ? $this->detectSourceName($urlOrPath) : 'Static PDF Document');

        $log = ScraperLog::create([
            'url'              => $urlOrPath,
            'source_name'      => $sourceName,
            'status'           => 'processing',
            'records_found'    => 0,
            'records_imported' => 0,
            'user_id'          => $userId,
        ]);

        $tempFile = null;

        try {
            if ($isUrl) {
                // Download PDF via Guzzle
                $response = $this->client->get($urlOrPath);
                $tempFile = tempnam(sys_get_temp_dir(), 'pdf_scrape_') . '.pdf';
                file_put_contents($tempFile, (string) $response->getBody());
                $filePath = $tempFile;
            } else {
                $filePath = $urlOrPath;
                if (!file_exists($filePath)) {
                    throw new Exception("PDF file not found at path: {$filePath}");
                }
            }

            // Parse PDF with Smalot
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();

            if (empty(trim($text))) {
                throw new Exception("Unable to extract text from the provided PDF. The file may be image-only (scanned).");
            }

            // Route to citrus parser if matching or generic PDF parser
            if (str_contains(strtoupper($text), 'CITRUS') || str_contains(strtoupper($text), 'KINNOW') || str_contains(strtoupper($urlOrPath), 'CITRUS')) {
                $parsed = $this->parseCitrusPdfText($text, $urlOrPath, $sourceName);
            } else {
                $parsed = $this->parseGenericPdfText($text, $urlOrPath, $sourceName);
            }

            $imported = 0;
            $newCount = 0;
            $updatedCount = 0;
            foreach ($parsed as $item) {
                if (!PdfScrapedRecord::hasMinimumFields($item)) {
                    continue;
                }

                $record = $this->savePdfRecord($item, $urlOrPath, $sourceName, $userId);
                if ($record->wasRecentlyCreated) {
                    $newCount++;
                } else {
                    $updatedCount++;
                }
                $imported++;
            }

            $log->update([
                'status'           => 'completed',
                'records_found'    => count($parsed),
                'records_imported' => $imported,
            ]);

            return [
                'status'           => true,
                'message'          => "PDF Scraped successfully! Found " . count($parsed) . " items ({$newCount} new records added, {$updatedCount} updated).",
                'records_found'    => count($parsed),
                'records_imported' => $imported,
                'records_new'      => $newCount,
                'records_updated'  => $updatedCount,
            ];
        } catch (Exception $e) {
            Log::error('PDF SCRAPER ERROR: ' . $e->getMessage());

            $log->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return [
                'status'           => false,
                'message'          => 'PDF Scraping failed: ' . $e->getMessage(),
                'records_found'    => 0,
                'records_imported' => 0,
            ];
        } finally {
            if ($tempFile && file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }
    }

    /**
     * Parser for SCCI Citrus / Kinnow PDF Directory.
     */
/**
     * Parser for SCCI Citrus / Kinnow PDF Directory.
     */
    public function parseCitrusPdfText(string $text, string $sourceUrl, string $sourceName): array
    {
        $headerPos = stripos($text, 'MOBILE');
        if ($headerPos !== false) {
            $text = substr($text, $headerPos + 6);
        }

        // Improved phone pattern to match standard Pakistani mobile/landline numbers
        $phonePattern = '/(?:\b0?3\d{2}[-\s]?\d{7,8}\b|\b3\d{9}\b|\b048[-\s]?\d{6,7}\b)/';

        $rawParts = preg_split($phonePattern, $text, -1, PREG_SPLIT_OFFSET_CAPTURE);
        preg_match_all($phonePattern, $text, $phones);

        $parsedList = [];
        $phoneCount = count($phones[0]);

        for ($i = 0; $i < $phoneCount; $i++) {
            $chunk = trim($rawParts[$i][0] ?? '');
            $phone = trim($phones[0][$i] ?? '');

            // Secondary phone attachment for preceding record
            if (empty($chunk) || preg_match('/^[,;\/\s]+$/', $chunk)) {
                if (!empty($parsedList)) {
                    $lastIdx = count($parsedList) - 1;
                    if (empty($parsedList[$lastIdx]['phone_2'])) {
                        $parsedList[$lastIdx]['phone_2'] = $this->cleanPhoneNumber($phone);
                    }
                }
                continue;
            }

            // SAFEGUARD: If a chunk is abnormally large, it means a phone number was missing 
            // and multiple records got merged. We split or truncate it safely.
            if (strlen($chunk) > 300) {
                // Try to isolate the last business name or break it down into lines
                $lines = array_values(array_filter(array_map('trim', explode("\n", $chunk))));
                $chunk = array_pop($lines) ?? $chunk; // Keep only the tail end for the current record
            }

            $lines = array_values(array_filter(array_map('trim', explode("\n", $chunk))));

            $firmName = '';
            $category = 'CITRUS / KINNOW';
            $address = '';
            $contact = '';

            if (!empty($lines)) {
                $firmName = array_shift($lines);
                if (count($lines) > 0 && preg_match('/^(CITRUS|KINNOW|COLD STORAGE|GROWER|EXPORTERS?)/i', $lines[0])) {
                    $category = array_shift($lines);
                }
                if (count($lines) > 0) {
                    $contact = array_pop($lines); // Usually the last line is the person's name
                }
                $address = implode(', ', $lines);
            }

            // Clean string values and enforce character limits to prevent DB truncation
            $firmName = mb_substr(preg_replace('/\s+/', ' ', trim($firmName)), 0, 100);
            $category = mb_substr(preg_replace('/\s+/', ' ', trim($category)), 0, 100);
            $address  = mb_substr(preg_replace('/\s+/', ' ', trim($address)), 0, 255);
            $contact  = mb_substr(preg_replace('/\s+/', ' ', trim($contact)), 0, 100);

            // Clean phone (allow null if blank)
            $cleanPhone = !empty($phone) ? $this->cleanPhoneNumber($phone) : null;

            $parsedList[] = [
                'source_url'     => $sourceUrl,
                'source_name'    => $sourceName,
                'business_name'  => $firmName ?: 'Unknown Business',
                'category'       => $category ?: 'CITRUS / KINNOW',
                'address'        => $address,
                'contact_person' => $contact,
                'phone'          => $cleanPhone,
                'phone_2'        => null,
                'email'          => null,
                'website_domain' => null,
                'raw_data'       => [
                    'original_chunk' => $chunk,
                    'raw_phone'      => $phone,
                    'extracted_type' => 'pdf_citrus_directory',
                ],
            ];
        }

        return $parsedList;
    }

    /**
     * Generic PDF directory parser for business listings.
     */
    public function parseGenericPdfText(string $text, string $sourceUrl, string $sourceName): array
    {
        $phonePattern = '/(?:\b0?3\d{2}[-\s]?\d{7,8}\b|\b3\d{9}\b|\b048[-\s]?\d{6,7}\b)/';
        $parts = preg_split($phonePattern, $text, -1, PREG_SPLIT_OFFSET_CAPTURE);
        preg_match_all($phonePattern, $text, $phones);

        $parsedList = [];
        $count = count($phones[0]);

        for ($i = 0; $i < $count; $i++) {
            $chunk = trim($parts[$i][0] ?? '');
            $phone = trim($phones[0][$i] ?? '');

            if (empty($chunk) || preg_match('/^[,;\/\s]+$/', $chunk)) {
                if (!empty($parsedList)) {
                    $last = count($parsedList) - 1;
                    if (empty($parsedList[$last]['phone_2'])) {
                        $parsedList[$last]['phone_2'] = $this->cleanPhoneNumber($phone);
                    }
                }
                continue;
            }

            $lines = array_values(array_filter(array_map('trim', explode("\n", $chunk))));
            if (empty($lines)) continue;

            $firmName = array_shift($lines);
            $contact = count($lines) > 0 ? array_pop($lines) : null;
            $address = count($lines) > 0 ? implode(', ', $lines) : null;

            $email = null;
            if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $chunk, $mEmail)) {
                $email = $mEmail[0];
            }

            $parsedList[] = [
                'source_url'     => $sourceUrl,
                'source_name'    => $sourceName,
                'business_name'  => $firmName,
                'category'       => 'PDF Directory Lead',
                'address'        => $address,
                'contact_person' => $contact,
                'phone'          => $this->cleanPhoneNumber($phone),
                'phone_2'        => null,
                'email'          => $email,
                'website_domain' => null,
                'raw_data'       => [
                    'original_chunk' => $chunk,
                    'extracted_type' => 'pdf_generic_directory',
                ],
            ];
        }

        return $parsedList;
    }

    /**
     * Clean and normalize phone numbers (e.g. 10 digits starting with 3 -> 03...).
     */
    public function cleanPhoneNumber(?string $phone): ?string
    {
        if (!$phone) return null;
        $clean = preg_replace('/[^\d]/', '', $phone);
        if (strlen($clean) === 10 && str_starts_with($clean, '3')) {
            $clean = '0' . $clean;
        }
        return $clean;
    }

    /**
     * Parser for SCCI (Sargodha Chamber of Commerce & Industry) Importers/Exporters
     */
    public function parseScci(string $html, string $url, string $sourceName): array
    {
        $dom = new DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new DOMXPath($dom);

        $textAreas = $xpath->query('//div[contains(@class, "text-area")]');
        $records = [];

        foreach ($textAreas as $ta) {
            $data = [
                'source_url'     => $url,
                'source_name'    => $sourceName,
                'contact_person' => null,
                'business_name'  => null,
                'phone'          => null,
                'phone_2'        => null,
                'email'          => null,
                'category'       => null,
                'address'        => null,
                'website_domain' => null,
                'raw_data'       => [],
            ];

            // Contact Person Name
            $titleNode = $xpath->query('.//h6[contains(@class, "title")]//a | .//h6[contains(@class, "title")]', $ta)->item(0);
            if ($titleNode) {
                $name = trim($titleNode->textContent);
                if (!empty($name) && $name !== '#') {
                    $data['contact_person'] = $name;
                }
            }

            // Paragraph key-values
            $pNodes = $xpath->query('.//p', $ta);
            foreach ($pNodes as $p) {
                $txt = trim($p->textContent);
                if (preg_match('/Company:\s*(.*)/i', $txt, $m)) {
                    $data['business_name'] = trim($m[1]);
                } elseif (preg_match('/(?:Phone|Mobile|Tel|Cell):\s*(.*)/i', $txt, $m)) {
                    $val = trim($m[1]);
                    if (!$data['phone']) {
                        $data['phone'] = $val;
                    } else {
                        $data['phone_2'] = $val;
                    }
                } elseif (preg_match('/(?:Importer|Exporter|Category|Sector|Product):\s*(.*)/i', $txt, $m)) {
                    $data['category'] = trim($m[1]);
                } elseif (preg_match('/(?:Email|E-mail):\s*(.*)/i', $txt, $m)) {
                    $data['email'] = trim($m[1]);
                } elseif (preg_match('/(?:Address|Location):\s*(.*)/i', $txt, $m)) {
                    $data['address'] = trim($m[1]);
                } else {
                    if (preg_match('/^([^:]+):\s*(.*)$/u', $txt, $m)) {
                        $data['raw_data'][strtolower(trim($m[1]))] = trim($m[2]);
                    }
                }
            }

            if (ScrapedRecord::hasMinimumFields($data)) {
                $records[] = $data;
            }
        }

        return $records;
    }

    /**
     * Simple & Generic Parser for any directory website (HTML Tables, Cards, Key-values, Regex)
     */
    public function parseGeneric(string $html, string $url, string $sourceName): array
    {
        $dom = new DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new DOMXPath($dom);

        $records = [];

        // Check HTML tables first
        $tables = $xpath->query('//table');
        if ($tables->length > 0) {
            foreach ($tables as $table) {
                $rows = $xpath->query('.//tr', $table);
                if ($rows->length < 2) continue;

                $headers = [];
                $firstRow = $rows->item(0);
                $headerCells = $xpath->query('.//th | .//td', $firstRow);
                foreach ($headerCells as $idx => $cell) {
                    $headers[$idx] = strtolower(trim($cell->textContent));
                }

                for ($i = 1; $i < $rows->length; $i++) {
                    $row = $rows->item($i);
                    $cells = $xpath->query('.//td', $row);
                    if ($cells->length === 0) continue;

                    $item = [
                        'source_url'     => $url,
                        'source_name'    => $sourceName,
                        'contact_person' => null,
                        'business_name'  => null,
                        'phone'          => null,
                        'phone_2'        => null,
                        'email'          => null,
                        'category'       => null,
                        'address'        => null,
                        'website_domain' => null,
                        'raw_data'       => [],
                    ];

                    foreach ($cells as $cIdx => $cell) {
                        $val = trim($cell->textContent);
                        $col = $headers[$cIdx] ?? '';

                        if (str_contains($col, 'company') || str_contains($col, 'business')) {
                            $item['business_name'] = $val;
                        } elseif (str_contains($col, 'name') || str_contains($col, 'person')) {
                            $item['contact_person'] = $val;
                        } elseif (str_contains($col, 'phone') || str_contains($col, 'tel')) {
                            $item['phone'] = $val;
                        } elseif (str_contains($col, 'email')) {
                            $item['email'] = $val;
                        } elseif (str_contains($col, 'category') || str_contains($col, 'product')) {
                            $item['category'] = $val;
                        } elseif (str_contains($col, 'address') || str_contains($col, 'city')) {
                            $item['address'] = $val;
                        } else {
                            if ($col) $item['raw_data'][$col] = $val;
                        }
                    }

                    if (ScrapedRecord::hasMinimumFields($item)) {
                        $records[] = $item;
                    }
                }
            }
        }

        // Check card/box elements if no table found
        if (empty($records)) {
            $cards = $xpath->query('//div[contains(@class, "card") or contains(@class, "listing") or contains(@class, "box")]');
            foreach ($cards as $card) {
                $text = trim($card->textContent);
                if (strlen($text) < 15) continue;

                $item = [
                    'source_url'     => $url,
                    'source_name'    => $sourceName,
                    'contact_person' => null,
                    'business_name'  => null,
                    'phone'          => null,
                    'phone_2'        => null,
                    'email'          => null,
                    'category'       => null,
                    'address'        => null,
                    'website_domain' => null,
                    'raw_data'       => [],
                ];

                $h = $xpath->query('.//h1 | .//h2 | .//h3 | .//h4 | .//h5 | .//h6', $card)->item(0);
                if ($h) {
                    $item['business_name'] = trim($h->textContent);
                }

                if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text, $mEmail)) {
                    $item['email'] = $mEmail[0];
                }

                if (preg_match('/(?:\+92|0092|0)?\s*(?:3\d{2}[-\s]?\d{7}|[2-9]\d{1,3}[-\s]?\d{6,8})/', $text, $mPhone)) {
                    $item['phone'] = trim($mPhone[0]);
                }

                $lines = explode("\n", $text);
                foreach ($lines as $line) {
                    if (str_contains($line, ':')) {
                        $parts = explode(':', $line, 2);
                        $k = strtolower(trim($parts[0]));
                        $v = trim($parts[1]);
                        if ($v) {
                            if (str_contains($k, 'company')) $item['business_name'] = $v;
                            elseif (str_contains($k, 'name')) $item['contact_person'] = $v;
                            elseif (str_contains($k, 'phone')) $item['phone'] = $v;
                            elseif (str_contains($k, 'category')) $item['category'] = $v;
                            else $item['raw_data'][$k] = $v;
                        }
                    }
                }

                if (ScrapedRecord::hasMinimumFields($item)) {
                    $records[] = $item;
                }
            }
        }

        return $records;
    }

    /**
     * Save record to database with deduplication.
     */
    protected function saveRecord(array $data, string $url, string $sourceName, ?int $userId): ScrapedRecord
    {
        $query = ScrapedRecord::where('source_url', $url);

        if (!empty($data['phone'])) {
            $query->where('phone', $data['phone']);
        } elseif (!empty($data['email'])) {
            $query->where('email', $data['email']);
        } elseif (!empty($data['business_name']) && !empty($data['contact_person'])) {
            $query->where('business_name', $data['business_name'])
                  ->where('contact_person', $data['contact_person']);
        } else {
            $query->where('business_name', $data['business_name'] ?: ($data['contact_person'] ?? ''));
        }

        $existing = $query->first();

        if ($existing) {
            $existing->update([
                'business_name'  => $data['business_name'] ?: $existing->business_name,
                'contact_person' => $data['contact_person'] ?: $existing->contact_person,
                'phone'          => $data['phone'] ?: $existing->phone,
                'phone_2'        => $data['phone_2'] ?: $existing->phone_2,
                'email'          => $data['email'] ?: $existing->email,
                'category'       => $data['category'] ?: $existing->category,
                'address'        => $data['address'] ?: $existing->address,
                'raw_data'       => array_merge($existing->raw_data ?: [], $data['raw_data'] ?: []),
            ]);

            return $existing;
        }

        return ScrapedRecord::create([
            'source_url'     => $url,
            'source_name'    => $sourceName,
            'business_name'  => $data['business_name'] ?? null,
            'contact_person' => $data['contact_person'] ?? null,
            'phone'          => $data['phone'] ?? null,
            'phone_2'        => $data['phone_2'] ?? null,
            'email'          => $data['email'] ?? null,
            'category'       => $data['category'] ?? null,
            'address'        => $data['address'] ?? null,
            'website_domain' => $data['website_domain'] ?? null,
            'status'         => 'new',
            'raw_data'       => $data['raw_data'] ?? null,
            'created_by'     => $userId,
        ]);
    }

    /**
     * Auto-detect readable source name from URL.
     */
    protected function detectSourceName(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST) ?: $url;
        $host = preg_replace('/^www\./i', '', $host);

        if (str_contains($host, 'scci.org.pk')) {
            if (str_contains(strtolower($url), 'citrus')) return 'SCCI Citrus PDF Directory';
            if (str_contains($url, 'importers')) return 'SCCI Importers';
            if (str_contains($url, 'exporters')) return 'SCCI Exporters';
            return 'Sargodha Chamber of Commerce';
        }

        if (str_contains(strtolower($url), '.pdf')) {
            return 'Static PDF Document';
        }

        return ucfirst(explode('.', $host)[0]) . ' Directory';
    }

    /**
     * Save PDF record to pdf_scraped_records table with deduplication.
     */
    protected function savePdfRecord(array $data, string $url, string $sourceName, ?int $userId): PdfScrapedRecord
    {
        $bName = trim($data['business_name'] ?? '');
        $cPerson = trim($data['contact_person'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $email = trim($data['email'] ?? '');

        $query = PdfScrapedRecord::where('source_url', $url);

        if (!empty($bName) && !empty($phone)) {
            $query->where('business_name', $bName)->where('phone', $phone);
        } elseif (!empty($bName) && !empty($cPerson)) {
            $query->where('business_name', $bName)->where('contact_person', $cPerson);
        } elseif (!empty($bName)) {
            $query->where('business_name', $bName);
        } elseif (!empty($phone)) {
            $query->where('phone', $phone);
        } elseif (!empty($email)) {
            $query->where('email', $email);
        } else {
            $query->where('contact_person', $cPerson);
        }

        $existing = $query->first();

        if ($existing) {
            $existing->update([
                'business_name'  => $data['business_name'] ?: $existing->business_name,
                'category'       => $data['category'] ?: $existing->category,
                'address'        => $data['address'] ?: $existing->address,
                'contact_person' => $data['contact_person'] ?: $existing->contact_person,
                'phone'          => $data['phone'] ?: $existing->phone,
                'phone_2'        => $data['phone_2'] ?: $existing->phone_2,
                'email'          => $data['email'] ?: $existing->email,
                'raw_data'       => array_merge($existing->raw_data ?: [], $data['raw_data'] ?: []),
            ]);

            return $existing;
        }

        return PdfScrapedRecord::create([
            'source_url'     => $url,
            'source_name'    => $sourceName,
            'business_name'  => $data['business_name'] ?? null,
            'category'       => $data['category'] ?? null,
            'address'        => $data['address'] ?? null,
            'contact_person' => $data['contact_person'] ?? null,
            'phone'          => $data['phone'] ?? null,
            'phone_2'        => $data['phone_2'] ?? null,
            'email'          => $data['email'] ?? null,
            'website_domain' => $data['website_domain'] ?? null,
            'status'         => 'new',
            'raw_data'       => $data['raw_data'] ?? null,
            'created_by'     => $userId,
        ]);
    }

    /**
     * Convert scraped record to CRM Profile.
     */
    public function convertToProfile(ScrapedRecord|PdfScrapedRecord $record): Profile
    {
 $fullName = trim($record->contact_person ?: $record->business_name ?: 'Scraped Lead');
    $parts = explode(' ', $fullName, 2);
    
    // Safely constrain lengths to avoid database truncation errors
    $firstName = mb_substr($parts[0], 0, 50);
    $lastName  = mb_substr($parts[1] ?? 'Lead', 0, 50);

    // Handle phone safely when empty
$phone = null;
if (!empty($record->phone)) {
    $cleanedPhone = preg_replace('/[^0-9]/', '', $record->phone);
    if (!empty($cleanedPhone)) {
        $phone = strlen($cleanedPhone) > 11 ? substr($cleanedPhone, -11) : $cleanedPhone;
    }
}

        $profile = Profile::create([
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'phone'      => $phone,
            'email'      => $record->email,
            'source'     => 'In-person',
            'status'     => 'prospective',
            'address_1'  => $record->address,
            'job_type'   => 'private',
        ]);

        $record->update(['status' => 'converted']);

        return $profile;
    }

    /**
     * Convert scraped record to CRM Organization.
     */
    public function convertToOrganization(ScrapedRecord|PdfScrapedRecord $record): Organization
    {
        $orgName = $record->business_name ?: ($record->contact_person ? $record->contact_person . ' Co.' : 'Scraped Business');

        $phone = $record->phone ? preg_replace('/[^0-9]/', '', $record->phone) : null;
        if ($phone && strlen($phone) > 11) {
            $phone = substr($phone, -11);
        }

        $org = Organization::create([
            'name'              => $orgName,
            'size'              => '2-15',
            'source'            => 'In-person',
            'organization_type' => $record->category ?: 'General',
            'ownership_type'    => 'private',
            'phone'             => $phone,
            'address'           => $record->address,
            'tier'              => 'Tier 1',
            'status'            => 'prospective',
            'focal_person'      => $record->contact_person,
            'website_domain'    => $record->website_domain,
        ]);

        $record->update(['status' => 'converted']);

        return $org;
    }
}
