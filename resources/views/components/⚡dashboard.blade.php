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
    <h1 class="text-2xl font-semibold text-gray-900 mb-6">Dashboard</h1>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-10">
        <div class="p-4 border border-gray-200 rounded-md bg-white">
            <p class="text-sm text-gray-500">Karyawan Aktif</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $totalEmployees }}</p>
        </div>
        <div class="p-4 border border-gray-200 rounded-md bg-white">
            <p class="text-sm text-gray-500">Departemen</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $totalDepartments }}</p>
        </div>
        <div class="p-4 border border-gray-200 rounded-md bg-white">
            <p class="text-sm text-gray-500">Jadwal Hari Ini</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $scheduledToday }}</p>
        </div>
        <div class="p-4 border border-gray-200 rounded-md bg-white">
            <p class="text-sm text-gray-500">Izin/Cuti Menunggu</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $pendingLeaveRequests }}</p>
        </div>
    </div>

    <h2 class="text-sm font-medium text-gray-500 mb-3">Akses Cepat</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
        <a href="{{ route('departments.index') }}" class="p-4 border border-gray-200 rounded-md bg-white hover:border-gray-400 text-sm font-medium text-gray-700">Departemen</a>
        <a href="{{ route('employees.index') }}" class="p-4 border border-gray-200 rounded-md bg-white hover:border-gray-400 text-sm font-medium text-gray-700">Karyawan</a>
        <a href="{{ route('shifts.index') }}" class="p-4 border border-gray-200 rounded-md bg-white hover:border-gray-400 text-sm font-medium text-gray-700">Shift</a>
        <a href="{{ route('schedules.index') }}" class="p-4 border border-gray-200 rounded-md bg-white hover:border-gray-400 text-sm font-medium text-gray-700">Jadwal</a>
        <a href="{{ route('leave-requests.index') }}" class="p-4 border border-gray-200 rounded-md bg-white hover:border-gray-400 text-sm font-medium text-gray-700">Izin & Cuti</a>
    </div>
</div>
