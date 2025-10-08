<?php

namespace App\Filament\Resources\Purges\Pages;

use App\Filament\Resources\Purges\PurgeResource;
use App\Models\Purge;
use App\Services\PurgeService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

/**
 * @property Purge $record
 */
class ViewPurge extends ViewRecord
{
    protected static string $resource = PurgeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('toggleSave')
                ->label(fn () => $this->record->save ? 'Mark as Unsaved' : 'Mark as Saved')
                ->icon(fn () => $this->record->save ? 'heroicon-o-bookmark-slash' : 'heroicon-o-bookmark')
                ->color(fn () => $this->record->save ? 'warning' : 'primary')
                ->requiresConfirmation()
                ->modalHeading(fn () => $this->record->save ? 'Mark Tweet as Unsaved?' : 'Mark Tweet as Saved?')
                ->modalDescription(fn () => $this->record->save
                    ? 'This will allow the tweet to be deleted during the next purge cycle.'
                    : 'This will protect the tweet from being deleted.'
                )
                ->action(function () {
                    $this->record->update(['save' => ! $this->record->save]);

                    Notification::make()
                        ->success()
                        ->title($this->record->save ? 'Tweet marked as saved' : 'Tweet marked as unsaved')
                        ->send();
                }),

            Actions\Action::make('manualPurge')
                ->label('Delete Tweet Now')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Delete This Tweet from Twitter?')
                ->modalDescription('This will immediately delete the tweet from Twitter. This action cannot be undone.')
                ->hidden(fn () => $this->record->save || $this->record->purged_at)
                ->action(function (PurgeService $purgeService) {
                    $success = $purgeService->processPurge($this->record);

                    if ($success) {
                        Notification::make()
                            ->success()
                            ->title('Tweet deleted successfully')
                            ->body('The tweet has been deleted from Twitter.')
                            ->send();
                    } else {
                        Notification::make()
                            ->danger()
                            ->title('Failed to delete tweet')
                            ->body('There was an error deleting the tweet. Check the logs for details.')
                            ->send();
                    }
                }),

            Actions\EditAction::make(),
        ];
    }
}
