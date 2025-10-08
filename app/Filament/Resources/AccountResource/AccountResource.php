<?php

namespace App\Filament\Resources\AccountResource;

use App\Enums\SocialService;
use App\Filament\Resources\AccountResource\Pages;
use App\Models\Account;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-share';

    protected static ?string $navigationLabel = 'Connected Accounts';

    protected static ?string $modelLabel = 'Connected Account';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Components\Select::make('service')
                    ->label('Service')
                    ->options([
                        SocialService::TWITTER->value => SocialService::TWITTER->label(),
                        SocialService::FACEBOOK->value => SocialService::FACEBOOK->label(),
                        SocialService::TELEGRAM->value => SocialService::TELEGRAM->label(),
                    ])
                    ->required()
                    ->disabled(),

                Components\TextInput::make('username')
                    ->disabled(),

                Components\Toggle::make('is_active')
                    ->label('Active')
                    ->disabled(),

                Components\Placeholder::make('last_synced')
                    ->label('Last Synced')
                    ->content(fn (Account $record): string => 
                        $record->last_synced_at?->diffForHumans() ?? 'Never'
                    ),

                Components\Placeholder::make('token_status')
                    ->label('Token Status')
                    ->content(function (Account $record): HtmlString {
                        if ($record->isTokenExpired()) {
                            return new HtmlString('<span class="text-red-600 dark:text-red-400">Expired</span>');
                        } elseif ($record->needsTokenRefresh()) {
                            return new HtmlString('<span class="text-orange-600 dark:text-orange-400">Needs Refresh</span>');
                        } else {
                            return new HtmlString('<span class="text-green-600 dark:text-green-400">Valid</span>');
                        }
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('service')
                    ->label('Service')
                    ->formatStateUsing(fn (SocialService $state): string => $state->label())
                    ->icon(fn (SocialService $state): string => $state->icon())
                    ->iconColor(fn (SocialService $state): string => match($state) {
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
                    ->color(fn (Account $record): string => 
                        $record->isTokenExpired() ? 'danger' : 
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
                    ->query(fn (Builder $query): Builder => 
                        $query->whereNotNull('token_expires_at')
                              ->where('token_expires_at', '<', now())
                    ),

                Tables\Filters\Filter::make('needs_refresh')
                    ->label('Needs Token Refresh')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereNotNull('token_expires_at')
                              ->where('token_expires_at', '>', now())
                              ->where('token_expires_at', '<', now()->addDay())
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('reconnect')
                    ->label('Reconnect')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (Account $record): bool => !$record->is_active || $record->isTokenExpired())
                    ->url(fn (Account $record): string => route('social.connect', ['service' => $record->service->value]))
                    ->openUrlInNewTab(false),

                Tables\Actions\Action::make('verify')
                    ->label('Verify')
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(function (Account $record) {
                        $service = match($record->service) {
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

                Tables\Actions\Action::make('disconnect')
                    ->label('Disconnect')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Disconnect Account')
                    ->modalDescription('Are you sure you want to disconnect this account? You can reconnect it later.')
                    ->action(function (Account $record) {
                        $service = match($record->service) {
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
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ])
            ->emptyStateHeading('No connected accounts')
            ->emptyStateDescription('Connect your social media accounts to get started.')
            ->emptyStateActions([
                Tables\Actions\Action::make('connect_twitter')
                    ->label('Connect Twitter')
                    ->icon('heroicon-o-share')
                    ->url(route('social.connect', ['service' => SocialService::TWITTER->value])),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->where('user_id', auth()->id()));
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccounts::route('/'),
            'view' => Pages\ViewAccount::route('/{record}'),
        ];
    }
}
