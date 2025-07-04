@extends('layouts.app')

@section('content')
    {{-- Thêm CSS và JS libraries --}}
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .gradient-card {
            background: linear-gradient(135deg, var(--tw-gradient-stops));
            transition: all 0.3s ease;
        }
        
        .gradient-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .chart-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .modern-list-item {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        
        .modern-list-item:hover {
            background: rgba(59, 130, 246, 0.05);
            border-left-color: #3b82f6;
            transform: translateX(5px);
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
    </style>

    {{-- Header Section --}}
    <div class="bg-white shadow-2xl rounded-2xl mb-8 mx-4 lg:mx-0">
        <div class="px-6 py-6">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center mr-4">
                        <i class="fas fa-tachometer-alt text-white text-xl"></i>
                    </div>
                    Trang Quản Trị
                </h1>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <i class="fas fa-bell text-gray-500 text-xl cursor-pointer hover:text-blue-500 transition-colors"></i>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">3</span>
                    </div>
                    <div class="text-sm text-gray-600">
                        <i class="fas fa-clock mr-1"></i>
                        {{ now()->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-6">
        {{-- Thống kê nhanh - Nâng cấp cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            {{-- Tổng số sách --}}
            <div class="gradient-card from-blue-500 to-blue-600 text-white rounded-2xl p-6 animate-float">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center mb-2">
                            <i class="fas fa-book text-blue-100 mr-2"></i>
                            <h6 class="text-blue-100 text-sm font-medium">Tổng số sách</h6>
                        </div>
                        <h2 class="text-4xl font-bold mb-2">{{ \App\Models\Book::count() }}</h2>
                        <p class="text-blue-100 text-sm">
                            <i class="fas fa-arrow-up mr-1"></i>
                            +12% so với tháng trước
                        </p>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded-2xl p-4">
                        <i class="fas fa-book text-3xl opacity-80"></i>
                    </div>
                </div>
                <div class="mt-4 bg-white bg-opacity-10 rounded-lg p-2">
                    <div class="flex justify-between text-xs">
                        <span>Mục tiêu: 1,500</span>
                        <span>{{ round((\App\Models\Book::count() / 1500) * 100) }}%</span>
                    </div>
                    <div class="w-full bg-white bg-opacity-20 rounded-full h-2 mt-1">
                        <div class="bg-white rounded-full h-2" style="width: {{ round((\App\Models\Book::count() / 1500) * 100) }}%"></div>
                    </div>
                </div>
            </div>

            {{-- Tác giả --}}
            <div class="gradient-card from-green-500 to-green-600 text-white rounded-2xl p-6 animate-float" style="animation-delay: 0.1s;">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center mb-2">
                            <i class="fas fa-users text-green-100 mr-2"></i>
                            <h6 class="text-green-100 text-sm font-medium">Tác giả</h6>
                        </div>
                        <h2 class="text-4xl font-bold mb-2">{{ \App\Models\Author::count() }}</h2>
                        <p class="text-green-100 text-sm">
                            <i class="fas fa-arrow-up mr-1"></i>
                            +5% so với tháng trước
                        </p>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded-2xl p-4">
                        <i class="fas fa-users text-3xl opacity-80"></i>
                    </div>
                </div>
                <div class="mt-4 bg-white bg-opacity-10 rounded-lg p-2">
                    <div class="flex justify-between text-xs">
                        <span>Mục tiêu: 100</span>
                        <span>{{ round((\App\Models\Author::count() / 100) * 100) }}%</span>
                    </div>
                    <div class="w-full bg-white bg-opacity-20 rounded-full h-2 mt-1">
                        <div class="bg-white rounded-full h-2" style="width: {{ round((\App\Models\Author::count() / 100) * 100) }}%"></div>
                    </div>
                </div>
            </div>

            {{-- Nhà xuất bản --}}
            <div class="gradient-card from-yellow-500 to-yellow-600 text-white rounded-2xl p-6 animate-float" style="animation-delay: 0.2s;">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center mb-2">
                            <i class="fas fa-building text-yellow-100 mr-2"></i>
                            <h6 class="text-yellow-100 text-sm font-medium">Nhà xuất bản</h6>
                        </div>
                        <h2 class="text-4xl font-bold mb-2">{{ \App\Models\Publisher::count() }}</h2>
                        <p class="text-yellow-100 text-sm">
                            <i class="fas fa-arrow-up mr-1"></i>
                            +2% so với tháng trước
                        </p>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded-2xl p-4">
                        <i class="fas fa-building text-3xl opacity-80"></i>
                    </div>
                </div>
                <div class="mt-4 bg-white bg-opacity-10 rounded-lg p-2">
                    <div class="flex justify-between text-xs">
                        <span>Mục tiêu: 30</span>
                        <span>{{ round((\App\Models\Publisher::count() / 30) * 100) }}%</span>
                    </div>
                    <div class="w-full bg-white bg-opacity-20 rounded-full h-2 mt-1">
                        <div class="bg-white rounded-full h-2" style="width: {{ round((\App\Models\Publisher::count() / 30) * 100) }}%"></div>
                    </div>
                </div>
            </div>

            {{-- Danh mục --}}
            <div class="gradient-card from-red-500 to-red-600 text-white rounded-2xl p-6 animate-float" style="animation-delay: 0.3s;">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center mb-2">
                            <i class="fas fa-folder text-red-100 mr-2"></i>
                            <h6 class="text-red-100 text-sm font-medium">Danh mục</h6>
                        </div>
                        <h2 class="text-4xl font-bold mb-2">{{ \App\Models\Category::count() }}</h2>
                        <p class="text-red-100 text-sm">
                            <i class="fas fa-arrow-up mr-1"></i>
                            +1% so với tháng trước
                        </p>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded-2xl p-4">
                        <i class="fas fa-folder text-3xl opacity-80"></i>
                    </div>
                </div>
                <div class="mt-4 bg-white bg-opacity-10 rounded-lg p-2">
                    <div class="flex justify-between text-xs">
                        <span>Mục tiêu: 20</span>
                        <span>{{ round((\App\Models\Category::count() / 20) * 100) }}%</span>
                    </div>
                    <div class="w-full bg-white bg-opacity-20 rounded-full h-2 mt-1">
                        <div class="bg-white rounded-full h-2" style="width: {{ round((\App\Models\Category::count() / 20) * 100) }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Biểu đồ section --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            {{-- Biểu đồ lượt xem theo danh mục --}}
            <div class="chart-container rounded-2xl shadow-2xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h4 class="text-xl font-bold text-gray-800 flex items-center">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center mr-3">
                            <i class="fas fa-chart-bar text-white"></i>
                        </div>
                        Lượt xem theo danh mục
                    </h4>
                    <div class="flex space-x-2">
                        <button class="px-4 py-2 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition-all text-sm font-medium">
                            <i class="fas fa-calendar-week mr-1"></i>
                            Tuần
                        </button>
                        <button class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-all text-sm font-medium">
                            <i class="fas fa-calendar-alt mr-1"></i>
                            Tháng
                        </button>
                    </div>
                </div>
                <div class="relative">
                    <canvas id="viewsChart" style="max-height: 350px;"></canvas>
                </div>
            </div>

            {{-- Biểu đồ số lượng sách theo nhà xuất bản --}}
            <div class="chart-container rounded-2xl shadow-2xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h4 class="text-xl font-bold text-gray-800 flex items-center">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center mr-3">
                            <i class="fas fa-chart-pie text-white"></i>
                        </div>
                        Sách theo nhà xuất bản
                    </h4>
                    <button class="px-4 py-2 bg-purple-100 text-purple-600 rounded-lg hover:bg-purple-200 transition-all text-sm font-medium">
                        <i class="fas fa-external-link-alt mr-1"></i>
                        Chi tiết
                    </button>
                </div>
                <div class="relative">
                    <canvas id="publisherChart" style="max-height: 350px;"></canvas>
                </div>
            </div>
        </div>

        {{-- Content section --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Danh sách sách mới nhất --}}
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-2xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h4 class="text-xl font-bold text-gray-800 flex items-center">
                        <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center mr-3">
                            <i class="fas fa-book-open text-white"></i>
                        </div>
                        Sách mới thêm gần đây
                    </h4>
                    <button class="px-4 py-2 bg-green-100 text-green-600 rounded-lg hover:bg-green-200 transition-all text-sm font-medium">
                        <i class="fas fa-eye mr-1"></i>
                        Xem tất cả
                    </button>
                </div>
                <div class="space-y-3">
                    @foreach ($recentBooks as $book)
                        <div class="modern-list-item bg-gray-50 rounded-xl p-4 hover:shadow-lg">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center text-white font-bold">
                                        {{ substr($book->title, 0, 1) }}
                                    </div>
                                    <div>
                                        <h6 class="font-semibold text-gray-800 text-lg">{{ $book->title }}</h6>
                                        <p class="text-sm text-gray-500 flex items-center">
                                            <i class="fas fa-calendar-alt mr-1"></i>
                                            {{ $book->created_at->format('d/m/Y') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="bg-blue-100 text-blue-600 px-3 py-1 rounded-full text-sm font-semibold flex items-center">
                                        <i class="fas fa-eye mr-1"></i>
                                        {{ $book->views }}
                                    </span>
                                    <button class="text-gray-400 hover:text-blue-500 transition-colors">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Thống kê phụ --}}
            <div class="bg-white rounded-2xl shadow-2xl p-6">
                <h4 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center mr-3">
                        <i class="fas fa-chart-line text-white"></i>
                    </div>
                    Thống kê nhanh
                </h4>
                <div class="space-y-4">
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-4 border-l-4 border-blue-500">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-image text-white"></i>
                                </div>
                                <div>
                                    <p class="text-gray-700 font-medium">Tổng banner</p>
                                    <p class="text-xs text-gray-500">Hệ thống quảng cáo</p>
                                </div>
                            </div>
                            <span class="bg-blue-100 text-blue-600 px-3 py-1 rounded-full text-lg font-bold">
                                {{ \App\Models\Banner::count() }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-4 border-l-4 border-green-500">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-eye text-white"></i>
                                </div>
                                <div>
                                    <p class="text-gray-700 font-medium">Tổng lượt xem</p>
                                    <p class="text-xs text-gray-500">Engagement metrics</p>
                                </div>
                            </div>
                            <span class="bg-green-100 text-green-600 px-3 py-1 rounded-full text-lg font-bold">
                                {{ number_format(\App\Models\Book::sum('views')) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-red-50 to-pink-50 rounded-xl p-4 border-l-4 border-red-500">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-pink-600 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-heart text-white"></i>
                                </div>
                                <div>
                                    <p class="text-gray-700 font-medium">Tổng lượt thích</p>
                                    <p class="text-xs text-gray-500">User interaction</p>
                                </div>
                            </div>
                            <span class="bg-red-100 text-red-600 px-3 py-1 rounded-full text-lg font-bold">
                                {{ number_format(\App\Models\Book::sum('likes')) }}
                            </span>
                        </div>
                    </div>
                </div>
                
                {{-- Performance metrics --}}
                <div class="mt-6 space-y-4">
                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700">Tỷ lệ tương tác</span>
                            <span class="text-sm font-bold text-gray-800">
                                {{ \App\Models\Book::sum('views') > 0 ? round((\App\Models\Book::sum('likes') / \App\Models\Book::sum('views')) * 100, 1) : 0 }}%
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-gradient-to-r from-green-400 to-emerald-500 h-3 rounded-full transition-all duration-1000" 
                                 style="width: {{ \App\Models\Book::sum('views') > 0 ? round((\App\Models\Book::sum('likes') / \App\Models\Book::sum('views')) * 100, 1) : 0 }}%"></div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700">Mục tiêu tháng</span>
                            <span class="text-sm font-bold text-gray-800">75%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-gradient-to-r from-blue-400 to-purple-500 h-3 rounded-full transition-all duration-1000" style="width: 75%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Dữ liệu từ Laravel - giữ nguyên cấu trúc gốc
        const viewsData = {!! json_encode($viewsByCategory) !!};
        const publisherData = {!! json_encode($booksByPublisher) !!};

        // Biểu đồ lượt xem theo danh mục - Enhanced
        const viewsCtx = document.getElementById('viewsChart').getContext('2d');
        new Chart(viewsCtx, {
            type: 'bar',
            data: {
                labels: viewsData.map(item => item.label),
                datasets: [{
                    label: 'Lượt xem',
                    data: viewsData.map(item => item.views),
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                    hoverBackgroundColor: 'rgba(59, 130, 246, 0.9)',
                    hoverBorderColor: 'rgba(59, 130, 246, 1)',
                    hoverBorderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: 'white',
                        bodyColor: 'white',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1,
                        cornerRadius: 8
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)',
                            drawBorder: false
                        },
                        ticks: {
                            color: 'rgba(0, 0, 0, 0.6)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: 'rgba(0, 0, 0, 0.6)'
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });

        // Biểu đồ sách theo nhà xuất bản - Enhanced
        const publisherCtx = document.getElementById('publisherChart').getContext('2d');
        new Chart(publisherCtx, {
            type: 'doughnut',
            data: {
                labels: publisherData.map(item => item.name),
                datasets: [{
                    label: 'Số lượng sách',
                    data: publisherData.map(item => item.books_count),
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(139, 92, 246, 0.8)'
                    ],
                    borderColor: [
                        'rgba(59, 130, 246, 1)',
                        'rgba(16, 185, 129, 1)',
                        'rgba(245, 158, 11, 1)',
                        'rgba(239, 68, 68, 1)',
                        'rgba(139, 92, 246, 1)'
                    ],
                    borderWidth: 3,
                    hoverBorderWidth: 5,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: {
                                size: 12,
                                weight: 'bold'
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: 'white',
                        bodyColor: 'white',
                        borderColor: 'rgba(255, 255, 255, 0.2)',
                        borderWidth: 1,
                        cornerRadius: 8
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });

        // Add smooth scrolling and loading animations
        document.addEventListener('DOMContentLoaded', function() {
            // Animate cards on scroll
            const cards = document.querySelectorAll('.gradient-card, .chart-container');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            });

            cards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'all 0.6s ease';
                observer.observe(card);
            });
        });
    </script>
@endsection