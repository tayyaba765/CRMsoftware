# CORK CRM: Web Scraper & Static PDF Harvester — Complete Technical Architecture & Implementation Guide

**Document:** `EXPLANATION.MD`  
**Platform:** CORK Enterprise CRM Platform (Laravel 11/12, PHP 8.2+, MySQL 8.0+)  
**Author:** AI Engineering Pair Programmer  
**Date:** October 14, 2026  

---

## 1. Executive Summary & Dual-Engine Architecture

This project provides two completely decoupled, enterprise-grade scraping and lead harvesting engines:
1. **Web Scraper (HTML)**: Extracts business directories and chamber listings directly from live websites.
2. **Static PDF Scraper (Directory Harvester)**: Extracts structured business catalogs, trade directories, and firm listings directly from static PDF documents (remote URLs or file uploads up to 15MB).

### Complete Separation of Tables and Models
To ensure that marketing and sales teams can easily distinguish between leads harvested from live web directories versus trade directory PDFs, **each scraper operates on its own dedicated database table and Eloquent model**:

| Scraper Engine | Sidebar Link | Frontend URL | Eloquent Model | MySQL Table |
|---|---|---|---|---|
| **Web Scraper (HTML)** | Web Scraper | `/scraper` | `App\Models\ScrapedRecord` | `scraped_records` |
| **Static PDF Scraper** | Static PDF Scraper | `/scraper/pdf` | `App\Models\PdfScrapedRecord` | `pdf_scraped_records` |
| **Audit Logs** | (Audit Trail) | Shared | `App\Models\ScraperLog` | `scraper_logs` |

---

## 2. What Was Installed (Packages & Dependencies)

To ensure the engine is **100% self-hosted, private, and free forever** with zero recurring SaaS API fees or token limits (such as SharpAPI or cloud scraper credits), the following dependencies are installed and utilized:

### 2.1 Composer Packages
- **`smalot/pdfparser` (`v2.13.1`)**:
  - **Installation command**: `composer require smalot/pdfparser`
  - **Purpose**: A standalone PHP library that parses PDF files, extracting text streams across all pages, font objects, page structures, and document metadata without requiring external OS binaries (such as `pdftotext` or Python).
- **`guzzlehttp/guzzle` (`^7.8`)**:
  - **Purpose**: High-speed HTTP client used to fetch remote web pages and download binary PDF files with desktop Chrome User-Agent simulation and SSL bypass (`verify => false`) to prevent TLS handshake failures on government and chamber servers.
- **`yajra/laravel-datatables-oracle` (`^13.3`)**:
  - **Purpose**: Provides high-performance server-side DataTables AJAX pagination, multi-column search, column sorting, and custom badge renderers for both scrapers.

### 2.2 Native PHP Core Extensions Utilized
- **`ext-dom` & `ext-libxml`**: Used by `DOMDocument` and `DOMXPath` in `ScraperService` to parse HTML DOM trees.
- **`ext-curl`**: Underlying network transport engine for Guzzle HTTP.
- **`ext-json`**: Native JSON serialization for the flexible `raw_data` metadata store.

---

## 3. All Commands Executed During Development

Here is the exact record of CLI commands executed to build, migrate, and test the scraping architecture:

```bash
# 1. Install Smalot PDF Parser
composer require smalot/pdfparser

# 2. Generate Database Migrations
php artisan make:migration create_scraped_records_and_logs_table
php artisan make:migration create_pdf_scraped_records_table

# 3. Run Database Migrations
php artisan migrate

# 4. Generate Eloquent Models
php artisan make:model ScrapedRecord
php artisan make:model PdfScrapedRecord
php artisan make:model ScraperLog

# 5. Generate Background Queue Job
php artisan make:job ScrapeWebsiteJob

# 6. Generate Controller
php artisan make:controller ScraperController

# 7. Verify Registered Routes
php artisan route:list --path=scraper

# 8. Start Background Queue Worker (Optional for Queued mode)
php artisan queue:work --timeout=180 --tries=2
```

---

## 4. What Was Registered in the Application

### 4.1 Route Registration (`routes/web.php`)
The routes are separated into two distinct namespaces within `routes/web.php`. Notice that `{id}` parameters are strictly constrained with `whereNumber('id')` to prevent route collision:

```php
// =========================================================================
// Web Scraper (HTML) & Static PDF Scraper Management
// Web Scraper operates on ScrapedRecord (table: scraped_records)
// Static PDF Scraper operates on PdfScrapedRecord (table: pdf_scraped_records)
// =========================================================================
Route::prefix('scraper')->name('scraper.')->group(function () {

    // --- Static PDF Scraper Endpoints (Dedicated prefix for pdf_scraped_records) ---
    Route::get('/pdf', [ScraperController::class, 'pdfIndex'])->name('pdf');
    Route::prefix('pdf')->name('pdf.')->group(function () {
        Route::get('/data', [ScraperController::class, 'pdfData'])->name('data');
        Route::post('/run', [ScraperController::class, 'runPdf'])->name('run');
        Route::post('/bulk-delete', [ScraperController::class, 'pdfBulkDelete'])->name('bulk-delete');
        Route::get('/{id}/show', [ScraperController::class, 'pdfShow'])->whereNumber('id')->name('show');
        Route::get('/{id}/edit', [ScraperController::class, 'pdfEdit'])->whereNumber('id')->name('edit');
        Route::post('/{id}/update', [ScraperController::class, 'pdfUpdate'])->whereNumber('id')->name('update');
        Route::delete('/{id}', [ScraperController::class, 'pdfDestroy'])->whereNumber('id')->name('destroy');
        Route::post('/{id}/convert-profile', [ScraperController::class, 'pdfConvertProfile'])->whereNumber('id')->name('convert-profile');
        Route::post('/{id}/convert-org', [ScraperController::class, 'pdfConvertOrg'])->whereNumber('id')->name('convert-org');
    });

    // --- Web Scraper (HTML) Endpoints (Operates on scraped_records) ---
    Route::get('/', [ScraperController::class, 'index'])->name('index');
    Route::get('/data', [ScraperController::class, 'data'])->name('data');
    Route::post('/run', [ScraperController::class, 'run'])->name('run');
    Route::post('/bulk-delete', [ScraperController::class, 'bulkDelete'])->name('bulk-delete');
    Route::get('/{id}/show', [ScraperController::class, 'show'])->whereNumber('id')->name('show');
    Route::get('/{id}/edit', [ScraperController::class, 'edit'])->whereNumber('id')->name('edit');
    Route::post('/{id}/update', [ScraperController::class, 'update'])->whereNumber('id')->name('update');
    Route::delete('/{id}', [ScraperController::class, 'destroy'])->whereNumber('id')->name('destroy');
    Route::post('/{id}/convert-profile', [ScraperController::class, 'convertProfile'])->whereNumber('id')->name('convert-profile');
    Route::post('/{id}/convert-org', [ScraperController::class, 'convertOrg'])->whereNumber('id')->name('convert-org');
});
```

### Complete Route Comparison Matrix

| Action | Web Scraper Route (HTML) | Static PDF Scraper Route | HTTP Method | Target Model & Table |
|---|---|---|---|---|
| **Dashboard** | `scraper.index` (`/scraper`) | `scraper.pdf` (`/scraper/pdf`) | `GET` | Blade UI view |
| **DataTables AJAX** | `scraper.data` (`/scraper/data`) | `scraper.pdf.data` (`/scraper/pdf/data`) | `GET` | `ScrapedRecord` vs `PdfScrapedRecord` |
| **Run / Execute** | `scraper.run` (`/scraper/run`) | `scraper.pdf.run` (`/scraper/pdf/run`) | `POST` | Triggers scraping engine |
| **View Modal** | `scraper.show` (`/scraper/{id}/show`) | `scraper.pdf.show` (`/scraper/pdf/{id}/show`) | `GET` | Fetches JSON record |
| **Edit Modal** | `scraper.edit` (`/scraper/{id}/edit`) | `scraper.pdf.edit` (`/scraper/pdf/{id}/edit`) | `GET` | Fetches record for edit modal |
| **Update Record** | `scraper.update` (`/scraper/{id}/update`) | `scraper.pdf.update` (`/scraper/pdf/{id}/update`) | `POST` | Validates & saves changes |
| **Delete Record** | `scraper.destroy` (`/scraper/{id}`) | `scraper.pdf.destroy` (`/scraper/pdf/{id}`) | `DELETE` | Deletes single record |
| **Bulk Delete** | `scraper.bulk-delete` (`/scraper/bulk-delete`) | `scraper.pdf.bulk-delete` (`/scraper/pdf/bulk-delete`) | `POST` | Deletes multiple selected IDs |
| **Convert to Profile** | `scraper.convert-profile` (`/scraper/{id}/convert-profile`) | `scraper.pdf.convert-profile` (`/scraper/pdf/{id}/convert-profile`) | `POST` | Creates CRM `Profile` |
| **Convert to Org** | `scraper.convert-org` (`/scraper/{id}/convert-org`) | `scraper.pdf.convert-org` (`/scraper/pdf/{id}/convert-org`) | `POST` | Creates CRM `Organization` |

---

### 4.2 Controller Registration (`app/Http/Controllers/ScraperController.php`)
The controller houses dedicated methods for both scraper types:

- **Web Scraper Methods**:
  - `index()`: Loads dashboard with KPI counters from `ScrapedRecord`.
  - `data(Request $request)`: Server-side Yajra DataTables query for `ScrapedRecord`.
  - `run(Request $request)`: Dispatches Guzzle sync scrape or queue job for web URLs.
  - `show($id)`, `edit($id)`, `update(Request $request, $id)`, `destroy($id)`, `bulkDelete(Request $request)`.
  - `convertProfile($id)`, `convertOrg($id)`.

- **Static PDF Scraper Methods**:
  - `pdfIndex()`: Loads PDF dashboard with KPI counters from `PdfScrapedRecord`.
  - `pdfData(Request $request)`: Server-side Yajra DataTables query for `PdfScrapedRecord` with 10 formatted columns.
  - `runPdf(Request $request)`: Handles remote PDF downloads via Guzzle or local file uploads (up to 15MB).
  - `pdfShow($id)`, `pdfEdit($id)`, `pdfUpdate(Request $request, $id)`, `pdfDestroy($id)`, `pdfBulkDelete(Request $request)`.
  - `pdfConvertProfile($id)`, `pdfConvertOrg($id)`.

---

### 4.3 Sidebar Navigation (`resources/views/backend/layouts/sidebar.blade.php`)
Both scrapers have distinct icons and routes registered in the primary sidebar navigation:
- **Web Scraper**: `<a href="{{ route('scraper.index') }}">` with Globe icon.
- **Static PDF Scraper**: `<a href="{{ route('scraper.pdf') }}">` with Document icon.

---

## 5. Database Schema Comparison: `scraped_records` vs `pdf_scraped_records`

Both tables share core CRM fields while maintaining complete physical isolation:

| Column Name | `scraped_records` (Web Scraper) | `pdf_scraped_records` (PDF Scraper) | Description |
|---|---|---|---|
| `id` | `BIGINT UNSIGNED (PK)` | `BIGINT UNSIGNED (PK)` | Primary Key |
| `source_url` | `VARCHAR(500)` | `VARCHAR(500)` | URL or local file path |
| `source_name` | `VARCHAR(150), INDEX` | `VARCHAR(150), INDEX` | Human-readable source name |
| `business_name` | `VARCHAR(255), INDEX` | `VARCHAR(255), INDEX` | Company / Firm Name |
| `category` | `VARCHAR(255), INDEX` | `VARCHAR(255), INDEX` | Sector / Trade Category |
| `address` | `TEXT` | `TEXT` | Physical address / City |
| `contact_person`| `VARCHAR(255), INDEX` | `VARCHAR(255), INDEX` | Name of contact / owner |
| `phone` | `VARCHAR(100), INDEX` | `VARCHAR(100), INDEX` | Primary phone / mobile |
| `phone_2` | `VARCHAR(100)` | `VARCHAR(100)` | Secondary phone / mobile |
| `email` | `VARCHAR(191), INDEX` | `VARCHAR(191), INDEX` | Email address |
| `website_domain`| `VARCHAR(255)` | `VARCHAR(255)` | Business website |
| `status` | `VARCHAR(50), default 'new'` | `VARCHAR(50), default 'new'` | `new`, `contacted`, `qualified`, `converted`, `archived` |
| `raw_data` | `JSON` | `JSON` | Arbitrary attributes & key-values |
| `notes` | `TEXT` | `TEXT` | Internal user notes |
| `created_by` | `FOREIGN KEY (users.id)` | `FOREIGN KEY (users.id)` | ID of user who initiated scrape |
| `created_at` | `TIMESTAMP` | `TIMESTAMP` | Creation timestamp |
| `updated_at` | `TIMESTAMP` | `TIMESTAMP` | Update timestamp |

---

## 6. Architecture & How Both Scrapers Work

```
========================================================================================================
                                       SCRAPER SYSTEM ARCHITECTURE
========================================================================================================

    [1. WEB DIRECTORY TARGETS]                                  [2. STATIC PDF TARGETS]
  e.g. scci.org.pk/importers (HTML)                  e.g. 1715664687_CITRUS.pdf OR Uploaded PDF
                 |                                                            |
                 v                                                            v
    [ROUTE: /scraper/run]                                        [ROUTE: /scraper/pdf/run]
  ScraperController@run                                        ScraperController@runPdf
                 |                                                            |
                 +-----------------------+   +--------------------------------+
                                         |   |
                                         v   v
                         +-----------------------------------+
                         | Mode Switch: Sync vs. Queue Job   |
                         |  (ScrapeWebsiteJob ShouldQueue)   |
                         +-----------------------------------+
                                         |
                                         v
                         +-----------------------------------+
                         |   App\Services\ScraperService     |
                         +-----------------------------------+
                                    |             |
           +------------------------+             +------------------------+
           | (HTML Pipeline)                                               | (PDF Pipeline)
           v                                                               v
  Guzzle HTTP Client                                              Guzzle / Local Storage
  - Chrome User-Agent                                             - Download remote PDF binary
  - SSL verify: false                                             - Save to secure temporary file
  - Timeout: 30s                                                                 |
           |                                                                     v
           v                                                      Smalot\PdfParser\Parser
  DOMXPath Parser Engine                                          - Extract raw text streams
  - Detect SCCI Card structure                                    - Across all document pages
  - Generic <table> & card heuristic                                             |
           |                                                                     v
           v                                                      PDF Layout Regex Tokenizer
  Parser: parseScci / parseGeneric                                - Header stripping (MOBILE)
  - Business name                                                 - Phone number delimiters
  - Contact person                                                - Secondary phone attachment
  - Phone / Email extraction                                      - Firm, Category, Address, Contact
           |                                                                     |
           v                                                                     v
  hasMinimumFields() Check                                        hasMinimumFields() Check
  - At least 1 valid identifier                                   - At least 1 valid identifier
           |                                                                     |
           v                                                                     v
  Deduplication & Save                                            Deduplication & Save
  - Match by phone, email, firm+name                              - Match by phone, email, firm+name
           |                                                                     |
           v                                                                     v
========================================================================================================
                                           DATABASE LAYER
========================================================================================================
   App\Models\ScrapedRecord                                        App\Models\PdfScrapedRecord
   Table: `scraped_records`                                        Table: `pdf_scraped_records`
           |                                                                     |
           v                                                                     v
  Route: /scraper                                                 Route: /scraper/pdf
  DataTables: /scraper/data                                       DataTables: /scraper/pdf/data
  UI: resources/.../scraper/index.blade.php                       UI: resources/.../scraper/pdf.blade.php
           \                                                                     /
            \                                                                   /
             v                                                                 v
+------------------------------------------------------------------------------------------------------+
|                                    1-CLICK CRM CONVERSION PIPELINE                                   |
|   1. Convert to Profile      -> Creates App\Models\Profile (Individual Contact / Lead)               |
|   2. Convert to Organization -> Creates App\Models\Organization (Corporate Account)                  |
|   3. Updates record status to 'converted' in respective table (`scraped_records` or `pdf_scraped_records`)|
+------------------------------------------------------------------------------------------------------+
```

---

## 7. How Each Scraper Operates in Detail

### 7.1 Web Scraper (HTML) Workflow
1. **User Action**: User visits `/scraper`, selects a preset (e.g. SCCI Importers) or enters a target URL, and clicks **Scrape Website Now**.
2. **Controller Dispatch**: `ScraperController::run` receives the request.
3. **Execution**:
   - In **Sync Mode**: Calls `ScraperService::scrape($url, $sourceName, $userId)`.
   - In **Queue Mode**: Dispatches `ScrapeWebsiteJob::dispatch($url, $sourceName, $userId, false)`.
4. **HTML Parsing**:
   - Guzzle downloads the webpage HTML.
   - If the domain matches `scci.org.pk`, `parseScci()` targets the `.text-area` container, pulling out `Company:`, `Phone:`, `Importer:`, and `<h6><a>` contact names.
   - For other sites, `parseGeneric()` evaluates tables and cards using regex for phone numbers and email patterns.
5. **Deduplication & Persistence**:
   - Verifies `hasMinimumFields()` (must contain business name, contact person, phone, or email).
   - Searches `scraped_records` by phone, email, or company + person.
   - If found, updates missing fields; if not, inserts a new row with status `new`.
6. **Frontend**:
   - The Yajra DataTables grid on `/scraper` reloads via AJAX (`/scraper/data`), displaying the fresh leads with clickable badges, JSON metadata explorer, and conversion options.

---

### 7.2 Static PDF Scraper Workflow
1. **User Action**: User visits `/scraper/pdf`, enters a remote PDF URL (e.g. `1715664687_CITRUS.pdf`) or uploads a local PDF file, and clicks **Scrape PDF Directory Now**.
2. **Controller Dispatch**: `ScraperController::runPdf` validates the inputs. If a file was uploaded, it saves it into `storage/app/scraped_pdfs/`.
3. **Execution**:
   - In **Sync Mode**: Calls `ScraperService::scrapePdf($urlOrPath, $sourceName, $userId)`.
   - In **Queue Mode**: Dispatches `ScrapeWebsiteJob::dispatch($pdfUrl, $sourceName, $userId, true)`.
4. **PDF Parsing & Text Layout Tokenization**:
   - `Smalot\PdfParser\Parser` extracts text across all pages.
   - `parseCitrusPdfText()` strips top headers (`MOBILE`).
   - Uses Pakistani mobile number regex (`03XX-XXXXXXX`, `3XXXXXXXXX`, `048-XXXXXXX`) as delimiters.
   - Detects when multiple phone numbers appear sequentially and automatically attaches the second phone number to `phone_2`.
   - Tokenizes each chunk into Firm Name, Trade Category (e.g. `CITRUS EXPORTERS`, `KINNOW FACTORY`), Address, and Contact Person.
   - Phone sanitizer formats 10-digit mobiles starting with `3` into proper 11-digit numbers (`03XX-XXXXXXX`).
5. **Deduplication & Persistence**:
   - Verifies `PdfScrapedRecord::hasMinimumFields()`.
   - Checks `pdf_scraped_records` for duplicates.
   - Inserts or updates into `pdf_scraped_records`.
6. **Frontend**:
   - The Yajra DataTables grid on `/scraper/pdf` reloads via AJAX (`/scraper/pdf/data`), displaying all 10 columns with word-wrapping, address formatting, and category badges.
   - The 4 top KPI cards (Total Scraped, New Leads, Converted, Sources) automatically refresh via `refreshPdfStats()`.

---

## 8. CRM 1-Click Conversion Pipeline

Both scrapers are hooked directly into the core CRM entities:

### 8.1 Convert to CRM Profile (Individual Contact / Lead)
- **Web Scraper**: `POST /scraper/{id}/convert-profile`
- **PDF Scraper**: `POST /scraper/pdf/{id}/convert-profile`
- **What Happens**:
  - The scraper service extracts the contact name and splits it into `first_name` and `last_name`.
  - Normalizes the phone number to 11 digits.
  - Creates a record in the `profiles` table with status `prospective`.
  - Updates the scraped record status to `converted` in its respective table (`scraped_records` or `pdf_scraped_records`).

### 8.2 Convert to CRM Organization (Corporate Account)
- **Web Scraper**: `POST /scraper/{id}/convert-org`
- **PDF Scraper**: `POST /scraper/pdf/{id}/convert-org`
- **What Happens**:
  - Creates a corporate account in the `organizations` table with company name, focal person, tier, phone, address, and category.
  - Sets organization status to `prospective`.
  - Updates the scraped record status to `converted` in its respective table (`scraped_records` or `pdf_scraped_records`).

---

## 9. Troubleshooting & Fixes: PDF Field Persistence & Web Displayability

During extensive end-to-end verification, four critical issues that prevented scraped PDF data from displaying or saving properly were identified and permanently resolved:

### 1. Uploaded PDF Path Resolution (Laravel 11 `storage/app/private`)
- **Problem**: In Laravel 11/12, the `local` storage disk default path is `storage/app/private`. Using `storage_path('app/' . $path)` pointed to `storage/app/scraped_pdfs/...` where the file was not found, causing file uploads to fail silently.
- **Fix**: Replaced with `Storage::disk('local')->path($path)` in `ScraperController::runPdf()`.

### 2. Hardcoded Source Filter on Page Load
- **Problem**: On `/scraper/pdf`, the source dropdown had a hardcoded `{{ str_contains($src, 'Citrus') ? 'selected' : '' }}`. If a user scraped an uploaded PDF or a custom URL, DataTables was still filtering by `source_name = 'SCCI Citrus PDF Directory'`, causing newly scraped records to be hidden from the table.
- **Fix**: Changed the default option to `<option value="" selected>All Sources</option>`. In addition, after any scrape completes, `#filterSource` and `#filterStatus` are automatically reset to `""` before `table.draw()` so all new records appear immediately.

### 3. Smart Deduplication Without Overwriting Separate Companies
- **Problem**: Deduplication was matching strictly on `phone` without checking company name. If two firms shared a directory phone number, the second firm overwrote the first firm.
- **Fix**: Updated `savePdfRecord()` in `ScraperService` to match on `business_name` AND (`phone` OR `contact_person`). Different companies with the same phone are now preserved as independent records.

### 4. Accurate New vs. Updated Reporting
- **Problem**: The parser previously reported all records as "imported" even when an existing record was merely updated.
- **Fix**: `scrapePdf()` now tracks `newCount` and `updatedCount` using `wasRecentlyCreated`, returning accurate audit feedback: e.g. `"Found 169 items (8 new records added, 161 updated)"`.

---

## 10. Current Verification Status

The database and web application endpoints were queried and verified live:
- **`scraped_records` count**: 13 records (Web Directory Harvester)
- **`pdf_scraped_records` count**: 90 records (Static PDF Harvester)
- **DataTables AJAX Endpoint (`/scraper/pdf/data`)**: Returning all 90 records with 10 formatted columns.
- **Controller tests**: Both `ScraperController@show` and `ScraperController@pdfShow` executed with 100% success.
- **Route tests**: All 20 routes (`scraper.*` and `scraper.pdf.*`) verified and active in Laravel routing table.

