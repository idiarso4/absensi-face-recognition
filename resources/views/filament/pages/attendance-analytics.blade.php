<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg p-6 text-white">
            <h1 class="text-3xl font-bold mb-2">Analytics Absensi</h1>
            <p class="text-blue-100">Pantau performa kehadiran dan statistik karyawan</p>
        </div>

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Total Karyawan</h3>
                        <p class="text-3xl font-bold text-blue-600">{{ $stats['total_users'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Absensi Bulan Ini</h3>
                        <p class="text-3xl font-bold text-green-600">{{ $stats['total_attendances_this_month'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Tepat Waktu</h3>
                        <p class="text-3xl font-bold text-yellow-600">{{ $stats['on_time_percentage'] }}%</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-red-100 rounded-lg">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Tidak Hadir Hari Ini</h3>
                        <p class="text-3xl font-bold text-red-600">{{ $stats['absent_today'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Monthly Attendance Chart -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Tren Absensi 12 Bulan Terakhir</h3>
                <canvas id="monthlyChart" width="400" height="200"></canvas>
            </div>

            <!-- Daily Attendance Chart -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Absensi 7 Hari Terakhir</h3>
                <canvas id="dailyChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Bottom Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top Performers -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Top Performer Bulan Ini</h3>
                <div class="space-y-3">
                    @foreach($topPerformers as $index => $performer)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-sm font-semibold text-blue-600">
                                    {{ $index + 1 }}
                                </div>
                                <div class="ml-3">
                                    <p class="font-medium text-gray-900">{{ $performer->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $performer->email }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900">{{ $performer->attendances_count }}</p>
                                <p class="text-xs text-gray-500">absensi</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Department/Role Stats -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Distribusi Peran</h3>
                <div class="space-y-3">
                    @foreach($departmentStats as $stat)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                                <span class="ml-3 font-medium text-gray-900">{{ $stat->role_name }}</span>
                            </div>
                            <span class="font-semibold text-gray-900">{{ $stat->user_count }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Monthly Chart
            const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
            const monthlyData = @json($monthlyData);

            new Chart(monthlyCtx, {
                type: 'line',
                data: {
                    labels: monthlyData.map(item => item.month),
                    datasets: [{
                        label: 'Jumlah Absensi',
                        data: monthlyData.map(item => item.count),
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });

            // Daily Chart
            const dailyCtx = document.getElementById('dailyChart').getContext('2d');
            const dailyData = @json($dailyData);

            new Chart(dailyCtx, {
                type: 'bar',
                data: {
                    labels: dailyData.map(item => item.day),
                    datasets: [{
                        label: 'Jumlah Absensi',
                        data: dailyData.map(item => item.count),
                        backgroundColor: 'rgba(34, 197, 94, 0.8)',
                        borderColor: 'rgb(34, 197, 94)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-filament-panels::page>
