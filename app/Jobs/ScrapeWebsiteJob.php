<?php

namespace App\Jobs;

use App\Services\ScraperService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ScrapeWebsiteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 180;
    public int $tries = 2;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $url,
        public ?string $sourceName = null,
        public ?int $userId = null,
        public bool $isPdf = false
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ScraperService $scraperService): void
    {
        Log::info("Starting background scrape job for: {$this->url} (PDF: " . ($this->isPdf ? 'yes' : 'no') . ")");
        if ($this->isPdf) {
            $result = $scraperService->scrapePdf($this->url, $this->sourceName, $this->userId);
        } else {
            $result = $scraperService->scrape($this->url, $this->sourceName, $this->userId);
        }
        Log::info("Finished background scrape job for {$this->url}", $result);
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        Log::error("ScrapeWebsiteJob failed for {$this->url}: " . ($exception ? $exception->getMessage() : 'Unknown error'));
    }
}
