<?php

namespace App\Filament\Resources\Accounts\Pages;

use App\Enums\SocialService;
use App\Filament\Resources\Accounts\AccountResource;
use App\Models\Account;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAccount extends ViewRecord
{
    protected static string $resource = AccountResource::class;

    protected function getHeaderActions(): array
    {
        /** @var Account $record */
        $record = $this->record;

        return [
            Actions\Action::make('reconnect')
                ->label('Reconnect Account')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn (): bool => ! $record->is_active || $record->isTokenExpired())
                ->url(fn (): string => route('social.connect', ['service' => $record->service->value])),

            Actions\Action::make('verify')
                ->label('Verify Credentials')
                ->icon('heroicon-o-check-circle')
                ->color('info')
                ->requiresConfirmation()
                ->action(function () use ($record) {
                    $service = match ($record->service) {
                        SocialService::TWITTER => app(\App\Services\TwitterAccountService::class),
                        default => throw new \Exception('Service not yet implemented'),
                    };

                    if ($service->verifyCredentials($record)) {
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Account verified')
                            ->body('Credentials are valid and metadata updated.')
                            ->send();
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title('Verification failed')
                            ->body('Could not verify account credentials.')
                            ->send();
                    }
                }),

            Actions\Action::make('disconnect')
                ->label('Disconnect Account')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Disconnect Account')
                ->modalDescription('Are you sure you want to disconnect this account? You can reconnect it later.')
                ->action(function () use ($record) {
                    $service = match ($record->service) {
                        SocialService::TWITTER => app(\App\Services\TwitterAccountService::class),
                        default => throw new \Exception('Service not yet implemented'),
                    };

                    $service->disconnect($record);

                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('Account disconnected')
                        ->body("Successfully disconnected {$record->username}")
                        ->send();

                    return redirect()->route('filament.admin.resources.accounts.index');
                }),
        ];
    }
}
