<?php

namespace App\Filament\Resources\Purges\Pages;

use App\Filament\Resources\Purges\PurgeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPurge extends ViewRecord
{
    protected static string $resource = PurgeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
