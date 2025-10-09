<?php

namespace App\Filament\Resources\Purges\Pages\ViewPurge\Widgets;

use App\Models\Purge;
use Filament\Widgets\Widget;

class ViewPostWidget extends Widget
{
    protected string $view = 'filament.resources.purges.pages.view-purge.widgets.view-post-widget';

    public Purge $record;

    protected int|string|array $columnSpan = 'full';
}
