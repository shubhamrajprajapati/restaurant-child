<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;

class MenuStatsOverview extends BaseWidget
{
    private function getHtmlLabel($label)
    {
        return new HtmlString("<span class='text-defaultWhite'>$label</span>");
    }
    protected function getStats(): array
    {
        return [
            Stat::make('', $this->getHtmlLabel('Users'))
                ->icon('heroicon-s-user-group')
                ->extraAttributes([
                    'class' => 'bg-gradient-cursor-design-1',
                ]),
            Stat::make('', $this->getHtmlLabel('Restaurant Name'))
                ->icon('heroicon-s-building-storefront')
                ->extraAttributes([
                    'class' => 'bg-gradient-cursor-design-1',
                ]),
            Stat::make('', $this->getHtmlLabel('Homepage Edits'))
                ->icon('heroicon-o-pencil-square')
                ->extraAttributes([
                    'class' => 'bg-gradient-cursor-design-1',
                ]),
            Stat::make('', $this->getHtmlLabel('Rolling Message'))
                ->extraAttributes([
                    'class' => 'bg-gradient-cursor-design-2',
                ]),
            Stat::make('', $this->getHtmlLabel('Reviews'))
                ->extraAttributes([
                    'class' => 'bg-gradient-cursor-design-2',
                ]),
            Stat::make('', $this->getHtmlLabel('Opening Hours'))
                ->extraAttributes([
                    'class' => 'bg-gradient-cursor-design-2',
                ]),
        ];

    }
}
