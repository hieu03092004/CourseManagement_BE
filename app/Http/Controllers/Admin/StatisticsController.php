<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Course;

class StatisticsController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->type ?? 'week';  // week | month
        $range = intval($request->range ?? 1); // 1 - 5

        if (!in_array($type, ['week', 'month'])) {
            return response()->json(['error' => 'Invalid type'], 400);
        }

        if ($range < 1 || $range > 5) {
            return response()->json(['error' => 'Range must be 1-5'], 400);
        }

        return response()->json([
            "overview" => $this->getOverview(),
            "timeline" => $type === 'week'
                ? $this->getWeeklyStatistics($range)
                : $this->getMonthlyStatistics($range),
            "courses" => $type === 'week'
                ? $this->getCourseStatisticsByWeek($range)
                : $this->getCourseStatisticsByMonth($range)
        ]);
    }

    // ----------------------------------------------------------
    // 1) Tổng quan: tổng doanh thu, tổng đơn, tổng khoá học
    // ----------------------------------------------------------
    private function getOverview()
    {
        return [
            "total_revenue" => OrderItem::sum('unit_price'),
            "total_orders" => Order::count(),
            "total_courses" => Course::count()
        ];
    }

    // ----------------------------------------------------------
    // 2) Thống kê theo tuần (N tuần gần đây)
    // ----------------------------------------------------------
    private function getWeeklyStatistics($range)
    {
        $today = Carbon::today();
        $thisWeek = $today->copy()->startOfWeek(Carbon::MONDAY);

        $weeks = [];

        for ($i = 0; $i < $range; $i++) {

            $start = $thisWeek->copy()->subWeeks($i);
            $end = $start->copy()->endOfWeek(Carbon::SUNDAY);

            $orders = Order::with('items')
                ->whereBetween('created_at', [$start, $end])
                ->get();

            $weeks[] = [
                "label" => "Week " . $start->format('W'),
                "start_date" => $start->toDateString(),
                "end_date"   => $end->toDateString(),
                "total_orders" => $orders->count(),
                "total_revenue" => $orders->flatMap->items->sum('unit_price')
            ];
        }

        return array_reverse($weeks);
    }

    // ----------------------------------------------------------
    // 3) Thống kê theo tháng (N tháng gần đây)
    // ----------------------------------------------------------
    private function getMonthlyStatistics($range)
    {
        $thisMonth = Carbon::today()->startOfMonth();

        $months = [];

        for ($i = 0; $i < $range; $i++) {

            $start = $thisMonth->copy()->subMonths($i);
            $end = $start->copy()->endOfMonth();

            $orders = Order::with('items')
                ->whereBetween('created_at', [$start, $end])
                ->get();

            $months[] = [
                "label" => $start->format("Y-m"),
                "start_date" => $start->toDateString(),
                "end_date"   => $end->toDateString(),
                "total_orders" => $orders->count(),
                "total_revenue" => $orders->flatMap->items->sum('unit_price')
            ];
        }

        return array_reverse($months);
    }

    // ----------------------------------------------------------
    // 4) Bảng khoá học theo N tuần gần đây
    // ----------------------------------------------------------
    private function getCourseStatisticsByWeek($range)
    {
        $today = Carbon::today();
        $startDate = $today->copy()->startOfWeek()->subWeeks($range - 1);
        $endDate = $today->copy()->endOfWeek();

        $items = OrderItem::with(['order', 'course'])
            ->whereHas('order', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->get();

        return $items->groupBy('courses_id')->map(function ($group) {
            return [
                "course_id" => $group->first()->course->courses_id,
                "title" => $group->first()->course->title,
                "total_orders" => $group->groupBy('orders_id')->count(),
                "total_revenue" => $group->sum('unit_price'),
            ];
        })->values();
    }

    // ----------------------------------------------------------
    // 5) Bảng khoá học theo N tháng gần đây
    // ----------------------------------------------------------
    private function getCourseStatisticsByMonth($range)
    {
        $today = Carbon::today();
        $startDate = $today->copy()->startOfMonth()->subMonths($range - 1);
        $endDate = $today->copy()->endOfMonth();

        $items = OrderItem::with(['order', 'course'])
            ->whereHas('order', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->get();

        return $items->groupBy('courses_id')->map(function ($group) {
            return [
                "course_id" => $group->first()->course->courses_id,
                "title" => $group->first()->course->title,
                "total_orders" => $group->groupBy('orders_id')->count(),
                "total_revenue" => $group->sum('unit_price'),
            ];
        })->values();
    }
}
