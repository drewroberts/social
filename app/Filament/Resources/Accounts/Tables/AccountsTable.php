<?php

namespace App\Filament\Resources\Accounts\Tables;

use App\Enums\SocialService;
use App\Models\Account;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('service')
                    ->label('Service')
                    ->formatStateUsing(fn (SocialService $state): string => $state->label())
                    ->icon(fn (SocialService $state): string => $state->icon())
                    ->iconColor(fn (SocialService $state): string => match ($state) {
                        SocialService::TWITTER => 'info',
                        SocialService::FACEBOOK => 'primary',
                        SocialService::TELEGRAM => 'success',
                    })
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('username')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('token_status')
                    ->label('Token Status')
                    ->badge()
                    ->color(fn (Account $record): string => $record->isTokenExpired() ? 'danger' :
                        ($record->needsTokenRefresh() ? 'warning' : 'success')
                    )
                    ->formatStateUsing(function (Account $record): string {
                        if ($record->isTokenExpired()) {
                            return 'Expired';
                        } elseif ($record->needsTokenRefresh()) {
                            return 'Needs Refresh';
                        } else {
                            return 'Valid';
                        }
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('token_expires_at', $direction);
                    }),

                Tables\Columns\TextColumn::make('last_synced_at')
                    ->label('Last Synced')
                    ->dateTime()
                    ->since()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Connected')
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('service')
                    ->options([
                        SocialService::TWITTER->value => SocialService::TWITTER->label(),
                        SocialService::FACEBOOK->value => SocialService::FACEBOOK->label(),
                        SocialService::TELEGRAM->value => SocialService::TELEGRAM->label(),
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('All accounts')
                    ->trueLabel('Active accounts')
                    ->falseLabel('Inactive accounts'),

                Tables\Filters\Filter::make('token_expired')
                    ->label('Expired Tokens')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('token_expires_at')
                        ->where('token_expires_at', '<', now())
                    ),

                Tables\Filters\Filter::make('needs_refresh')
                    ->label('Needs Token Refresh')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('token_expires_at')
                        ->where('token_expires_at', '>', now())
                        ->where('token_expires_at', '<', now()->addDay())
                    ),
            ])
            ->recordActions([
                ViewAction::make(),

                Action::make('reconnect')
                    ->label('Reconnect')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (Account $record): bool => ! $record->is_active || $record->isTokenExpired())
                    ->url(fn (Account $record): string => route('social.connect', ['service' => $record->service->value]))
                    ->openUrlInNewTab(false),

                Action::make('verify')
                    ->label('Verify')
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(function (Account $record) {
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

                Action::make('disconnect')
                    ->label('Disconnect')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Disconnect Account')
                    ->modalDescription('Are you sure you want to disconnect this account? You can reconnect it later.')
                    ->action(function (Account $record) {
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
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ])
            ->emptyStateHeading('No connected accounts')
            ->emptyStateDescription('Connect your social media accounts to get started.')
            ->emptyStateActions([
                Action::make('connect_twitter')
                    ->label('Connect Twitter')
                    ->icon('heroicon-o-share')
                    ->url(route('social.connect', ['service' => SocialService::TWITTER->value])),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->where('user_id', Auth::id()));
    }
}
