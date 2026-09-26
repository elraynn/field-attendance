<?php

use App\Models\Employee;
use App\Models\Schedule;
use App\Models\Shift;
use Carbon\Carbon;
use Livewire\Component;

new class extends Component
{
    public string $weekStart;

    public ?int $selectedEmployeeId = null;

    public ?string $selectedDate = null;

    public ?int $editingScheduleId = null;

    public string $shift_id = '';

    public bool $showAssignPanel = false;

    public function mount(): void
    {
        $this->weekStart = now()->startOfWeek()->format('Y-m-d');
    }

    public function previousWeek(): void
    {
        $this->weekStart = Carbon::parse($this->weekStart)->subWeek()->format('Y-m-d');
    }

    public function nextWeek(): void
    {
        $this->weekStart = Carbon::parse($this->weekStart)->addWeek()->format('Y-m-d');
    }

    public function selectCell(int $employeeId, string $date, ?int $scheduleId = null, ?int $currentShiftId = null): void
    {
        $this->selectedEmployeeId = $employeeId;
        $this->selectedDate = $date;
        $this->editingScheduleId = $scheduleId;
        $this->shift_id = $currentShiftId ? (string) $currentShiftId : '';
        $this->showAssignPanel = true;
        $this->resetErrorBag();
    }

    public function assign(): void
    {
        $this->validate([
            'shift_id' => ['required', 'integer', 'exists:shifts,id'],
        ]);

        if ($this->editingScheduleId) {
            Schedule::findOrFail($this->editingScheduleId)->update(['shift_id' => $this->shift_id]);
        } else {
            Schedule::create([
                'employee_id' => $this->selectedEmployeeId,
                'shift_id' => $this->shift_id,
                'date' => $this->selectedDate,
            ]);
        }

        $this->closePanel();
    }

    public function removeAssignment(): void
    {
        if ($this->editingScheduleId) {
            Schedule::findOrFail($this->editingScheduleId)->delete();
        }

        $this->closePanel();
    }

    public function closePanel(): void
    {
        $this->reset(['selectedEmployeeId', 'selectedDate', 'editingScheduleId', 'shift_id', 'showAssignPanel']);
    }

    public function with(): array
    {
        $start = Carbon::parse($this->weekStart);
        $dates = collect(range(0, 6))->map(fn ($i) => $start->copy()->addDays($i));

        $employees = Employee::where('is_active', true)->orderBy('first_name')->get();

        $schedules = Schedule::whereBetween('date', [$dates->first()->format('Y-m-d'), $dates->last()->format('Y-m-d')])
            ->with('shift')
            ->get();

        $scheduleMap = [];
        foreach ($schedules as $schedule) {
            $scheduleMap[$schedule->employee_id][$schedule->date] = $schedule;
        }

        return [
            'dates' => $dates,
            'employees' => $employees,
            'scheduleMap' => $scheduleMap,
            'shifts' => Shift::where('is_active', true)->orderBy('name')->get(),
        ];
    }
};
?>

<div class="max-w-6xl mx-auto py-10 px-4">
    <div class="flex items-center justify-between mb-2">
        <h1 class="text-2xl font-semibold text-gray-900">Kalender Jadwal</h1>
        <a href="{{ route('schedules.index') }}" class="text-sm text-gray-600 hover:underline">&larr; Kembali ke Jadwal</a>
    </div>

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('schedules.calendar.export.excel', ['week' => $weekStart]) }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">Export Excel</a>
        <a href="{{ route('schedules.calendar.export.pdf', ['week' => $weekStart]) }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">Export PDF</a>

        <button wire:click="previousWeek" class="px-3 py-1.5 border border-gray-300 rounded-md text-sm hover:bg-gray-50">&larr; Minggu Lalu</button>
        <span class="text-sm text-gray-600">{{ $dates->first()->format('d M') }} - {{ $dates->last()->format('d M Y') }}</span>
        <button wire:click="nextWeek" class="px-3 py-1.5 border border-gray-300 rounded-md text-sm hover:bg-gray-50">Minggu Depan &rarr;</button>
    </div>

    <div class="overflow-x-auto border border-gray-200 rounded-md">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-3 py-2 text-left sticky left-0 bg-gray-100">Karyawan</th>
                    @foreach ($dates as $date)
                        <th class="px-3 py-2 text-center whitespace-nowrap">
                            {{ ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'][$date->dayOfWeek] }}
                            <br>
                            <span class="text-xs text-gray-500 font-normal">{{ $date->format('d/m') }}</span>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($employees as $employee)
                    <tr class="border-t border-gray-200">
                        <td class="px-3 py-2 font-medium whitespace-nowrap sticky left-0 bg-white">{{ $employee->first_name }} {{ $employee->last_name }}</td>
                        @foreach ($dates as $date)
                            @php $schedule = $scheduleMap[$employee->id][$date->format('Y-m-d')] ?? null; @endphp
                            <td class="px-1 py-1 text-center">
                                <button
                                    wire:click="selectCell({{ $employee->id }}, '{{ $date->format('Y-m-d') }}', {{ $schedule?->id ?? 'null' }}, {{ $schedule?->shift_id ?? 'null' }})"
                                    class="w-full py-2 px-1 rounded text-xs whitespace-nowrap {{ $schedule ? 'bg-blue-100 text-blue-700 hover:bg-blue-200' : 'bg-gray-50 text-gray-300 hover:bg-gray-100' }}"
                                >
                                    {{ $schedule?->shift?->name ?? '+' }}
                                </button>
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-center text-gray-400">Belum ada karyawan aktif.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($showAssignPanel)
        <div class="mt-6 p-4 border border-gray-200 rounded-md bg-gray-50 max-w-sm">
            @php $employee = $employees->firstWhere('id', $selectedEmployeeId); @endphp
            <p class="text-sm font-medium text-gray-700 mb-3">
                {{ $employee?->first_name }} {{ $employee?->last_name }} — {{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}
            </p>

            <select wire:model="shift_id" class="w-full rounded-md border-gray-300 mb-2">
                <option value="">-- Pilih Shift --</option>
                @foreach ($shifts as $shift)
                    <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                @endforeach
            </select>
            @error('shift_id') <p class="text-sm text-red-600 mb-2">{{ $message }}</p> @enderror

            <div class="flex gap-2">
                <button wire:click="assign" class="px-3 py-1.5 bg-gray-900 text-white rounded-md text-sm">Simpan</button>
                @if ($editingScheduleId)
                    <button wire:click="removeAssignment" wire:confirm="Hapus jadwal ini?" class="px-3 py-1.5 border border-red-300 text-red-600 rounded-md text-sm">Hapus</button>
                @endif
                <button wire:click="closePanel" class="px-3 py-1.5 border border-gray-300 rounded-md text-sm">Batal</button>
            </div>
        </div>
    @endif
</div>
