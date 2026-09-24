<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::livewire('/departments', 'department-manager')->name('departments.index');
Route::livewire('/employees', 'employee-manager')->name('employees.index');
Route::livewire('/shifts', 'shift-manager')->name('shifts.index');
Route::livewire('/schedules', 'schedule-manager')->name('schedules.index');
Route::livewire('/schedules/bulk', 'schedule-bulk-assign')->name('schedules.bulk');
Route::livewire('/schedules/calendar', 'schedule-calendar')->name('schedules.calendar');
Route::livewire('/leave-requests', 'leave-request-manager')->name('leave-requests.index');
