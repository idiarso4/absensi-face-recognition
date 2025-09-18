<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Welcome Section -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg p-6 text-white">
            <h1 class="text-2xl font-bold mb-2">Selamat Datang, {{ auth()->user()->name }}!</h1>
            <p class="text-blue-100">Semoga hari Anda menyenangkan dan produktif.</p>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Today's Attendance -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Status Absensi Hari Ini</h3>
                        @if($todaysAttendance)
                            <p class="text-green-600 font-medium">
                                Masuk: {{ $todaysAttendance->check_in->format('H:i') }}
                                @if($todaysAttendance->check_out)
                                    | Keluar: {{ $todaysAttendance->check_out->format('H:i') }}
                                @endif
                            </p>
                        @else
                            <p class="text-gray-500">Belum absen masuk</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Pending Leaves -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Cuti Pending</h3>
                        <p class="text-2xl font-bold text-yellow-600">{{ $pendingLeaves }}</p>
                    </div>
                </div>
            </div>

            <!-- Today's Schedule -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Jadwal Hari Ini</h3>
                        @if($todaysSchedule)
                            <p class="text-blue-600 font-medium">
                                {{ $todaysSchedule->shift->name }} ({{ $todaysSchedule->shift->start_time }} - {{ $todaysSchedule->shift->end_time }})
                            </p>
                        @else
                            <p class="text-gray-500">Tidak ada jadwal</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Aksi Cepat</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('presensi') }}?action=checkin"
                   class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors {{ $this->hasCheckedInToday() ? 'opacity-50 cursor-not-allowed' : '' }}">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h4 class="font-medium text-gray-900">Absen Masuk</h4>
                        <p class="text-sm text-gray-500">Catat waktu masuk</p>
                    </div>
                </a>

                <a href="{{ route('presensi') }}?action=checkout"
                   class="flex items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors {{ !$this->hasCheckedInToday() || $this->hasCheckedOutToday() ? 'opacity-50 cursor-not-allowed' : '' }}">
                    <div class="p-2 bg-orange-100 rounded-lg">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h4 class="font-medium text-gray-900">Absen Keluar</h4>
                        <p class="text-sm text-gray-500">Catat waktu keluar</p>
                    </div>
                </a>

                <a href="/admin/leaves/create"
                   class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h4 class="font-medium text-gray-900">Ajukan Cuti</h4>
                        <p class="text-sm text-gray-500">Buat permohonan cuti</p>
                    </div>
                </a>

                <a href="/admin/schedules"
                   class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h4 class="font-medium text-gray-900">Lihat Jadwal</h4>
                        <p class="text-sm text-gray-500">Jadwal kerja Anda</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Aktivitas Terbaru</h2>
            <div class="space-y-3">
                @if($todaysAttendance)
                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Absen masuk hari ini</p>
                            <p class="text-xs text-gray-500">{{ $todaysAttendance->check_in->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                @endif

                @if($pendingLeaves > 0)
                    <div class="flex items-center p-3 bg-yellow-50 rounded-lg">
                        <div class="p-2 bg-yellow-100 rounded-lg">
                            <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">{{ $pendingLeaves }} permohonan cuti menunggu persetujuan</p>
                            <p class="text-xs text-gray-500">Periksa status di menu Cuti</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
