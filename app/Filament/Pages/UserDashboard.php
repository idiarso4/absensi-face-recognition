<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Schedule;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class UserDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.user-dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = 1;

    public function getTitle(): string
    {
        return 'Dashboard ' . Auth::user()->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('check_in')
                ->label('Absen Masuk')
                ->icon('heroicon-o-clock')
                ->color('success')
                ->url(route('presensi') . '?action=checkin')
                ->visible(!$this->hasCheckedInToday()),

            Action::make('check_out')
                ->label('Absen Keluar')
                ->icon('heroicon-o-arrow-right-on-rectangle')
                ->color('warning')
                ->url(route('presensi') . '?action=checkout')
                ->visible($this->hasCheckedInToday() && !$this->hasCheckedOutToday()),

            Action::make('request_leave')
                ->label('Ajukan Cuti')
                ->icon('heroicon-o-calendar-days')
                ->color('info')
                ->url('/admin/leaves/create'),

            Action::make('view_schedule')
                ->label('Lihat Jadwal')
                ->icon('heroicon-o-calendar')
                ->url('/admin/schedules'),
        ];
    }

    public function hasCheckedInToday(): bool
    {
        return Attendance::where('user_id', Auth::id())
            ->whereDate('check_in', today())
            ->exists();
    }

    public function hasCheckedOutToday(): bool
    {
        return Attendance::where('user_id', Auth::id())
            ->whereDate('check_out', today())
            ->exists();
    }

    public function getTodaysAttendance()
    {
        return Attendance::where('user_id', Auth::id())
            ->whereDate('check_in', today())
            ->first();
    }

    public function getPendingLeaves()
    {
        return Leave::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->count();
    }

    public function getTodaysSchedule()
    {
        return Schedule::where('user_id', Auth::id())
            ->where('date', today())
            ->first();
    }

    protected function getViewData(): array
    {
        return [
            'todaysAttendance' => $this->getTodaysAttendance(),
            'pendingLeaves' => $this->getPendingLeaves(),
            'todaysSchedule' => $this->getTodaysSchedule(),
        ];
    }
}
