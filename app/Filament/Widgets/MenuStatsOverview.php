<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\RestaurantDetails;
use App\Filament\Resources\UserResource;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;

class MenuStatsOverview extends BaseWidget
{
    private function getHtmlLabel($label)
    {
        return new HtmlString("<span class='text-defaultWhite'>$label</span>");
    }

    private function getStatsDetails()
    {
        return [
            // Rows of 3, alternating between design 1 and design 2
            ['label' => 'Users', 'icon' => 'user-group', 'design' => 'bg-gradient-cursor-design-1', 'url' => UserResource::getUrl()],
            ['label' => 'Restaurant Details', 'icon' => 'building-storefront', 'design' => 'bg-gradient-cursor-design-1', 'url' => RestaurantDetails::getUrl()],
            ['label' => 'Homepage Edits', 'icon' => 'pencil-square', 'design' => 'bg-gradient-cursor-design-1', 'url' => null],

            ['label' => 'Rolling Message', 'icon' => 'chat-bubble-left', 'design' => 'bg-gradient-cursor-design-2', 'url' => null],
            ['label' => 'Reviews', 'icon' => 'star', 'design' => 'bg-gradient-cursor-design-2', 'url' => null],
            ['label' => 'Opening Hours', 'icon' => 'clock', 'design' => 'bg-gradient-cursor-design-2', 'url' => null],

            ['label' => 'Meta Details', 'icon' => 'code-bracket', 'design' => 'bg-gradient-cursor-design-1', 'url' => null],
            ['label' => 'Create Categories', 'icon' => 'squares-plus', 'design' => 'bg-gradient-cursor-design-1', 'url' => null],
            ['label' => 'Menu Special Options', 'icon' => 'list-bullet', 'design' => 'bg-gradient-cursor-design-1', 'url' => null],

            ['label' => 'Create Menu', 'icon' => 'table-cells', 'design' => 'bg-gradient-cursor-design-2', 'url' => null],
            ['label' => 'Reservation', 'icon' => 'calendar-days', 'design' => 'bg-gradient-cursor-design-2', 'url' => null],
            ['label' => 'Social Media Icons', 'icon' => 'squares-2x2', 'design' => 'bg-gradient-cursor-design-2', 'url' => null],

            ['label' => 'Color Themes', 'icon' => 'swatch', 'design' => 'bg-gradient-cursor-design-1', 'url' => null],
            ['label' => 'Designs', 'icon' => 'sparkles', 'design' => 'bg-gradient-cursor-design-1', 'url' => null],
            ['label' => 'Holiday', 'icon' => 'stop', 'design' => 'bg-gradient-cursor-design-1', 'url' => null],

            ['label' => 'Scripts', 'icon' => 'document-text', 'design' => 'bg-gradient-cursor-design-2', 'url' => null],
            ['label' => 'Time Zone', 'icon' => 'globe-alt', 'design' => 'bg-gradient-cursor-design-2', 'url' => null],
            ['label' => 'Reservations/Order', 'icon' => 'shopping-cart', 'design' => 'bg-gradient-cursor-design-2', 'url' => null],
        ];

    }

    protected function getStats(): array
    {
        return collect($this->getStatsDetails())
            ->map(function ($item) {
                return Stat::make('', $this->getHtmlLabel($item['label']))
                    ->icon("heroicon-s-{$item['icon']}")
                    ->url($item['url'])
                    ->extraAttributes(['class' => $item['design'] . " px-2 py-2"]);
            })->all();

    }
}
