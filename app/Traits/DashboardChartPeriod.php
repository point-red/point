<?php

namespace App\Traits;

use App\Model\Form;

trait DashboardChartPeriod
{
    public function scopePeriodic($query, $period)
    {
        $query->when(is_null($period) || $period === 'monthly', function($q) {
            return $q->monthly();
        })
        ->when($period === 'weekly', function($q) {
            return $q->weekly();
        })
        ->when($period === 'daily', function($q) {
            return $q->daily();
        })
        ->when($period === 'yearly', function($q) {
            return $q->yearly();
        })
        ->when($period === 'quarterly', function($q) {
            return $q->quarterly();
        });
    }
    
    public function scopeDaily ($query)
    {
        $query->selectRaw('DAYOFYEAR(' . Form::getTableName('date') . ') AS day')
            ->groupBy('day');
    }

    public function scopeWeekly ($query)
    {
        $query->selectRaw('YEARWEEK(' . Form::getTableName('date') . ') AS week')
            ->groupBy('week');
    }
    
    public function scopeMonthly ($query)
    {
        $query->selectRaw('MONTH(' . Form::getTableName('date') . ') AS month')
            ->selectRaw('YEAR(' . Form::getTableName('date') . ') AS year')
            ->groupBy('month', 'year');
    }

    public function scopeQuarterly ($query)
    {
        $query->selectRaw('QUARTER(' . Form::getTableName('date') . ') AS quarter')
            ->selectRaw('YEAR(' . Form::getTableName('date') . ') AS year')
            ->groupBy('quarter', 'year');
    }

    public function scopeYearly ($query)
    {
        $query->selectRaw('YEAR(' . Form::getTableName('date') . ') AS year')
            ->groupBy('year');
    }
}