@extends('layouts.app')

@section('content')
@php
    use Illuminate\Support\Str;
@endphp

<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<style>
/* === CLEAN MONO + SOFT ACCENTS === */
:root{
  --bg:#fafafa;
  --card:#ffffff;
  --text:#111;          /* gần đen */
  --muted:#616161;      /* chữ phụ */
  --line:#e9e9e9;       /* viền */
  --accent:#111;        /* primary */

  /* soft accent tông dịu (desaturated) */
  --soft-blue:#eaf1ff;   /* xanh dương rất nhạt */
  --soft-green:#eaf7ee;  /* xanh lá rất nhạt */
  --soft-amber:#fff4e6;  /* hổ phách rất nhạt */
  --soft-rose:#ffecef;   /* hồng rất nhạt */

  --ink:#111;
  --ink-2:#2b2b2b;
  --ink-3:#444;
  --ink-4:#6b7280; /* slate-500 */
}

body.ui-mono{ background:var(--bg)!important; color:var(--text)!important; }

/* Card / panel */
.bg-white, .chart-container, .gradient-card{
  background:var(--card)!important;
  color:var(--text)!important;
  border:1px solid var(--line)!important;
  box-shadow:0 6px 16px rgba(0,0,0,.08)!important;
  border-radius:12px!important;
}
.gradient-card{ transition:.2s; }
.gradient-card:hover{ transform:translateY(-4px); }

/* Typo */
h1,h2,h3,h4,h5,h6{ color:var(--text)!important; letter-spacing:.1px }
.text-gray-500, .text-gray-600{ color:var(--muted)!important }

/* Header icon */
.fa-bell{ color:var(--ink-4)!important }

/* Badge / pill dịu màu */
.badge-soft{
  display:inline-flex; align-items:center; gap:.4rem;
  padding:.25rem .6rem; border-radius:999px; font-weight:600; font-size:.8rem;
  border:1px solid var(--line); color:var(--ink-2);
}
.badge-soft.blue{ background:var(--soft-blue); }
.badge-soft.green{ background:var(--soft-green); }
.badge-soft.amber{ background:var(--soft-amber); }
.badge-soft.rose{ background:var(--soft-rose); }

/* Progress */
.progress-track{ background:#eee!important; }
.progress-fill{ background:var(--ink)!important; }

/* Tabs / buttons */
.tab-button{
  background:#fff!important; color:var(--ink-4)!important;
  border:1px solid var(--line)!important; border-radius:8px; padding:.4rem .7rem;
}
.tab-button:hover{ border-color:#cfcfcf; color:var(--ink-2) }
.tab-button.active{
  background:var(--ink)!important; color:#fff!important; border-color:var(--ink)!important;
  box-shadow:0 2px 8px rgba(0,0,0,.25)!important;
}
button{ border-radius:8px!important; transition:.2s }
button.btn-primary{
  background:var(--ink)!important; border:1px solid var(--ink)!important; color:#fff!important
}
button.btn-primary:hover{ background:#000!important; border-color:#000!important }
button.btn-ghost{
  background:#fff!important; color:var(--ink)!important; border:1px solid var(--line)!important;
}
button.btn-ghost:hover{ border-color:#cfcfcf!important; }

/* List hover */
.modern-list-item{ border-left:4px solid transparent }
.modern-list-item:hover{ background:#fafafa!important; border-left-color:var(--ink)!important }

/* Modal */
.modal{ display:none; position:fixed; inset:0; z-index:1000; background:rgba(0,0,0,.45)!important; backdrop-filter:blur(3px) }
.modal.show{ display:flex; align-items:center; justify-content:center }
.modal-content{ border:1px solid var(--line)!important; background:#fff; width:90%; max-width:800px; border-radius:16px; overflow:hidden }
.modal .modal-head{
  background:linear-gradient(180deg, #111, #1b1b1b); color:#fff; padding:24px; border-bottom:1px solid #000;
}
.modal .modal-sub{ color:#cfcfcf; }

/* Focus */
:focus{ outline:2px solid var(--ink); outline-offset:2px }
</style>

{{-- Header --}}
<div class="bg-white rounded-2xl mb-8 mx-4 lg:mx-0">
  <div class="px-6 py-6">
    <div class="flex items-center justify-between">
      <h1 class="text-3xl font-bold flex items-center">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center mr-4" style="background:linear-gradient(135deg,#111,#2b2b2b)">
          <i class="fas fa-tachometer-alt text-white text-xl"></i>
        </div>
        Trang Quản Trị
      </h1>
      <div class="flex items-center space-x-4">
        <div class="relative">
          <i class="fas fa-bell text-xl cursor-pointer hover:text-black transition-colors"></i>
          <span class="absolute -top-2 -right-2 badge-soft blue" style="padding:.1rem .45rem; border:none; font-weight:700;">3</span>
        </div>
        <div class="text-sm" id="realtime-clock">
          <i class="fas fa-clock mr-1"></i>{{ now()->format('d/m/Y H:i') }}
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container mx-auto px-4 py-6">
  {{-- Quick stats --}}
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    {{-- Tổng số sách --}}
    <div class="gradient-card p-6 animate-float">
      <div class="flex items-center justify-between">
        <div>
          <div class="flex items-center mb-2">
            <i class="fas fa-book mr-2 text-gray-500"></i>
            <h6 class="text-sm font-medium">Tổng số sách</h6>
          </div>
          <h2 class="text-4xl font-bold mb-2">{{ number_format(\App\Models\Book::count()) }}</h2>
          @php
            $currentBooks = \App\Models\Book::count();
            $lastMonthBooks = \App\Models\Book::where('created_at', '<', now()->subMonth())->count();
            $bookGrowth = $lastMonthBooks > 0 ? (($currentBooks - $lastMonthBooks) / $lastMonthBooks) * 100 : 0;
          @endphp
          <p class="text-sm flex items-center">
            <span class="badge-soft amber"><i class="fas fa-chart-line"></i>{{ $bookGrowth > 0 ? '+' : '' }}{{ number_format($bookGrowth, 1) }}%</span>
            <span class="ml-2 text-gray-600">so với tháng trước</span>
          </p>
        </div>
        <div class="rounded-2xl p-4" style="background:var(--soft-blue)">
          <i class="fas fa-book text-3xl" style="color:var(--ink-3)"></i>
        </div>
      </div>
      <div class="mt-4 rounded-lg p-2">
        <div class="flex justify-between text-xs">
          <span>Mục tiêu: 1,500</span>
          <span>{{ round((\App\Models\Book::count() / 1500) * 100) }}%</span>
        </div>
        <div class="w-full rounded-full h-2 mt-1 progress-track">
          <div class="rounded-full h-2 progress-fill" style="width: {{ min(round((\App\Models\Book::count() / 1500) * 100), 100) }}%"></div>
        </div>
      </div>
    </div>

    {{-- Sắp hết hàng --}}
    <div class="gradient-card p-6 animate-float" style="animation-delay:.08s" onclick="showLowStockModal()">
      <div class="flex items-center justify-between">
        <div>
          <div class="flex items-center mb-2">
            <i class="fas fa-exclamation-triangle mr-2 text-gray-500"></i>
            <h6 class="text-sm font-medium">Sắp hết hàng</h6>
          </div>
          <h2 class="text-4xl font-bold mb-2">{{ \App\Models\Book::where('stock', '<', 10)->count() }}</h2>
          <p class="text-sm text-gray-600"><i class="fas fa-info-circle mr-1"></i>Nhấn để xem chi tiết</p>
        </div>
        <div class="rounded-2xl p-4" style="background:var(--soft-rose)">
          <i class="fas fa-boxes text-3xl" style="color:var(--ink-3)"></i>
        </div>
      </div>
      <div class="mt-4 rounded-lg p-2">
        <div class="flex justify-between text-xs">
          <span>Tổng sản phẩm có stock &lt; 10</span>
          <span class="font-bold">{{ \App\Models\Book::where('stock', '<', 10)->count() }} items</span>
        </div>
        <div class="flex justify-between text-xs mt-1">
          <span>Hết hàng (stock = 0)</span>
          <span class="font-bold">{{ \App\Models\Book::where('stock', 0)->count() }} items</span>
        </div>
      </div>
    </div>

    {{-- Đơn hôm nay (đếm tất cả) --}}
    <div class="gradient-card p-6 animate-float" style="animation-delay:.16s" onclick="showTodayOrdersModal()">
      <div class="flex items-center justify-between">
        <div>
          <div class="flex items-center mb-2">
            <i class="fas fa-shopping-cart mr-2 text-gray-500"></i>
            <h6 class="text-sm font-medium">Đơn hôm nay</h6>
          </div>
          @php
            $todayOrders = \App\Models\Order::whereDate('created_at', now())->count();
            $yesterdayOrders = \App\Models\Order::whereDate('created_at', now()->subDay())->count();
            $orderGrowth = $yesterdayOrders > 0 ? (($todayOrders - $yesterdayOrders) / $yesterdayOrders) * 100 : ($todayOrders > 0 ? 100 : 0);
          @endphp
          <h2 class="text-4xl font-bold mb-2">{{ $todayOrders }}</h2>
          <p class="text-sm flex items-center">
            <span class="badge-soft blue"><i class="fas fa-arrows-alt-v"></i>{{ $orderGrowth > 0 ? '+' : '' }}{{ number_format($orderGrowth, 1) }}%</span>
            <span class="ml-2 text-gray-600">so với hôm qua ({{ $yesterdayOrders }})</span>
          </p>
        </div>
        <div class="rounded-2xl p-4" style="background:var(--soft-amber)">
          <i class="fas fa-cart-plus text-3xl" style="color:var(--ink-3)"></i>
        </div>
      </div>
      <div class="mt-4 rounded-lg p-2 order-trend">
        <div class="flex justify-between text-xs"><span>Tuần này</span><span>{{ \App\Models\Order::whereBetween('created_at', [now()->startOfWeek(), now()])->count() }} đơn</span></div>
        <div class="flex justify-between text-xs mt-1"><span>Tháng này</span><span>{{ \App\Models\Order::whereMonth('created_at', now()->month)->count() }} đơn</span></div>
      </div>
    </div>

    {{-- Doanh thu hôm nay (CHỈ completed) --}}
    <div class="gradient-card p-6 animate-float" style="animation-delay:.24s">
      <div class="flex items-center justify-between">
        <div>
          <div class="flex items-center mb-2">
            <i class="fas fa-dollar-sign mr-2 text-gray-500"></i>
            <h6 class="text-sm font-medium">Doanh thu hôm nay</h6>
          </div>
          @php
            $todayRevenue = \App\Models\Order::whereDate('created_at', now())->where('status','completed')->sum('total_price') ?? 0;
            $yesterdayRevenue = \App\Models\Order::whereDate('created_at', now()->subDay())->where('status','completed')->sum('total_price') ?? 0;
            $revenueGrowth = $yesterdayRevenue > 0 ? (($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100 : ($todayRevenue > 0 ? 100 : 0);
          @endphp
          <h2 class="text-3xl font-bold mb-2">{{ number_format($todayRevenue) }}₫</h2>
          <p class="text-sm flex items-center">
            <span class="badge-soft green"><i class="fas fa-percentage"></i>{{ $revenueGrowth > 0 ? '+' : '' }}{{ number_format($revenueGrowth, 1) }}%</span>
            <span class="ml-2 text-gray-600">so với hôm qua</span>
          </p>
        </div>
        <div class="rounded-2xl p-4" style="background:var(--soft-green)">
          <i class="fas fa-chart-line text-3xl" style="color:var(--ink-3)"></i>
        </div>
      </div>
      <div class="mt-4 rounded-lg p-2">
        <div class="flex justify-between text-xs"><span>Hôm qua</span><span>{{ number_format($yesterdayRevenue) }}₫</span></div>
        <div class="flex justify-between text-xs mt-1">
          <span>Tháng này</span>
          <span>{{ number_format(\App\Models\Order::whereMonth('created_at', now()->month)->where('status','completed')->sum('total_price') ?? 0) }}₫</span>
        </div>
      </div>
    </div>
  </div>

  {{-- Charts --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    {{-- Orders over time --}}
    <div class="chart-container rounded-2xl p-6">
      <div class="flex items-center justify-between mb-6">
        <h4 class="text-xl font-bold flex items-center">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center mr-3" style="background:linear-gradient(135deg,#111,#2b2b2b)">
            <i class="fas fa-chart-line text-white"></i>
          </div>
          Thống kê đơn hàng
        </h4>
        <div class="flex space-x-1" id="orderTimeFilter">
          <button class="tab-button" data-period="day"><i class="fas fa-calendar-day mr-1"></i>Ngày</button>
          <button class="tab-button active" data-period="week"><i class="fas fa-calendar-week mr-1"></i>Tuần</button>
          <button class="tab-button" data-period="month"><i class="fas fa-calendar-alt mr-1"></i>Tháng</button>
          <button class="tab-button" data-period="quarter"><i class="fas fa-calendar mr-1"></i>Quý</button>
          <button class="tab-button" data-period="year"><i class="fas fa-calendar-check mr-1"></i>Năm</button>
        </div>
      </div>
      <div class="relative">
        <canvas id="orderChart" style="max-height:350px"></canvas>
      </div>
    </div>

    {{-- Views by category (API) --}}
    <div class="chart-container rounded-2xl p-6">
      <div class="flex items-center justify-between mb-6">
        <h4 class="text-xl font-bold flex items-center">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center mr-3" style="background:linear-gradient(135deg,#111,#2b2b2b)">
            <i class="fas fa-chart-bar text-white"></i>
          </div>
          Lượt đọc theo danh mục
        </h4>
        <div class="flex space-x-2" id="viewsRangeFilter">
          <button class="tab-button active" data-range="week"><i class="fas fa-calendar-week mr-1"></i>Tuần</button>
          <button class="tab-button" data-range="month"><i class="fas fa-calendar-alt mr-1"></i>Tháng</button>
        </div>
      </div>
      <div class="relative">
        <canvas id="viewsChart" style="max-height:350px"></canvas>
      </div>
    </div>
  </div>

  {{-- More charts / lists --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    {{-- Books by publisher --}}
    <div class="chart-container rounded-2xl p-6">
      <div class="flex items-center justify-between mb-6">
        <h4 class="text-xl font-bold flex items-center">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center mr-3" style="background:linear-gradient(135deg,#111,#2b2b2b)">
            <i class="fas fa-chart-pie text-white"></i>
          </div>
          Sách theo nhà xuất bản
        </h4>
        <button class="px-4 py-2 btn-ghost text-sm font-medium">
          <i class="fas fa-external-link-alt mr-1"></i>Chi tiết
        </button>
      </div>
      <div class="relative">
        <canvas id="publisherChart" style="max-height:350px"></canvas>
      </div>
    </div>

    {{-- Low stock list --}}
    <div class="chart-container rounded-2xl p-6">
      <div class="flex items-center justify-between mb-6">
        <h4 class="text-xl font-bold flex items-center">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center mr-3" style="background:linear-gradient(135deg,#111,#2b2b2b)">
            <i class="fas fa-exclamation-triangle text-white"></i>
          </div>
          Top sách sắp hết hàng
        </h4>
        <button class="px-4 py-2 btn-primary text-sm font-medium" onclick="showLowStockModal()">
          <i class="fas fa-box mr-1"></i>Bổ sung kho
        </button>
      </div>
      <div class="space-y-3 max-h-80 overflow-y-auto">
        @php
          $lowStockBooks = \App\Models\Book::where('stock', '<', 10)->orderBy('stock', 'asc')->take(10)->get();
        @endphp
        @forelse ($lowStockBooks as $book)
          <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border-l-4 border-gray-300 hover:shadow-md transition-all modern-list-item">
            <div class="flex items-center space-x-3">
              <div class="w-12 h-12 rounded-xl flex items-center justify-center font-bold text-white" style="background:linear-gradient(135deg,#111,#2b2b2b)">
                {{ substr($book->title, 0, 1) }}
              </div>
              <div>
                <h6 class="font-semibold text-lg">{{ Str::limit($book->title, 25) }}</h6>
                <p class="text-xs text-gray-600">{{ $book->category->name ?? 'N/A' }}</p>
              </div>
            </div>
            <div class="text-right">
              <span class="badge-soft rose"><i class="fas fa-boxes"></i>{{ $book->stock }} left</span>
            </div>
          </div>
        @empty
          <div class="text-center py-8 text-gray-600">
            <i class="fas fa-check-circle text-4xl mb-2"></i>
            <p>Tất cả sản phẩm đều có đủ hàng!</p>
          </div>
        @endforelse
      </div>
    </div>
  </div>

  {{-- Content --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 bg-white rounded-2xl p-6">
      <div class="flex items-center justify-between mb-6">
        <h4 class="text-xl font-bold flex items-center">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center mr-3" style="background:linear-gradient(135deg,#111,#2b2b2b)">
            <i class="fas fa-book-open text-white"></i>
          </div>
          Sách mới thêm gần đây
        </h4>
        <button class="px-4 py-2 btn-ghost text-sm font-medium">
          <i class="fas fa-eye mr-1"></i>Xem tất cả
        </button>
      </div>
      <div class="space-y-3">
        @foreach ($recentBooks as $book)
          <div class="modern-list-item bg-gray-50 rounded-xl p-4 hover:shadow-lg">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <div class="w-14 h-14 rounded-xl flex items-center justify-center font-bold text-white" style="background:linear-gradient(135deg,#111,#2b2b2b)">
                  {{ substr($book->title, 0, 1) }}
                </div>
                <div>
                  <h6 class="font-semibold text-lg">{{ $book->title }}</h6>
                  <div class="flex items-center space-x-4 text-sm text-gray-600 mt-1">
                    <span class="flex items-center"><i class="fas fa-calendar-alt mr-1"></i>{{ $book->created_at->format('d/m/Y') }}</span>
                    <span class="flex items-center"><i class="fas fa-user mr-1"></i>{{ $book->author->name ?? 'N/A' }}</span>
                    <span class="flex items-center"><i class="fas fa-boxes mr-1"></i>{{ $book->stock }} trong kho</span>
                  </div>
                </div>
              </div>
              <div class="flex items-center space-x-3">
                <span class="badge-soft blue"><i class="fas fa-eye"></i>{{ number_format($book->views) }}</span>
                <button class="text-gray-400 hover:text-black transition-colors">
                  <i class="fas fa-chevron-right"></i>
                </button>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>

    <div class="bg-white rounded-2xl p-6">
      <h4 class="text-xl font-bold mb-6 flex items-center">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center mr-3" style="background:linear-gradient(135deg,#111,#2b2b2b)">
          <i class="fas fa-chart-line text-white"></i>
        </div>
        Thống kê nhanh
      </h4>

      <div class="space-y-4">
        <div class="rounded-xl p-4 border border-[var(--line)] bg-white">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
              <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white" style="background:linear-gradient(135deg,#111,#2b2b2b)">
                <i class="fas fa-image"></i>
              </div>
              <div>
                <p class="font-medium">Tổng banner</p>
                <p class="text-xs text-gray-600">Hệ thống quảng cáo</p>
              </div>
            </div>
            <span class="badge-soft blue" style="font-size:1rem"><i class="fas fa-layer-group"></i>{{ \App\Models\Banner::count() }}</span>
          </div>
        </div>

        <div class="rounded-xl p-4 border border-[var(--line)] bg-white">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
              <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white" style="background:linear-gradient(135deg,#111,#2b2b2b)">
                <i class="fas fa-eye"></i>
              </div>
              <div>
                <p class="font-medium">Tổng lượt xem</p>
                <p class="text-xs text-gray-600">Engagement metrics</p>
              </div>
            </div>
            <span class="badge-soft amber" style="font-size:1rem">{{ number_format(\App\Models\Book::sum('views')) }}</span>
          </div>
        </div>

        <div class="rounded-xl p-4 border border-[var(--line)] bg-white">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
              <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white" style="background:linear-gradient(135deg,#111,#2b2b2b)">
                <i class="fas fa-heart"></i>
              </div>
              <div>
                <p class="font-medium">Tổng lượt thích</p>
                <p class="text-xs text-gray-600">User interaction</p>
              </div>
            </div>
            <span class="badge-soft rose" style="font-size:1rem">{{ number_format(\App\Models\Book::sum('likes')) }}</span>
          </div>
        </div>

        <div class="rounded-xl p-4 border border-[var(--line)] bg-white">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
              <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white" style="background:linear-gradient(135deg,#111,#2b2b2b)">
                <i class="fas fa-building"></i>
              </div>
              <div>
                <p class="font-medium">Nhà xuất bản</p>
                <p class="text-xs text-gray-600">Publishers count</p>
              </div>
            </div>
            <span class="badge-soft green" style="font-size:1rem">{{ \App\Models\Publisher::count() }}</span>
          </div>
        </div>
      </div>

      <div class="mt-6 space-y-4">
        <div class="bg-white rounded-xl p-4 border border-[var(--line)]">
          <div class="flex justify-between items-center mb-2">
            <span class="text-sm font-medium">Tỷ lệ tương tác</span>
            <span class="text-sm font-bold">{{ \App\Models\Book::sum('views') > 0 ? round((\App\Models\Book::sum('likes') / \App\Models\Book::sum('views')) * 100, 1) : 0 }}%</span>
          </div>
          <div class="w-full rounded-full h-3 progress-track">
            <div class="h-3 rounded-full progress-fill transition-all duration-1000" style="width: {{ \App\Models\Book::sum('views') > 0 ? round((\App\Models\Book::sum('likes') / \App\Models\Book::sum('views')) * 100, 1) : 0 }}%"></div>
          </div>
        </div>

        <div class="bg-white rounded-xl p-4 border border-[var(--line)]">
          <div class="flex justify-between items-center mb-2">
            <span class="text-sm font-medium">Mục tiêu tháng</span>
            <span class="text-sm font-bold">75%</span>
          </div>
          <div class="w-full rounded-full h-3 progress-track">
            <div class="h-3 rounded-full progress-fill transition-all duration-1000" style="width:75%"></div>
          </div>
        </div>

        <div class="bg-white rounded-xl p-4 border border-[var(--line)]">
          <div class="flex justify-between items-center mb-2">
            <span class="text-sm font-medium">Tỷ lệ hoàn thành đơn</span>
            <span class="text-sm font-bold">85%</span>
          </div>
          <div class="w-full rounded-full h-3 progress-track">
            <div class="h-3 rounded-full progress-fill transition-all duration-1000" style="width:85%"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Modal: Low stock --}}
<div id="lowStockModal" class="modal">
  <div class="modal-content">
    <div class="modal-head">
      <div class="flex items-center justify-between">
        <h3 class="text-2xl font-bold flex items-center"><i class="fas fa-exclamation-triangle mr-3"></i>Sản phẩm sắp hết hàng</h3>
        <button class="text-white hover:opacity-80 text-2xl" onclick="closeLowStockModal()"><i class="fas fa-times"></i></button>
      </div>
      <p class="mt-2 modal-sub">Danh sách các sản phẩm có số lượng tồn kho dưới 10</p>
    </div>

    <div class="p-6 max-h-96 overflow-y-auto">
      <div class="space-y-4" id="lowStockList">
        @php $allLowStockBooks = \App\Models\Book::where('stock', '<', 10)->orderBy('stock', 'asc')->get(); @endphp
        @forelse ($allLowStockBooks as $index => $book)
          <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border-l-4 border-gray-300">
            <div class="flex items-center space-x-4">
              <div class="w-12 h-12 rounded-xl flex items-center justify-center font-bold text-white text-lg" style="background:linear-gradient(135deg,#111,#2b2b2b)">
                {{ $index + 1 }}
              </div>
              <div class="flex-1">
                <h6 class="font-semibold text-lg">{{ $book->title }}</h6>
                <div class="flex items-center space-x-4 text-sm text-gray-600 mt-1">
                  <span class="flex items-center"><i class="fas fa-tag mr-1"></i>{{ $book->category->name ?? 'N/A' }}</span>
                  <span class="flex items-center"><i class="fas fa-user mr-1"></i>{{ $book->author->name ?? 'N/A' }}</span>
                  <span class="flex items-center"><i class="fas fa-dollar-sign mr-1"></i>{{ number_format($book->price) }}₫</span>
                </div>
              </div>
            </div>
            <div class="text-right">
              <span class="badge-soft amber"><i class="fas fa-boxes"></i>{{ $book->stock }} {{ $book->stock == 0 ? 'hết hàng' : 'còn lại' }}</span>
            </div>
          </div>
        @empty
          <div class="text-center py-12 text-gray-600">
            <i class="fas fa-check-circle text-6xl mb-4"></i>
            <h4 class="text-xl font-semibold mb-2">Tuyệt vời!</h4>
            <p>Tất cả sản phẩm đều có đủ hàng trong kho</p>
          </div>
        @endforelse
      </div>
    </div>

    <div class="bg-gray-50 px-6 py-4 rounded-b-2xl">
      <div class="flex justify-between items-center">
        <span class="text-sm text-gray-700">Tổng cộng: <strong>{{ $allLowStockBooks->count() }}</strong> sản phẩm cần bổ sung</span>
        <div class="space-x-2">
          <button class="btn-ghost px-4 py-2 text-sm" onclick="closeLowStockModal()">Đóng</button>
          <button class="btn-primary px-4 py-2 text-sm"><i class="fas fa-download mr-1"></i>Xuất báo cáo</button>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Modal: Today orders --}}
<div id="todayOrdersModal" class="modal">
  <div class="modal-content">
    <div class="modal-head">
      <div class="flex items-center justify-between">
        <h3 class="text-2xl font-bold flex items-center"><i class="fas fa-shopping-cart mr-3"></i>Đơn hàng hôm nay</h3>
        <button class="text-white hover:opacity-80 text-2xl" onclick="closeTodayOrdersModal()"><i class="fas fa-times"></i></button>
      </div>
      <div class="flex items-center justify-between mt-4 text-white">
        <div>
          <p class="modal-sub">{{ now()->format('d/m/Y') }}</p>
          <p class="text-2xl font-bold">{{ \App\Models\Order::whereDate('created_at', now())->count() }} đơn hàng</p>
        </div>
        <div class="text-right">
          <p class="modal-sub">Doanh thu (completed)</p>
          <p class="text-2xl font-bold">{{ number_format(\App\Models\Order::whereDate('created_at', now())->where('status','completed')->sum('total_price') ?? 0) }}₫</p>
        </div>
      </div>
    </div>

    <div class="p-6 max-h-96 overflow-y-auto">
      <div class="space-y-4">
        @php
          $todayOrdersList = \App\Models\Order::with(['user', 'orderItems.book'])
              ->whereDate('created_at', now())
              ->orderBy('created_at', 'desc')
              ->get();
        @endphp
        @forelse ($todayOrdersList as $order)
          <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border-l-4 border-gray-300 hover:shadow-md transition-all">
            <div class="flex items-center space-x-4">
              <div class="w-12 h-12 rounded-xl flex items-center justify-center font-bold text-white" style="background:linear-gradient(135deg,#111,#2b2b2b)">#{{ substr($order->id, -3) }}</div>
              <div class="flex-1">
                <h6 class="font-semibold">{{ $order->user->name ?? 'Khách hàng' }}</h6>
                <div class="flex items-center space-x-4 text-sm text-gray-600 mt-1">
                  <span class="flex items-center"><i class="fas fa-clock mr-1"></i>{{ $order->created_at->format('H:i') }}</span>
                  <span class="flex items-center"><i class="fas fa-shopping-bag mr-1"></i>{{ $order->orderItems->count() }} SP</span>
                  <span class="flex items-center"><i class="fas fa-credit-card mr-1"></i>{{ $order->payment_method ?? 'N/A' }}</span>
                </div>
              </div>
            </div>
            <div class="text-right">
              <div class="text-lg font-bold">{{ number_format($order->total_price) }}₫</div>
              @php
                $st = strtolower($order->status ?? 'pending');
                $cls = $st==='completed' ? 'green' : ($st==='pending' ? 'amber' : 'blue');
              @endphp
              <span class="badge-soft {{ $cls }}">{{ ucfirst($order->status ?? 'pending') }}</span>
            </div>
          </div>
        @empty
          <div class="text-center py-12 text-gray-600">
            <i class="fas fa-shopping-cart text-6xl mb-4"></i>
            <h4 class="text-xl font-semibold mb-2">Chưa có đơn hàng</h4>
            <p>Hôm nay chưa có đơn hàng nào được đặt</p>
          </div>
        @endforelse
      </div>
    </div>

    <div class="bg-gray-50 px-6 py-4 rounded-b-2xl">
      <div class="flex justify-between items-center">
        <div class="text-sm text-gray-700">
          <div class="grid grid-cols-3 gap-4">
            <div><span class="font-medium">Hôm qua: </span><span>{{ \App\Models\Order::whereDate('created_at', now()->subDay())->count() }} đơn</span></div>
            <div><span class="font-medium">Tuần này: </span><span>{{ \App\Models\Order::whereBetween('created_at', [now()->startOfWeek(), now()])->count() }} đơn</span></div>
            <div><span class="font-medium">Tháng này: </span><span>{{ \App\Models\Order::whereMonth('created_at', now()->month)->count() }} đơn</span></div>
          </div>
        </div>
        <div class="space-x-2">
          <button class="btn-ghost px-4 py-2 text-sm" onclick="closeTodayOrdersModal()">Đóng</button>
          <button class="btn-primary px-4 py-2 text-sm"><i class="fas fa-download mr-1"></i>Xuất báo cáo</button>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // bật theme mono
  document.addEventListener('DOMContentLoaded',()=>document.body.classList.add('ui-mono'));

  function showLowStockModal(){document.getElementById('lowStockModal').classList.add('show')}
  function closeLowStockModal(){document.getElementById('lowStockModal').classList.remove('show')}
  function showTodayOrdersModal(){document.getElementById('todayOrdersModal').classList.add('show')}
  function closeTodayOrdersModal(){document.getElementById('todayOrdersModal').classList.remove('show')}

  window.onclick=function(e){
    const a=document.getElementById('lowStockModal'),b=document.getElementById('todayOrdersModal');
    if(e.target==a) closeLowStockModal();
    if(e.target==b) closeTodayOrdersModal();
  }

  // ====== DATA from server (fallback only) ======
  const publisherData = {!! json_encode($booksByPublisher ?? []) !!};

  // ====== CHART DEFAULTS ======
  Chart.defaults.font.family = 'Inter, -apple-system, BlinkMacSystemFont, sans-serif';
  Chart.defaults.color = '#111';
  Chart.defaults.borderColor = '#eaeaea';

  // Orders line
  const orderDataSets = {
    day: {
      labels: ['00:00','04:00','08:00','12:00','16:00','20:00','23:59'],
      data: @php
        $hourlyOrders=[]; for($i=0;$i<24;$i+=4){
          $hourlyOrders[]=\App\Models\Order::whereDate('created_at', now())
            ->whereTime('created_at','>=',sprintf('%02d:00:00',$i))
            ->whereTime('created_at','<',sprintf('%02d:00:00',($i+4)%24))->count();
        }
        echo json_encode($hourlyOrders);
      @endphp,
      label:'Đơn hàng theo giờ hôm nay'
    },
    week: {
      labels: ['Thứ 2','Thứ 3','Thứ 4','Thứ 5','Thứ 6','Thứ 7','CN'],
      data: @php
        $weeklyOrders=[]; for($i=0;$i<7;$i++){
          $date=now()->startOfWeek()->addDays($i);
          $weeklyOrders[]=\App\Models\Order::whereDate('created_at',$date)->count();
        }
        echo json_encode($weeklyOrders);
      @endphp,
      label:'Đơn hàng trong tuần'
    },
    month: {
      labels: ['Tuần 1','Tuần 2','Tuần 3','Tuần 4','Tuần 5'],
      data: @php
        $monthlyOrders=[]; for($i=1;$i<=5;$i++){
          $startOfWeek=now()->startOfMonth()->addWeeks($i-1);
          $endOfWeek=$startOfWeek->copy()->endOfWeek();
          if($endOfWeek->month!=now()->month){$endOfWeek=now()->endOfMonth();}
          $monthlyOrders[]=\App\Models\Order::whereBetween('created_at',[$startOfWeek,$endOfWeek])->count();
        }
        echo json_encode($monthlyOrders);
      @endphp,
      label:'Đơn hàng theo tuần trong tháng'
    },
    quarter: {
      labels: @php
        $quarterMonths=[];$startOfQuarter=now()->firstOfQuarter();
        for($i=0;$i<3;$i++){$quarterMonths[]=$startOfQuarter->copy()->addMonths($i)->format('M Y');}
        echo json_encode($quarterMonths);
      @endphp,
      data: @php
        $quarterOrders=[];$startOfQuarter=now()->firstOfQuarter();
        for($i=0;$i<3;$i++){
          $month=$startOfQuarter->copy()->addMonths($i);
          $quarterOrders[]=\App\Models\Order::whereYear('created_at',$month->year)->whereMonth('created_at',$month->month)->count();
        }
        echo json_encode($quarterOrders);
      @endphp,
      label:'Đơn hàng theo tháng trong quý'
    },
    year: {
      labels: ['Q1','Q2','Q3','Q4'],
      data: @php
        $yearlyOrders=[]; for($i=1;$i<=4;$i++){
          $startOfQuarter=now()->setMonth(($i-1)*3+1)->firstOfQuarter();
          $endOfQuarter=$startOfQuarter->copy()->lastOfQuarter();
          $yearlyOrders[]=\App\Models\Order::whereBetween('created_at',[$startOfQuarter,$endOfQuarter])->count();
        }
        echo json_encode($yearlyOrders);
      @endphp,
      label:'Đơn hàng theo quý trong năm'
    }
  };

  const orderCtx=document.getElementById('orderChart').getContext('2d');
  let orderChart=new Chart(orderCtx,{
    type:'line',
    data:{
      labels:orderDataSets.week.labels,
      datasets:[{
        label:orderDataSets.week.label,
        data:orderDataSets.week.data,
        borderColor:'#111',
        backgroundColor:'rgba(0,0,0,0.06)',
        borderWidth:3,
        fill:true,
        tension:.35,
        pointBackgroundColor:'#111',
        pointBorderColor:'#fff',
        pointBorderWidth:2,
        pointRadius:4,
        pointHoverRadius:6,
        pointHoverBorderWidth:2
      }]
    },
    options:{
      responsive:true, maintainAspectRatio:false,
      plugins:{
        legend:{display:false},
        tooltip:{
          backgroundColor:'#111', titleColor:'#fff', bodyColor:'#fff',
          borderColor:'#000', borderWidth:1, cornerRadius:8, displayColors:false,
          callbacks:{label:(c)=>c.parsed.y+' đơn hàng'}
        }
      },
      interaction:{intersect:false,mode:'index'},
      scales:{
        y:{beginAtZero:true,grid:{color:'#eaeaea',drawBorder:false},ticks:{stepSize:1,callback:(v)=>v+' đơn'}},
        x:{grid:{display:false},ticks:{maxRotation:0}}
      }
    }
  });

  // ====== Views by Category from API (/api/books) ======
  async function buildViewsFromAPI(){
    const apiUrl = 'http://localhost:8000/api/books';
    let weekMap = new Map();
    let monthMap = new Map();

    try{
      const res = await fetch(apiUrl, { headers:{ 'Accept':'application/json' }});
      if(!res.ok) throw new Error('HTTP '+res.status);
      const data = await res.json();

      const list = Array.isArray(data?.top_rated_books) ? data.top_rated_books : [];
      const now = new Date();
      const d7  = new Date(now);  d7.setDate(d7.getDate()-7);
      const d30 = new Date(now); d30.setDate(d30.getDate()-30);

      const toDate = s => s ? new Date(s) : null;
      const labelOf = b => {
        const name = b?.category?.name ?? b?.category_name;
        return name ? name : `Danh mục #${b?.category_id ?? 'N/A'}`;
      };

      for(const b of list){
        const label = labelOf(b);
        const views = Number(b?.views ?? 0);
        const updated = toDate(b?.updated_at) || toDate(b?.created_at) || now;

        if(updated >= d30){
          monthMap.set(label, (monthMap.get(label) || 0) + views);
        }
        if(updated >= d7){
          weekMap.set(label, (weekMap.get(label) || 0) + views);
        }
      }
    }catch(err){
      console.warn('Books API failed, fallback to server data:', err);
      const viewsWeekFallback = {!! json_encode($viewsByCategoryWeek ?? ($viewsByCategory ?? [])) !!};
      const viewsMonthFallback = {!! json_encode($viewsByCategoryMonth ?? ($viewsByCategory ?? [])) !!};
      Object.entries(viewsWeekFallback).forEach(([k,v])=> weekMap.set(k, Number(v||0)));
      Object.entries(viewsMonthFallback).forEach(([k,v])=> monthMap.set(k, Number(v||0)));
    }

    const toDataset = (m) => {
      const entries = Array.from(m.entries());
      entries.sort((a,b)=>b[1]-a[1]); // desc
      return { labels: entries.map(e=>e[0]), data: entries.map(e=>e[1]) };
    };

    const weekDS  = toDataset(weekMap);
    const monthDS = toDataset(monthMap);

    const viewsCtx = document.getElementById('viewsChart').getContext('2d');
    const buildBG = (arr)=> {
      const max = Math.max(...arr, 0);
      // cột lớn nhất đen, còn lại xám có pha xanh nhạt tí xíu
      return arr.map(v => v===max ? '#111' : '#cfd6df');
    };

    if(window.__viewsChart) { window.__viewsChart.destroy(); }

    window.__viewsDataSets = {
      week:  { labels: weekDS.labels,  data: weekDS.data,  label: 'Lượt đọc theo danh mục (Tuần)' },
      month: { labels: monthDS.labels, data: monthDS.data, label: 'Lượt đọc theo danh mục (Tháng)' }
    };

    window.__viewsChart = new Chart(viewsCtx,{
      type:'bar',
      data:{
        labels: weekDS.labels,
        datasets:[{
          label: 'Lượt đọc theo danh mục (Tuần)',
          data: weekDS.data,
          backgroundColor: buildBG(weekDS.data),
          borderColor:'#111',
          borderWidth:2,
          borderRadius:8,
          borderSkipped:false
        }]
      },
      options:{
        responsive:true, maintainAspectRatio:false,
        plugins:{
          legend:{display:false},
          tooltip:{
            backgroundColor:'#111', titleColor:'#fff', bodyColor:'#fff',
            borderColor:'#000', borderWidth:1, cornerRadius:8, displayColors:false,
            callbacks:{ label:(c)=> (c.parsed.y||0).toLocaleString()+' lượt đọc' }
          }
        },
        scales:{
          y:{beginAtZero:true,grid:{color:'#eaeaea',drawBorder:false},ticks:{callback:(v)=>v.toLocaleString()}},
          x:{grid:{display:false},ticks:{maxRotation:0}}
        }
      }
    });

    // bind switch Tuần/Tháng
    const viewBtns = document.querySelectorAll('#viewsRangeFilter .tab-button');
    viewBtns.forEach(btn=>{
      btn.onclick = () => {
        viewBtns.forEach(b=>b.classList.remove('active'));
        btn.classList.add('active');
        const range = btn.getAttribute('data-range');
        const set = window.__viewsDataSets[range] || window.__viewsDataSets.week;
        const chart = window.__viewsChart;

        chart.data.labels = set.labels;
        chart.data.datasets[0].data = set.data;
        chart.data.datasets[0].label = set.label;
        const max = Math.max(...(set.data||[]), 0);
        chart.data.datasets[0].backgroundColor = (set.data||[]).map(v => v===max ? '#111' : '#cfd6df');
        chart.update();
      };
    });
  }

  // Publisher doughnut (xám + xanh dương nhạt dịu)
  const publisherCtx=document.getElementById('publisherChart').getContext('2d');
  const publisherChart=new Chart(publisherCtx,{
    type:'doughnut',
    data:{
      labels:Object.keys(publisherData),
      datasets:[{
        data:Object.values(publisherData),
        backgroundColor:['#111','#374151','#4b5563','#6b7280','#94a3b8','#cbd5e1','#e5e7eb','#f3f4f6'],
        borderWidth:3,
        borderColor:'#ffffff',
        hoverBorderWidth:5,
        hoverBorderColor:'#ffffff'
      }]
    },
    options:{
      responsive:true, maintainAspectRatio:false,
      plugins:{
        legend:{position:'bottom',labels:{usePointStyle:true,pointStyle:'circle',padding:20,font:{size:12,weight:'500'}}},
        tooltip:{
          backgroundColor:'#111', titleColor:'#fff', bodyColor:'#fff',
          borderColor:'#000', borderWidth:1, cornerRadius:8, displayColors:true,
          callbacks:{
            label:(ctx)=>{
              const total=ctx.dataset.data.reduce((a,b)=>a+b,0);
              const p=((ctx.parsed*100)/total).toFixed(1);
              return ctx.label+': '+ctx.parsed+' sách ('+p+'%)';
            }
          }
        }
      },
      cutout:'60%'
    }
  });

  // Switch order timeframe
  document.addEventListener('DOMContentLoaded',function(){
    const filterButtons=document.querySelectorAll('#orderTimeFilter .tab-button');
    filterButtons.forEach(btn=>{
      btn.addEventListener('click',function(){
        filterButtons.forEach(b=>b.classList.remove('active'));
        this.classList.add('active');
        const period=this.getAttribute('data-period');
        if(orderDataSets[period]){
          orderChart.data.labels=orderDataSets[period].labels;
          orderChart.data.datasets[0].data=orderDataSets[period].data;
          orderChart.data.datasets[0].label=orderDataSets[period].label;
          orderChart.update();
        }
      });
    });
  });

  // Lượt đọc theo danh mục từ API
  document.addEventListener('DOMContentLoaded', buildViewsFromAPI);

  // Clock
  function updateClock(){
    const now=new Date();
    const fmt=new Intl.DateTimeFormat('vi-VN',{year:'numeric',month:'2-digit',day:'2-digit',hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:false});
    document.getElementById('realtime-clock').innerHTML='<i class="fas fa-clock mr-1"></i>'+fmt.format(now);
  }
  setInterval(updateClock,1000);updateClock();

  // Shortcuts
  document.addEventListener('keydown',function(e){
    if(e.ctrlKey&&e.key==='r'){e.preventDefault();location.reload()}
    if(e.key==='Escape'){closeLowStockModal();closeTodayOrdersModal()}
  });
</script>
@endsection
