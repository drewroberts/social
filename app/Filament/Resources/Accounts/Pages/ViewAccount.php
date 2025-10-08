<?php

namespace App\Filament\Resources\Accounts\Pages;

use App\Enums\SocialService;
use App\Filament\Resources\Accounts\AccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAccount extends ViewRecord
{
    protected static string $resource = AccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('reconnect')
                ->label('Reconnect Account')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn (): bool => !$this->record->is_active || $this->record->isTokenExpired())
                ->url(fn (): string => route('social.connect', ['service' => $this->record->service->value])),

            Actions\Action::make('verify')
                ->label('Verify Credentials')
                ->icon('heroicon-o-check-circle')
                ->color('info')
                ->requiresConfirmation()
                ->action(function () {
                    $service = match($this->record->service) {
                        SocialService::TWITTER => app(\App\Services\TwitterAccountService::class),
                        default => throw new \Exception('Service not yet implemented'),
                    };

                    if ($service->verifyCredentials($this->record)) {
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
                ->action(function () {
                    $service = match($this->record->service) {
                        SocialService::TWITTER => app(\App\Services\TwitterAccountService::class),
                        default => throw new \Exception('Service not yet implemented'),
                    };

                    $service->disconnect($this->record);

                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('Account disconnected')
                        ->body("Successfully disconnected {$this->record->username}")
                        ->send();

                    return redirect()->route('filament.admin.resources.accounts.index');
                }),
        ];
    }
}
