<?php

namespace App\Filament\Resources\Accounts\Schemas;

use App\Enums\SocialService;
use App\Models\Account;
use Filament\Forms\Components;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

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

                Components\Placeholder::make('last_synced')
                    ->label('Last Synced')
                    ->content(fn (Account $record): string => $record->last_synced_at?->diffForHumans() ?? 'Never'
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
}
