<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\StatisticHelper;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Sử dụng helper function để lấy data
        $statistic = StatisticHelper::getDashboardStatistics();

        return response()->json([
            'pageTitle' => 'Trang Tổng Quan',
            'statistic' => $statistic,
        ]);
    }
}

