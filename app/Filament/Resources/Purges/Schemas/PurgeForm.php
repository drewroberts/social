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
                    ->formatStateUsing(fn (?Purge $record): string => $record?->status ?? 'Pending')
                    ->disabled()
                    ->dehydrated(false),

                Components\DateTimePicker::make('requested_at')
                    ->label('Requested At')
                    ->disabled(),

                Components\DateTimePicker::make('purged_at')
                    ->label('Purged At')
                    ->disabled(),
            ]);
    }
}
