<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::livewire('/departments', 'department-manager')->name('departments.index');
Route::livewire('/employees', 'employee-manager')->name('employees.index');
Route::livewire('/shifts', 'shift-manager')->name('shifts.index');
Route::livewire('/schedules', 'schedule-manager')->name('schedules.index');
