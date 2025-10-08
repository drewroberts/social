<?php

namespace App\Filament\Resources\Accounts\Schemas;

use App\Enums\SocialService;
use App\Models\Account;
use Filament\Forms\Components;
use Filament\Schemas\Schema;

class AccountForm
{
    public static function configure(Schema $schema): Schema
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

                Components\TextInput::make('last_synced')
                    ->label('Last Synced')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(fn (Account $record): string => $record->last_synced_at?->diffForHumans() ?? 'Never'),

                Components\TextInput::make('token_status')
                    ->label('Token Status')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(function (Account $record): string {
                        if ($record->isTokenExpired()) {
                            return 'Expired';
                        } elseif ($record->needsTokenRefresh()) {
                            return 'Needs Refresh';
                        } else {
                            return 'Valid';
                        }
                    }),
            ]);
    }
}
