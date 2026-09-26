<?php

use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Schedule;
use Livewire\Component;

new class extends Component
{
    public function with(): array
    {
        return [
            'totalEmployees' => Employee::where('is_active', true)->count(),
            'totalDepartments' => Department::count(),
            'scheduledToday' => Schedule::where('date', now()->format('Y-m-d'))->count(),
            'pendingLeaveRequests' => LeaveRequest::where('status', 'diajukan')->count(),
        ];
    }
};
?>

<div class="max-w-6xl mx-auto py-10 px-4">
    <h1 class="text-2xl font-semibold text-gray-900">Halo, {{ explode(' ', auth()->user()->name)[0] }}</h1>
    <p class="text-sm text-gray-500 mt-1 mb-8">Ringkasan field-attendance hari ini.</p>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-10">
        <div class="p-5 border border-gray-200 rounded-xl bg-white">
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 mb-3">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
            </span>
            <p class="text-sm text-gray-500">Karyawan Aktif</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $totalEmployees }}</p>
        </div>
        <div class="p-5 border border-gray-200 rounded-xl bg-white">
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-violet-50 text-violet-600 mb-3">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3.75h9a.75.75 0 01.75.75V21h-10.5V4.5a.75.75 0 01.75-.75zm10.5 5.25h3.75a.75.75 0 01.75.75V21h-4.5V9z" /></svg>
            </span>
            <p class="text-sm text-gray-500">Departemen</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $totalDepartments }}</p>
        </div>
        <div class="p-5 border border-gray-200 rounded-xl bg-white">
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-teal-50 text-teal-600 mb-3">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
            </span>
            <p class="text-sm text-gray-500">Jadwal Hari Ini</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $scheduledToday }}</p>
        </div>
        <div class="p-5 border border-gray-200 rounded-xl bg-white">
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-amber-50 text-amber-600 mb-3">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </span>
            <p class="text-sm text-gray-500">Izin/Cuti Menunggu</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $pendingLeaveRequests }}</p>
        </div>
    </div>

    <h2 class="text-sm font-medium text-gray-500 mb-3">Akses Cepat</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
        <a href="{{ route('departments.index') }}" class="flex items-center gap-3 p-4 border border-gray-200 rounded-xl bg-white hover:border-indigo-300 hover:shadow-sm transition text-sm font-medium text-gray-700">
            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3.75h9a.75.75 0 01.75.75V21h-10.5V4.5a.75.75 0 01.75-.75zm10.5 5.25h3.75a.75.75 0 01.75.75V21h-4.5V9z" /></svg>
            Departemen
        </a>
        <a href="{{ route('employees.index') }}" class="flex items-center gap-3 p-4 border border-gray-200 rounded-xl bg-white hover:border-indigo-300 hover:shadow-sm transition text-sm font-medium text-gray-700">
            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
            Karyawan
        </a>
        <a href="{{ route('shifts.index') }}" class="flex items-center gap-3 p-4 border border-gray-200 rounded-xl bg-white hover:border-indigo-300 hover:shadow-sm transition text-sm font-medium text-gray-700">
            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            Shift
        </a>
        <a href="{{ route('schedules.index') }}" class="flex items-center gap-3 p-4 border border-gray-200 rounded-xl bg-white hover:border-indigo-300 hover:shadow-sm transition text-sm font-medium text-gray-700">
            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
            Jadwal
        </a>
        <a href="{{ route('leave-requests.index') }}" class="flex items-center gap-3 p-4 border border-gray-200 rounded-xl bg-white hover:border-indigo-300 hover:shadow-sm transition text-sm font-medium text-gray-700">
            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            Izin & Cuti
        </a>
    </div>
</div>
