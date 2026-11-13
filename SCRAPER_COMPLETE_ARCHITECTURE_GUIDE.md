# CORK CRM: Complete Scraper Architecture & Static PDF Harvester Guide

**Document ID:** `SCRAPER_COMPLETE_ARCHITECTURE_GUIDE.md`  
**Generated:** October 14, 2026  
**Project:** CORK Enterprise CRM Platform (Laravel 11/12, PHP 8.3+, MySQL 8.0)

---

## 1. Executive Summary

This document provides a comprehensive technical breakdown of the **Web Scraper & Static PDF Harvester Architecture** engineered into the CRM platform.

The system empowers marketing and sales teams to automatically extract structured business leads from both **live dynamic websites** (HTML business directories, chamber of commerce websites) and **static document sources** (complex PDF trade directories, uploaded catalogs) without entering manual data. Extracted leads can be inspected, searched, filtered, edited, and converted with 1 click into active CRM **`Profile`** (Individual Contacts / Leads) or **`Organization`** (Corporate Accounts).

---

## 2. What Was Installed & Engine Dependencies

### 2.1 Dependencies Installed

To achieve a **100% free, self-hosted, and standalone** engine with zero recurring API costs or third-party subscription keys, the following components are utilized:

| Package / Library                 | Version                            | Purpose                                                                                                          | Free / Open-Source |
| --------------------------------- | ---------------------------------- | ---------------------------------------------------------------------------------------------------------------- | ------------------ |
| `guzzlehttp/guzzle`               | `^7.8` (bundled)                   | High-performance asynchronous HTTP client for fetching HTML and downloading binary PDF files                     | Yes (MIT)          |
| `smalot/pdfparser`                | `v2.13.1` (installed via Composer) | Standalone PHP PDF parser that extracts raw text streams, layout blocks, font metadata, and pages from PDF files | Yes (LGPL-3.0)     |
| `yajra/laravel-datatables-oracle` | `^13.3` (bundled)                  | Server-side AJAX data grid, column searching, custom badges, and sorting                                         | Yes (MIT)          |
| `ext-dom` & `ext-libxml`          | PHP Core                           | Native XML/HTML DOM parsing and XPath tree queries (`DOMDocument`, `DOMXPath`)                                   | Yes (Core)         |
| `ext-curl`                        | PHP Core                           | Underlying high-speed transport for Guzzle HTTP                                                                  | Yes (Core)         |
| `ext-json`                        | PHP Core                           | Native JSON serialization for `raw_data` metadata store                                                          | Yes (Core)         |

### 2.2 Why SharpAPI Was NOT Used

- **Evaluated & Rejected**: The user inquired about `sharpapi/laravel-web-scraping-api`. Investigation confirmed SharpAPI is a commercial SaaS API requiring paid subscription credits, account registration, external API keys, and rate limits.
- **In-House Solution Built**: We engineered our own proprietary parsers using native PHP, Guzzle, and `smalot/pdfparser`. This ensures:
    1. **Zero Recurring Costs**: Completely free forever.
    2. **Total Data Privacy**: No company data, phone numbers, or directory files are sent to external third-party cloud APIs.
    3. **No Rate Limits or Quotas**: Scrape thousands of records without hitting third-party API walls.
    4. **Custom Pattern Matching**: Tailored specifically for chamber of commerce listings, Pakistani phone number formats, and citrus/kinnow multi-line catalogs.

---

## 3. Architecture & System Flow

```
+-------------------------------------------------------------------------------------------------------+
|                                              DATA SOURCES                                             |
|  - Live Web Directories: https://www.scci.org.pk/importers | https://www.scci.org.pk/exporters        |
|  - Static PDF Documents: https://www.scci.org.pk/assets/img/categories/media/1715664687_CITRUS.pdf  |
|  - Uploaded PDF Files: Any local PDF directory catalog (up to 15MB)                                   |
+-------------------------------------------------------------------------------------------------------+
                                                   |
                                                   v
+-------------------------------------------------------------------------------------------------------+
|                                   CONTROLLER / ROUTING DISPATCH                                       |
|  - Route: GET  /scraper          -> ScraperController@index    (HTML Scraper Dashboard)               |
|  - Route: POST /scraper/run      -> ScraperController@run      (HTML Scraping Trigger)                |
|  - Route: GET  /scraper/pdf      -> ScraperController@pdfIndex (Static PDF Scraper Dashboard)         |
|  - Route: POST /scraper/pdf/run  -> ScraperController@runPdf   (Static PDF Scraping Trigger)          |
|  - Route: GET  /scraper/data     -> ScraperController@data     (Unified Yajra DataTables Endpoint)    |
+-------------------------------------------------------------------------------------------------------+
                                                   |
                        +--------------------------+--------------------------+
                        | (Mode: Sync)                                        | (Mode: Queue)
                        v                                                     v
         +-----------------------------+                       +-----------------------------+
         | Direct Controller Execution |                       |  ScrapeWebsiteJob (Queue)   |
         +-----------------------------+                       +-----------------------------+
                        |                                                     |
                        +--------------------------+--------------------------+
                                                   |
                                                   v
+-------------------------------------------------------------------------------------------------------+
|                                    ScraperService (CORE ENGINE)                                       |
|                                                                                                       |
|  [HTML Scraping Pipeline]                                                                             |
|    1. Guzzle client fetches HTML with Desktop User-Agent & SSL tolerance (`verify => false`)          |
|    2. Parser detection: `parseScci()` (card-based text-areas) OR `parseGeneric()` (tables/cards)     |
|                                                                                                       |
|  [PDF Scraping Pipeline]                                                                              |
|    1. Binary download via Guzzle (or direct local storage path)                                       |
|    2. Smalot PDF Parser extracts raw text streams across all pages                                    |
|    3. Parser detection: `parseCitrusPdfText()` (multi-line layout) OR `parseGenericPdfText()`         |
|    4. Phone delimiter extraction, secondary mobile pairing, and category/firm/contact separation     |
|                                                                                                       |
|  [Normalization & Deduplication]                                                                      |
|    1. Phone sanitizer: Normalizes 10-digit mobiles (`3166057598` -> `03166057598`)                    |
|    2. Minimum field validation: Requires at least 1 of (business_name, contact, phone, email)        |
|    3. Deduplication: Matches existing records by phone, email, or firm + person                       |
|    4. Arbitrary metadata captured in `raw_data` JSON column                                           |
+-------------------------------------------------------------------------------------------------------+
                                                   |
                                                   v
+-------------------------------------------------------------------------------------------------------+
|                                         DATABASE LAYER                                                |
|  - `scraped_records`: Central storage with full auditing, categories, phones, and raw metadata       |
|  - `scraper_logs`: Audit trail tracking URL, status, found count, imported count, errors              |
+-------------------------------------------------------------------------------------------------------+
                                                   |
                                                   v
+-------------------------------------------------------------------------------------------------------+
|                                    FRONTEND & CRM CONVERSION                                          |
|  - Responsive DataTables grid with multi-column search, status badges, and source filter              |
|  - View Modal with interactive Raw JSON Metadata Explorer                                             |
|  - Edit Modal with instant validation                                                                 |
|  - 1-Click Convert to Profile (`/scraper/{id}/convert-profile`)                                       |
|  - 1-Click Convert to Organization (`/scraper/{id}/convert-org`)                                      |
+-------------------------------------------------------------------------------------------------------+
```

---

## 4. What Services Do (`ScraperService.php`)

The [`App\Services\ScraperService`](file:///d:/laravel/oct8partnew/oct8/SEPT24/app/Services/ScraperService.php) is the central domain service that handles data extraction, cleaning, persistence, and CRM integration.

### 4.1 HTML Web Scraping Methods

- **`fetchHtml(string $url): string`**:
  Initializes a Guzzle HTTP client with a 30-second timeout, Chrome Desktop User-Agent, and SSL bypass (`verify => false`) to safely retrieve data from institutional or chamber websites without SSL handshaking failures.
- **`scrape(string $url, ?string $sourceName, ?int $userId): array`**:
  Orchestrates the HTML extraction. Creates an audit log in `scraper_logs`, passes the HTML to the appropriate parser, runs deduplication, stores valid records, and marks the log as `completed` or `failed`.
- **`parseScci(string $html, string $url, string $sourceName): array`**:
  Specialized DOM/XPath parser built for Sargodha Chamber of Commerce directories. Targets `.text-area` cards, extracting:
    - Contact Person: `<h6><a ...>`
    - Company / Firm Name: from `Company:` key-value lines
    - Phone: from `Phone:` key-value lines
    - Category / Importer Type: from `Importer:` or `Exporter:` lines
    - Unstructured lines and image links stored in `raw_data`.
- **`parseGeneric(string $html, string $url, string $sourceName): array`**:
  Heuristic fallback that scans generic HTML `<table>` elements and `.card` containers using regex to harvest emails (`/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/`) and phone numbers.

### 4.2 Static PDF Scraping Methods

- **`scrapePdf(string $urlOrPath, ?string $sourceName, ?int $userId): array`**:
  Accepts either a web URL (e.g. `https://www.scci.org.pk/.../CITRUS.pdf`) or a local file path (from file upload). If an HTTP URL is supplied, Guzzle downloads the binary to a secure temporary file. Then `Smalot\PdfParser\Parser` extracts the text across all pages.
- **`parseCitrusPdfText(string $text, string $sourceUrl, string $sourceName): array`**:
  Specialized multi-column text parser designed specifically for tabular PDF directories (like the SCCI Citrus / Kinnow Exporters catalog):
    - Strips page headers (`CITRUS / KINNOW`, `FIRM NAME CATEGORY ADDRESS NAME MOBILE`).
    - Uses phone numbers (`03XX-XXXXXXX`, `3XXXXXXXXX`, `048-XXXXXXX`) as record delimiters.
    - Detects secondary phone numbers (e.g. `3005703103, 3009603103`) and automatically assigns the second number to `phone_2`.
    - Splits paragraphs and lines into:
        - **FIRM NAME**: Top lines (e.g. `ABDULLAH AND CO.`, `ZOOM CITRUS`, `RAJA PERVAIZ`).
        - **CATEGORY**: Trade category (e.g. `CITRUS EXPORTERS`, `KINNOW EXPORTERS`, `CITRUS PROCESSING`, `KINNOW FACTORY`).
        - **ADDRESS**: Geographic address (e.g. `SABZI MANDI, SARGODHA`, `BLOCK G, ZAHOOR HAYAT COLONY, SARGODHA BHALWAL`).
        - **NAME (CONTACT PERSON)**: Bottom lines preceding mobile (e.g. `MUHAMMAD AMIR RAZA`, `MUHAMMAD NAEEM`, `SHEHZADA PERVEZ`).
- **`cleanPhoneNumber(?string $phone): ?string`**:
  Sanitizes phone numbers, stripping punctuation, spaces, and formatting 10-digit mobile numbers starting with `3` into standard 11-digit Pakistani mobile numbers (`03XX-XXXXXXX`).
- **`parseGenericPdfText(string $text, string $sourceUrl, string $sourceName): array`**:
  Fallback extractor for arbitrary business directory PDFs.

### 4.3 Persistence & CRM Conversion Methods

- **`saveRecord(array $data, string $url, string $sourceName, ?int $userId): ScrapedRecord`**:
  Executes deduplication against MySQL:
    1. Searches for existing record by `phone`.
    2. If no phone, searches by `email`.
    3. If no email, searches by `business_name` + `contact_person`.
    4. If found, updates missing fields and merges `raw_data` JSON.
    5. If new, creates a fresh record with status `new`.
- **`convertToProfile(ScrapedRecord $record): Profile`**:
  Splits contact name into first and last name, formats phone to 11 digits, sets lead status to `prospective`, creates a CRM [Profile](file:///d:/laravel/oct8partnew/oct8/SEPT24/app/Models/Profile.php), and marks the scraped record status as `converted`.
- **`convertToOrganization(ScrapedRecord $record): Organization`**:
  Formats company name, assigns focal person, phone, category, and address, sets organization status to `prospective`, creates a CRM [Organization](file:///d:/laravel/oct8partnew/oct8/SEPT24/app/Models/Organization.php), and marks the scraped record status as `converted`.

---

## 5. What Does the Queue Do? (Does Queue Do Something?)

### 5.1 Why Use Queues for Scraping?

When a user clicks "Scrape" on a website or large PDF document:

1. **Network Latency**: Downloading a remote website or a 16-page PDF takes several seconds.
2. **CPU & Memory Processing**: Parsing hundreds of DOM nodes or extracting thousands of PDF characters requires noticeable CPU time.
3. **HTTP Timeout Prevention**: Web servers (like Nginx, Apache, or PHP-FPM) have execution limits (typically 30 to 60 seconds). If a user scrapes a catalog containing 200+ records, a synchronous request could time out and throw a 504 Gateway Timeout error, leaving the user with an error screen.

### 5.2 How `ScrapeWebsiteJob` Works

The platform provides [`App\Jobs\ScrapeWebsiteJob`](file:///d:/laravel/oct8partnew/oct8/SEPT24/app/Jobs/ScrapeWebsiteJob.php), which implements Laravel's `ShouldQueue` interface:

```php
namespace App\Jobs;

class ScrapeWebsiteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 180; // 3-minute timeout protection
    public int $tries = 2;     // Automatic retry on network glitch

    public function __construct(
        public string $url,
        public ?string $sourceName = null,
        public ?int $userId = null,
        public bool $isPdf = false // Supports both HTML and PDF
    ) {}

    public function handle(ScraperService $scraperService): void
    {
        if ($this->isPdf) {
            $scraperService->scrapePdf($this->url, $this->sourceName, $this->userId);
        } else {
            $scraperService->scrape($this->url, $this->sourceName, $this->userId);
        }
    }
}
```

### 5.3 Queue vs. Sync Execution Comparison

| Feature                    | Instant Live Mode (Sync)                                                   | Background Queue Mode (Async)                                                            |
| -------------------------- | -------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------- |
| **Best Used For**          | Fast single-page crawls (e.g. SCCI Importers 14 records) or quick tests    | Multi-page crawls, large PDF catalogs (100+ records), slow servers                       |
| **User Experience**        | Spinner runs in browser; page updates with records immediately upon finish | Returns instant response (`"Job dispatched to background queue"`); browser never freezes |
| **Queue Worker Required?** | No (`php artisan serve` handles it immediately)                            | Yes (`php artisan queue:work` processes jobs in background)                              |
| **Timeout Safety**         | Bound to PHP `max_execution_time`                                          | Protected by job `$timeout = 180` seconds and automatic retry                            |

---

## 6. Database Design

Two dedicated tables were designed and created via migration [`2026_10_14_000001_create_scraped_records_and_logs_table.php`](file:///d:/laravel/oct8partnew/oct8/SEPT24/database/migrations/2026_10_14_000001_create_scraped_records_and_logs_table.php):

### 6.1 `scraped_records` Table

Stores extracted entities with a flexible minimum-field rule (records only need at least one identifier: `business_name`, `contact_person`, `phone`, or `email`):

| Column           | Type                   | Description                                                                  |
| ---------------- | ---------------------- | ---------------------------------------------------------------------------- |
| `id`             | `BIGINT UNSIGNED (PK)` | Auto-increment primary key                                                   |
| `source_url`     | `VARCHAR(500)`         | Full URL or file path where data originated                                  |
| `source_name`    | `VARCHAR(150)`         | Human-readable source label (e.g. `SCCI Citrus PDF Directory`)               |
| `business_name`  | `VARCHAR(255) NULL`    | Company / firm name                                                          |
| `contact_person` | `VARCHAR(255) NULL`    | Name of owner, director, or focal person                                     |
| `phone`          | `VARCHAR(100) NULL`    | Primary mobile / phone number (normalized)                                   |
| `phone_2`        | `VARCHAR(100) NULL`    | Secondary phone or mobile                                                    |
| `email`          | `VARCHAR(191) NULL`    | Email address                                                                |
| `category`       | `VARCHAR(255) NULL`    | Industry category (e.g. `CITRUS EXPORTERS`, `Tyres & Tubes`)                 |
| `address`        | `TEXT NULL`            | Physical business address                                                    |
| `website_domain` | `VARCHAR(255) NULL`    | Company website URL                                                          |
| `status`         | `VARCHAR(50)`          | Workflow status: `new`, `contacted`, `qualified`, `converted`, `archived`    |
| `raw_data`       | `JSON NULL`            | JSON store preserving all raw chunks, unmapped attributes, and source tokens |
| `notes`          | `TEXT NULL`            | Sales representative notes                                                   |
| `created_by`     | `BIGINT UNSIGNED NULL` | Foreign key referencing authenticated `users.id`                             |
| `timestamps`     | `TIMESTAMP`            | `created_at` and `updated_at`                                                |

### 6.2 `scraper_logs` Table

Maintains an immutable audit trail of every scraping run:

| Column             | Type                   | Description                                     |
| ------------------ | ---------------------- | ----------------------------------------------- |
| `id`               | `BIGINT UNSIGNED (PK)` | Primary key                                     |
| `url`              | `VARCHAR(500)`         | Target URL or file processed                    |
| `source_name`      | `VARCHAR(150)`         | Source label                                    |
| `status`           | `VARCHAR(50)`          | `processing`, `completed`, or `failed`          |
| `records_found`    | `INT`                  | Total count of raw items detected               |
| `records_imported` | `INT`                  | Total count of validated records stored/updated |
| `error_message`    | `TEXT NULL`            | Exception message if scraping failed            |
| `user_id`          | `BIGINT UNSIGNED NULL` | User who initiated the scrape                   |
| `timestamps`       | `TIMESTAMP`            | `created_at` and `updated_at`                   |

---

## 7. Complete List of Routes & Controller Endpoints

All routes are secured by Laravel's `auth` middleware inside [`routes/web.php`](file:///d:/laravel/oct8partnew/oct8/SEPT24/routes/web.php):

| HTTP Method | Route URL                       | Route Name                | Controller Action                  | Description                                    |
| ----------- | ------------------------------- | ------------------------- | ---------------------------------- | ---------------------------------------------- |
| `GET`       | `/scraper`                      | `scraper.index`           | `ScraperController@index`          | Main Web Scraper (HTML) dashboard view         |
| `GET`       | `/scraper/pdf`                  | `scraper.pdf`             | `ScraperController@pdfIndex`       | Dedicated Static PDF Scraper dashboard view    |
| `POST`      | `/scraper/run`                  | `scraper.run`             | `ScraperController@run`            | Execute live HTML scrape or dispatch queue     |
| `POST`      | `/scraper/pdf/run`              | `scraper.pdf.run`         | `ScraperController@runPdf`         | Execute live PDF scrape (URL or uploaded file) |
| `GET`       | `/scraper/data`                 | `scraper.data`            | `ScraperController@data`           | Server-side Yajra DataTables AJAX endpoint     |
| `GET`       | `/scraper/{id}/show`            | `scraper.show`            | `ScraperController@show`           | View single record & raw JSON metadata         |
| `GET`       | `/scraper/{id}/edit`            | `scraper.edit`            | `ScraperController@edit`           | Fetch record data for modal editing            |
| `POST`      | `/scraper/{id}/update`          | `scraper.update`          | `ScraperController@update`         | Save edited record with field validation       |
| `DELETE`    | `/scraper/{id}`                 | `scraper.destroy`         | `ScraperController@destroy`        | Delete a single scraped record                 |
| `POST`      | `/scraper/bulk-delete`          | `scraper.bulk-delete`     | `ScraperController@bulkDelete`     | Batch delete multiple selected records         |
| `POST`      | `/scraper/{id}/convert-profile` | `scraper.convert-profile` | `ScraperController@convertProfile` | Convert lead to CRM `Profile`                  |
| `POST`      | `/scraper/{id}/convert-org`     | `scraper.convert-org`     | `ScraperController@convertOrg`     | Convert business to CRM `Organization`         |

---

## 8. Frontend User Interfaces

### 8.1 HTML Web Scraper: [`resources/views/backend/scraper/index.blade.php`](file:///d:/laravel/oct8partnew/oct8/SEPT24/resources/views/backend/scraper/index.blade.php)

- Accessible from the sidebar under **Web Scraper**.
- Contains top tab navigation to switch to Static PDF Scraper.
- Preset dropdown (SCCI Importers, SCCI Exporters, or Custom URL).
- Live execution toggle (Instant Sync vs Background Queue).
- 4 real-time KPI metric cards (Total Scraped, New Leads, Converted, Sources).
- Server-side DataTables table with search, sorting, and pagination.

### 8.2 Static PDF Scraper: [`resources/views/backend/scraper/pdf.blade.php`](file:///d:/laravel/oct8partnew/oct8/SEPT24/resources/views/backend/scraper/pdf.blade.php)

- Accessible from the sidebar under **Static PDF Scraper** or via `/scraper/pdf`.
- Preset dropdown pre-populated with:
    - `https://www.scci.org.pk/assets/img/categories/media/1715664687_CITRUS.pdf` (SCCI Citrus / Kinnow Exporters Directory)
    - Custom PDF URL
    - Upload Local PDF Document
- Dynamic input switcher: Shows URL field for online links, or activates file upload for offline PDF files.
- Mode toggle: Instant Live Scrape vs Background Queue.
- Live DataTables table pre-filtered to PDF directory records with full search, view modal, edit modal, delete, and CRM conversion.

---

## 9. Verified Live Test Results

Both engines were thoroughly tested against live Sargodha Chamber of Commerce data sources:

### 9.1 HTML Scraper Verification (`https://www.scci.org.pk/importers`)

- **Found**: 14 business records
- **Imported**: 14 records
- **Sample Verified Entities**:
    - `Bhali Tyres` | Muhammad Imran Bhali | `0300-8703600` | Tyres & Tubes
    - `Atzan Pharmaceutical` | Basharat Ahmed | `0300-8602223` | Pharmaceutical
    - `Hasnat International` | Shakeel Irshad | `0321-2221627` | Tyres
    - `Harrison Pharmaceuticals` | Irfan Gulzar Anjum | `0322-9602646` | Pharmaceutical raw material

### 9.2 Static PDF Scraper Verification (`1715664687_CITRUS.pdf`)

- **Found**: 169 citrus & kinnow businesses across 16 PDF pages
- **Imported**: 169 records
- **Sample Verified Entities**:
    - `ABDULLAH AND CO.` | Muhammad Amir Raza | `03166057598` | `SABZI MANDI, SARGODHA` | Category: `CITRUS EXPORTERS`
    - `ZOOM CITRUS` | Muhammad Naeem | `03215526993` | `BLOCK G, ZAHOOR HAYAT COLONY, SARGODHA BHALWAL` | Category: `KINNOW EXPORTERS`
    - `PAK STAR PACKAGES` | Salman Ihsan | `03005703103` & `3009603103` (assigned to `phone_2`)
    - `AL REHMAN` | Muhammad Asif | `03336794422` | `16-KM BHALWAL, MARI LAK.` | Category: `CITRUS FACTORY`

### 9.3 CRM Conversion Verification

- **Record #48 (`ZOOM CITRUS`)**: Successfully converted to CRM **Organization #2** (`name = ZOOM CITRUS`, `phone = 03215526993`, `status = prospective`). Record status updated to `converted`.
- **Record #47 (`ABDULLAH AND CO.`)**: Successfully converted to CRM **Profile #2** (`full_name = MUHAMMAD AMIR RAZA`, `phone = 03166057598`, `status = prospective`). Record status updated to `converted`.

---

## 10. How to Use in the Browser

1. Start your local server if not already running: `php artisan serve`
2. Open your browser and navigate to:
    - **HTML Scraper**: `http://127.0.0.1:8000/scraper`
    - **Static PDF Scraper**: `http://127.0.0.1:8000/scraper/pdf`
3. To scrape the Citrus PDF:
    - Select the preset **"🍊 SCCI Citrus / Kinnow Exporters Directory (PDF)"**
    - Click **"Scrape PDF Directory Now"**
    - The table will update with all 169 records.
4. Click **"View"** to explore raw metadata, **"Edit"** to modify records, or **"Convert"** to turn any lead into an Organization or Profile!
