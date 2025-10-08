<?php

namespace App\Console\Commands;

use App\Models\Purge;
use Illuminate\Console\Command;

class PurgeBatchSave extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purge:batchsave {text : The text to search for in purge records}
                            {--case-sensitive : Perform a case-sensitive search}
                            {--regex : Treat the search text as a regular expression}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark purges as saved if their text contains the specified string';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $searchText = $this->argument('text');
        $caseSensitive = $this->option('case-sensitive');
        $useRegex = $this->option('regex');

        $searchType = $useRegex ? 'regex' : ($caseSensitive ? 'case-sensitive' : 'case-insensitive');
        $this->info("Searching for purges containing: \"{$searchText}\" ({$searchType})");

        $query = Purge::where('save', false);

        if ($useRegex) {
            // Get all purges and filter with PHP regex for database compatibility
            $purges = $query->get()->filter(function ($purge) use ($searchText) {
                return preg_match("/{$searchText}/", $purge->text);
            });
        } elseif ($caseSensitive) {
            // Get all purges and filter with PHP for case-sensitive search
            $purges = $query->get()->filter(function ($purge) use ($searchText) {
                return str_contains($purge->text, $searchText);
            });
        } else {
            $purges = $query->where('text', 'like', "%{$searchText}%")->get();
        }

        if ($purges->isEmpty()) {
            $this->warn('No purges found matching the search criteria.');
            return self::SUCCESS;
        }

        $this->info("Found {$purges->count()} purge(s) to mark as saved.");

        $bar = $this->output->createProgressBar($purges->count());
        $bar->start();

        $updated = 0;
        foreach ($purges as $purge) {
            $purge->update(['save' => true]);
            $updated++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Successfully marked {$updated} purge(s) as saved.");

        return self::SUCCESS;
    }
}
