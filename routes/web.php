<?php

use App\Http\Controllers\EmployeeExportController;
use Illuminate\Support\Facades\Route;

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
