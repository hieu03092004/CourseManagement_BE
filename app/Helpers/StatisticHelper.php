<?php

namespace App\Helpers;

class StatisticHelper
{
    /**
     * Get fake statistics data for dashboard
     *
     * @return array
     */
    public static function getDashboardStatistics()
    {
        return [
            'categoryProduct' => [
                'total' => 25,
                'active' => 18,
                'inactive' => 7,
            ],
            'product' => [
                'total' => 150,
                'active' => 120,
                'inactive' => 30,
            ],
            'account' => [
                'total' => 45,
                'active' => 40,
                'inactive' => 5,
            ],
            'user' => [
                'total' => 500,
                'active' => 480,
                'inactive' => 20,
            ],
        ];
    }

    /**
     * Get product statistics
     *
     * @return array
     */
    public static function getProductStatistics()
    {
        return [
            'total' => 150,
            'active' => 120,
            'inactive' => 30,
        ];
    }

    /**
     * Get category statistics
     *
     * @return array
     */
    public static function getCategoryStatistics()
    {
        return [
            'total' => 25,
            'active' => 18,
            'inactive' => 7,
        ];
    }
}

