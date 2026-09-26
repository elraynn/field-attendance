<?php

use App\Http\Controllers\EmployeeExportController;
use App\Http\Controllers\LeaveRequestExportController;
use App\Http\Controllers\ScheduleCalendarExportController;
use App\Http\Controllers\ScheduleCalendarExportRawController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::livewire('/login', 'login')->name('login');

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/login');
})->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::livewire('/', 'dashboard')->name('dashboard');

    Route::livewire('/departments', 'department-manager')->name('departments.index');
    Route::livewire('/employees', 'employee-manager')->name('employees.index');
    Route::get('/employees/export/excel', [EmployeeExportController::class, 'excel'])->name('employees.export.excel');
    Route::get('/employees/export/pdf', [EmployeeExportController::class, 'pdf'])->name('employees.export.pdf');
    Route::livewire('/shifts', 'shift-manager')->name('shifts.index');
    Route::livewire('/schedules', 'schedule-manager')->name('schedules.index');
    Route::livewire('/schedules/bulk', 'schedule-bulk-assign')->name('schedules.bulk');
    Route::livewire('/schedules/calendar', 'schedule-calendar')->name('schedules.calendar');
    Route::livewire('/leave-requests', 'leave-request-manager')->name('leave-requests.index');
    Route::get('/leave-requests/export/excel', [LeaveRequestExportController::class, 'excel'])->name('leave-requests.export.excel');
    Route::get('/leave-requests/export/pdf', [LeaveRequestExportController::class, 'pdf'])->name('leave-requests.export.pdf');
    Route::get('/schedules/calendar/export/excel', [ScheduleCalendarExportController::class, 'excel'])->name('schedules.calendar.export.excel');
    Route::get('/schedules/calendar/export/pdf', [ScheduleCalendarExportController::class, 'pdf'])->name('schedules.calendar.export.pdf');

    // Sementara, buat belajar — bandingin sama versi di atas, hasilnya harus sama persis.
    Route::get('/schedules/calendar/export/excel-raw', [ScheduleCalendarExportRawController::class, 'excel']);
    Route::get('/schedules/calendar/export/pdf-raw', [ScheduleCalendarExportRawController::class, 'pdf']);
});
