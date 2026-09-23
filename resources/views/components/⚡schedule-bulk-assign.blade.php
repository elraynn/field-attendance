<?php

use App\Models\Employee;
use App\Models\Schedule;
use App\Models\Shift;
use Livewire\Component;

new class extends Component
{
    public string $date = '';

    public string $shift_id = '';

    public array $employee_ids = [];

    public ?string $resultMessage = null;

    public function save(): void
    {
        $this->validate([
            'date' => ['required', 'date'],
            'shift_id' => ['required', 'integer', 'exists:shifts,id'],
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['integer', 'exists:employees,id'],
        ]);

        $assigned = 0;
        $skipped = 0;

        foreach ($this->employee_ids as $employeeId) {
            $alreadyScheduled = Schedule::where('employee_id', $employeeId)
                ->where('date', $this->date)
                ->exists();

            if ($alreadyScheduled) {
                $skipped++;

                continue;
            }

            Schedule::create([
                'employee_id' => $employeeId,
                'shift_id' => $this->shift_id,
                'date' => $this->date,
            ]);

            $assigned++;
        }

        $this->resultMessage = "{$assigned} karyawan berhasil dijadwalkan.";

        if ($skipped > 0) {
            $this->resultMessage .= " {$skipped} dilewati karena sudah punya jadwal di tanggal itu.";
        }

        $this->reset(['date', 'shift_id', 'employee_ids']);
    }

    public function with(): array
    {
        return [
            'shifts' => Shift::where('is_active', true)->orderBy('name')->get(),
            'employees' => Employee::where('is_active', true)->orderBy('first_name')->get(),
        ];
    }
};
?>

<div class="max-w-4xl mx-auto py-10 px-4">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Assign Massal Jadwal</h1>
        <a href="{{ route('schedules.index') }}" class="text-sm text-gray-600 hover:underline">&larr; Kembali ke Jadwal</a>
    </div>

    @if ($resultMessage)
        <div class="mb-6 p-4 border border-green-200 rounded-md bg-green-50 text-sm text-green-800">
            {{ $resultMessage }}
        </div>
    @endif

    <form wire:submit="save" x-on:keydown.enter.prevent class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Tanggal</label>
            <input type="date" wire:model="date" class="w-full rounded-md border-gray-300">
            @error('date') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Shift</label>
            <select wire:model="shift_id" class="w-full rounded-md border-gray-300">
                <option value="">-- Pilih Shift --</option>
                @foreach ($shifts as $shift)
                    <option value="{{ $shift->id }}">{{ $shift->name }} ({{ $shift->start_time }}-{{ $shift->end_time }})</option>
                @endforeach
            </select>
            @error('shift_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Karyawan</label>
            <div class="border border-gray-200 rounded-md divide-y divide-gray-100 max-h-80 overflow-y-auto">
                @foreach ($employees as $employee)
                    <label class="flex items-center gap-2 px-3 py-2 text-sm hover:bg-gray-50">
                        <input type="checkbox" wire:model="employee_ids" value="{{ $employee->id }}">
                        {{ $employee->first_name }} {{ $employee->last_name }}
                    </label>
                @endforeach
            </div>
            @error('employee_ids') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            @error('employee_ids.*') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-md text-sm">
            Assign ke {{ count($employee_ids) }} Karyawan
        </button>
    </form>
</div>
