<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="bg-gray-50 text-gray-900 antialiased min-h-screen flex flex-col">
        <nav class="bg-white border-b border-gray-200">
            <div class="max-w-6xl mx-auto px-4 flex flex-wrap items-center justify-between gap-2 py-2">
                <a href="{{ route('dashboard') }}" class="font-semibold text-gray-900">Field Attendance</a>

                <div class="flex flex-wrap gap-1 text-sm">
                    <a href="{{ route('departments.index') }}" class="px-3 py-1.5 rounded-md {{ request()->routeIs('departments.*') ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Departemen</a>
                    <a href="{{ route('employees.index') }}" class="px-3 py-1.5 rounded-md {{ request()->routeIs('employees.*') ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Karyawan</a>
                    <a href="{{ route('shifts.index') }}" class="px-3 py-1.5 rounded-md {{ request()->routeIs('shifts.*') ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Shift</a>
                    <a href="{{ route('schedules.index') }}" class="px-3 py-1.5 rounded-md {{ request()->routeIs('schedules.*') ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Jadwal</a>
                    <a href="{{ route('leave-requests.index') }}" class="px-3 py-1.5 rounded-md {{ request()->routeIs('leave-requests.*') ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Izin & Cuti</a>
                </div>
            </div>
        </nav>

        <main class="flex-1">
            {{ $slot }}
        </main>

        <footer class="border-t border-gray-200 py-4">
            <p class="text-center text-xs text-gray-400">{{ config('app.name') }} &copy; {{ now()->year }}</p>
        </footer>

        @livewireScripts
    </body>
</html>
