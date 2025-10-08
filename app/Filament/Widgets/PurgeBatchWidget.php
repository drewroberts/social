<?php

namespace App\Filament\Widgets;

use App\Models\Purge;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class PurgeBatchWidget extends Widget
{
    protected string $view = 'filament.widgets.purge-batch-widget';

    protected int | string | array $columnSpan = 'full';

    public string $searchText = '';
    
    public string $operation = 'save';
    
    public bool $caseSensitive = false;
    
    public bool $useRegex = false;

    public function getSearchCount(): int
    {
        if (empty($this->searchText)) {
            return 0;
        }

        $query = Purge::where('save', $this->operation === 'save' ? false : true);

        if ($this->useRegex) {
            $purges = $query->get()->filter(function ($purge) {
                return preg_match("/{$this->searchText}/", $purge->text);
            });
            return $purges->count();
        } elseif ($this->caseSensitive) {
            $purges = $query->get()->filter(function ($purge) {
                return str_contains($purge->text, $this->searchText);
            });
            return $purges->count();
        }

        return $query->where('text', 'like', "%{$this->searchText}%")->count();
    }

    public function batchSave(): void
    {
        $this->executeBatchOperation('save');
    }

    public function batchUnsave(): void
    {
        $this->executeBatchOperation('unsave');
    }

    protected function executeBatchOperation(string $operation): void
    {
        if (empty($this->searchText)) {
            Notification::make()
                ->warning()
                ->title('No search text provided')
                ->body('Please enter text to search for.')
                ->send();
            return;
        }

        $targetSaveState = $operation === 'save' ? false : true;
        $newSaveState = $operation === 'save' ? true : false;
        
        $query = Purge::where('save', $targetSaveState);

        if ($this->useRegex) {
            $purges = $query->get()->filter(function ($purge) {
                return preg_match("/{$this->searchText}/", $purge->text);
            });
        } elseif ($this->caseSensitive) {
            $purges = $query->get()->filter(function ($purge) {
                return str_contains($purge->text, $this->searchText);
            });
        } else {
            $purges = $query->where('text', 'like', "%{$this->searchText}%")->get();
        }

        if ($purges->isEmpty()) {
            $stateDescription = $operation === 'save' ? 'unsaved' : 'saved';
            Notification::make()
                ->warning()
                ->title('No purges found')
                ->body("No {$stateDescription} purges found containing the search text.")
                ->send();
            return;
        }

        $updated = 0;
        foreach ($purges as $purge) {
            $purge->update(['save' => $newSaveState]);
            $updated++;
        }

        $actionDescription = $operation === 'save' ? 'saved' : 'unsaved';
        Notification::make()
            ->success()
            ->title("Purges marked as {$actionDescription}")
            ->body("Successfully marked {$updated} purge(s) as {$actionDescription}.")
            ->send();

        $this->searchText = '';
    }
}
