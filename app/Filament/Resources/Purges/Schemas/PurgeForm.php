<?php

namespace App\Filament\Resources\Purges\Schemas;

use App\Models\Purge;
use Filament\Forms\Components;
use Filament\Schemas\Schema;

class PurgeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Components\TextInput::make('post_id')
                    ->label('Tweet ID')
                    ->numeric()
                    ->disabled(),

                Components\Textarea::make('text')
                    ->label('Tweet Text')
                    ->rows(3)
                    ->disabled(),

                Components\DateTimePicker::make('posted_at')
                    ->label('Posted At')
                    ->disabled(),

                Components\Toggle::make('save')
                    ->label('Save (Protect from Deletion)')
                    ->helperText('Enable this to prevent the tweet from being deleted'),

                Components\Select::make('account_id')
                    ->label('Twitter Account')
                    ->relationship('account', 'username')
                    ->placeholder('Use default (@drewroberts)')
                    ->helperText('Leave empty to use the default account'),

                Components\TextInput::make('status')
                    ->label('Status')
                    ->default(fn (?Purge $record): string => $record->status ?? 'Pending')
                    ->disabled()
                    ->dehydrated(false),

                Components\TextInput::make('requested_at_display')
                    ->label('Requested At')
                    ->default(fn (?Purge $record): string => $record?->requested_at?->diffForHumans() ?? 'Not yet requested'
                    )
                    ->disabled()
                    ->dehydrated(false),

                Components\TextInput::make('purged_at_display')
                    ->label('Purged At')
                    ->default(fn (?Purge $record): string => $record?->purged_at?->diffForHumans() ?? 'Not yet purged'
                    )
                    ->disabled()
                    ->dehydrated(false),
            ]);
    }
}
