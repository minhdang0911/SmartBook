<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RevenueController extends Controller
{
       public function index()
    {
        return view('admin.revenue.index');
    }

    /**
     * Lấy tổng doanh thu theo khoảng thời gian
     */
    public function getTotalRevenue(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth());
        
        $totalRevenue = Order::whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_price');

        $totalOrders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_revenue' => $totalRevenue,
                'total_orders' => $totalOrders,
                'average_order_value' => $totalOrders > 0 ? $totalRevenue / $totalOrders : 0,
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
            ]
        ]);
    }

    /**
     * Doanh thu theo ngày
     */
    public function getDailyRevenue(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth());

        $dailyRevenue = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_price) as revenue'),
                DB::raw('COUNT(*) as orders_count')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $dailyRevenue
        ]);
    }

    /**
     * Doanh thu theo tháng
     */
     public function getMonthlyRevenue(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);

        $monthlyRevenue = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_price) as revenue'),
                DB::raw('COUNT(*) as orders_count')
            )
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'asc')
            ->get();

        // Thông tin tổng quan của tháng
        $totalRevenue = Order::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->sum('total_price');

        $totalOrders = Order::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'year' => $year,
                'month' => $month,
                'total_revenue' => $totalRevenue,
                'total_orders' => $totalOrders,
                'average_order_value' => $totalOrders > 0 ? $totalRevenue / $totalOrders : 0,
                'daily_breakdown' => $monthlyRevenue
            ]
        ]);
    }

    /**
     * Doanh thu theo quý
     */
    public function getQuarterlyRevenue(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);

        $quarterlyRevenue = Order::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('QUARTER(created_at) as quarter'),
                DB::raw('SUM(total_price) as revenue'),
                DB::raw('COUNT(*) as orders_count')
            )
            ->whereYear('created_at', $year)
            ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('QUARTER(created_at)'))
            ->orderBy('year', 'asc')
            ->orderBy('quarter', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $quarterlyRevenue
        ]);
    }

    /**
     * Doanh thu theo năm
     */
    public function getYearlyRevenue(Request $request)
    {
        $years = $request->input('years', 5); // Mặc định lấy 5 năm gần nhất

        $yearlyRevenue = Order::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('SUM(total_price) as revenue'),
                DB::raw('COUNT(*) as orders_count')
            )
            ->whereYear('created_at', '>=', Carbon::now()->year - $years)
            ->groupBy(DB::raw('YEAR(created_at)'))
            ->orderBy('year', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $yearlyRevenue
        ]);
    }

    /**
     * Top sản phẩm bán chạy nhất (dựa trên doanh thu)
     */
    public function getTopProducts(Request $request)
    {
        $limit = $request->input('limit', 10);
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth());

        // Assuming you have OrderItem model with product relationship
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select(
                'products.name as product_name',
                'products.id as product_id',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.price * order_items.quantity) as total_revenue')
            )
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_revenue', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $topProducts
        ]);
    }

    /**
     * Thống kê doanh thu theo trạng thái đơn hàng
     */
    public function getRevenueByStatus(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth());

        $revenueByStatus = Order::select(
                'status',
                DB::raw('SUM(total_price) as revenue'),
                DB::raw('COUNT(*) as orders_count')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('status')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $revenueByStatus
        ]);
    }

    /**
     * Doanh thu theo phương thức thanh toán
     */
    public function getRevenueByPaymentMethod(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth());

        $revenueByPayment = Order::select(
                'payment',
                DB::raw('SUM(total_price) as revenue'),
                DB::raw('COUNT(*) as orders_count')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('payment')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $revenueByPayment
        ]);
    }

    /**
     * Dashboard tổng quan doanh thu
     */
    public function getDashboard(Request $request)
    {
        $today = Carbon::now()->startOfDay();
        $yesterday = Carbon::yesterday()->startOfDay();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $thisYear = Carbon::now()->startOfYear();

        // Doanh thu hôm nay
        $todayRevenue = Order::whereDate('created_at', $today)
            ->sum('total_price');

        // Doanh thu hôm qua
        $yesterdayRevenue = Order::whereDate('created_at', $yesterday)
            ->sum('total_price');

        // Doanh thu tháng này
        $thisMonthRevenue = Order::whereMonth('created_at', $thisMonth->month)
            ->whereYear('created_at', $thisMonth->year)
            ->sum('total_price');

        // Doanh thu tháng trước
        $lastMonthRevenue = Order::whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->sum('total_price');

        // Doanh thu năm nay
        $thisYearRevenue = Order::whereYear('created_at', $thisYear->year)
            ->sum('total_price');

        // Tổng số đơn hàng
        $totalOrders = Order::count();

        // Đơn hàng chờ xử lý
        $pendingOrders = Order::where('status', 'pending')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'today_revenue' => $todayRevenue,
                'yesterday_revenue' => $yesterdayRevenue,
                'revenue_growth_daily' => $yesterdayRevenue > 0 ? (($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100 : 0,
                'this_month_revenue' => $thisMonthRevenue,
                'last_month_revenue' => $lastMonthRevenue,
                'revenue_growth_monthly' => $lastMonthRevenue > 0 ? (($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100 : 0,
                'this_year_revenue' => $thisYearRevenue,
                'total_orders' => $totalOrders,
                'pending_orders' => $pendingOrders,
                'average_order_value' => $totalOrders > 0 ? $thisYearRevenue / $totalOrders : 0
            ]
        ]);
  }
   public function getRevenueBySpecificStatus(Request $request)
    {
        $status = $request->input('status', 'pending');
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth());

        $totalRevenue = Order::where('status', $status)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_price');

        $totalOrders = Order::where('status', $status)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Doanh thu theo ngày cho status này
        $dailyRevenue = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_price) as revenue'),
                DB::raw('COUNT(*) as orders_count')
            )
            ->where('status', $status)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $status,
                'total_revenue' => $totalRevenue,
                'total_orders' => $totalOrders,
                'average_order_value' => $totalOrders > 0 ? $totalRevenue / $totalOrders : 0,
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ],
                'daily_breakdown' => $dailyRevenue
            ]
        ]);
    }
  public function getQuarterDetail(Request $request)
{
    $year = $request->input('year', Carbon::now()->year);
    $quarter = $request->input('quarter');

    if (!in_array($quarter, [1, 2, 3, 4])) {
        return response()->json([
            'success' => false,
            'message' => 'Quarter must be 1, 2, 3 or 4'
        ], 400);
    }

    $quarterNames = [
        1 => ['name' => 'Q1', 'months' => 'January - March'],
        2 => ['name' => 'Q2', 'months' => 'April - June'],
        3 => ['name' => 'Q3', 'months' => 'July - September'],
        4 => ['name' => 'Q4', 'months' => 'October - December']
    ];

    $summary = Order::select(
            DB::raw('SUM(total_price) as total_revenue'),
            DB::raw('COUNT(*) as total_orders'),
            DB::raw('AVG(total_price) as average_order_value'),
            DB::raw('MIN(total_price) as min_order_value'),
            DB::raw('MAX(total_price) as max_order_value')
        )
        ->whereYear('created_at', $year)
        ->whereRaw('QUARTER(created_at) = ?', [$quarter])
        ->first();

    $monthlyData = Order::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('MONTHNAME(created_at) as month_name'),
            DB::raw('SUM(total_price) as revenue'),
            DB::raw('COUNT(*) as orders_count'),
            DB::raw('AVG(total_price) as average_order_value')
        )
        ->whereYear('created_at', $year)
        ->whereRaw('QUARTER(created_at) = ?', [$quarter])
        ->groupBy(DB::raw('MONTH(created_at)'), DB::raw('MONTHNAME(created_at)'))
        ->orderBy('month', 'asc')
        ->get();

    $topOrders = Order::select('id', 'total_price', 'created_at')
        ->whereYear('created_at', $year)
        ->whereRaw('QUARTER(created_at) = ?', [$quarter])
        ->orderBy('total_price', 'desc')
        ->limit(10)
        ->get();

    return response()->json([
        'success' => true,
        'quarter_info' => [
            'quarter' => $quarter,
            'name' => $quarterNames[$quarter]['name'],
            'months' => $quarterNames[$quarter]['months'],
            'year' => $year
        ],
        'summary' => [
            'total_revenue' => (float) ($summary->total_revenue ?? 0),
            'total_orders' => (int) ($summary->total_orders ?? 0),
            'average_order_value' => (float) ($summary->average_order_value ?? 0),
            'min_order_value' => (float) ($summary->min_order_value ?? 0),
            'max_order_value' => (float) ($summary->max_order_value ?? 0)
        ],
        'monthly_breakdown' => $monthlyData,
        'top_orders' => $topOrders
    ]);
}

}