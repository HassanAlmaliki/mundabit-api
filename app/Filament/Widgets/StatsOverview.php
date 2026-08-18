<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('إجمالي المستخدمين', User::count())
                ->description('إجمالي عدد المستخدمين المسجلين')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
            
            Stat::make('إجمالي الرواتب', '$' . number_format(User::sum('total_salary'), 2))
                ->description('مجموع رواتب المستخدمين')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
            
            Stat::make('الأهداف المحققة', '85%')
                ->description('معدل إنجاز الأهداف لهذا الشهر')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
        ];
    }
}

