<?php

namespace App\Console\Commands;

use App\Services\PurgeService;
use Illuminate\Console\Command;

class ProcessPurgeQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purge:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process the next pending tweet purge request';

    /**
     * Execute the console command.
     */
    public function handle(PurgeService $purgeService): int
    {
        $this->info('Checking for pending purge requests...');

        // Get the next pending purge
        $purge = $purgeService->getNextPendingPurge();

        if (!$purge) {
            $this->info('No pending purge requests found.');
            return Command::SUCCESS;
        }

        $this->info("Processing purge for tweet {$purge->post_id}...");

        // Process the purge
        $success = $purgeService->processPurge($purge);

        if ($success) {
            $this->info("✓ Successfully processed purge for tweet {$purge->post_id}");
            return Command::SUCCESS;
        }

        $this->error("✗ Failed to process purge for tweet {$purge->post_id}");
        return Command::FAILURE;
    }
}
