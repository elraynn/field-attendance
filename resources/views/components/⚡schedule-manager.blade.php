<?php

use App\Models\Shift;
use App\Models\Employee;

use App\Models\Schedule;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $date = '';
    public string $shift_id = '';
    public string $employee_id = '';
    public bool $showForm = false;
    public ?int $editingId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $scheduleId): void
    {
        $schedule = Schedule::findOrFail($scheduleId);
        $this->date = $schedule->date;
        $this->shift_id = $schedule->shift_id;
        $this->employee_id = $schedule->employee_id;
        $this->editingId = $schedule->id;

        $this->showForm = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->editingId) {
            Schedule::findOrFail($this->editingId)->update($validated);
        } else {
            Schedule::create($validated);
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function delete(int $scheduleId): void
    {
        Schedule::findOrFail($scheduleId)->delete();
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    protected function rules(): array
    {
        return [
            'date' => [
                'required',
                'date',
                Rule::unique('schedules', 'date')
                    ->where(fn ($query) => $query->where('employee_id', $this->employee_id))
                    ->ignore($this->editingId),
            ],
            'shift_id' => ['required', 'integer', 'exists:shifts,id'],
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
        ];
    }

    protected function messages(): array
    {
        return [
            'date.unique' => 'Karyawan ini sudah punya jadwal di tanggal tersebut.',
        ];
    }

    private function resetForm(): void
    {
        $this->reset(['employee_id', 'date', 'shift_id', 'editingId']);
        $this->resetErrorBag();
    }

    public function with(): array
    {
        return [
            'schedules' => Schedule::query()
                ->with('shift')
                ->with('employee')
                ->when($this->search, fn ($query) => $query->where('date', 'like', "%{$this->search}%"))
                ->orderBy('date')
                ->paginate(10),
            'shifts' => Shift::orderBy('name')->get(),
            'employees' => Employee::orderBy('first_name')->get(),

        ];
    }
};
?>

<div class="max-w-4xl mx-auto py-10 px-4">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Schedule</h1>
        <button wire:click="create" class="px-4 py-2 bg-gray-900 text-white rounded-md text-sm hover:bg-gray-700">
            + Tambah Schedule
        </button>
    </div>

    <input
        type="text"
        wire:model.live.debounce.300ms="search"
        placeholder="Cari Schedule..."
        class="w-full mb-4 rounded-md border-gray-300 focus:border-blue-300 focus:ring"
    >

    @if ($showForm)
        <div class="mb-6 p-4 border border-gray-200 rounded-md bg-gray-50">
            <form wire:submit="save" x-on:keydown.enter.prevent class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tanggal Shift</label>
                    <input type="date" wire:model="date" class="w-full rounded-md border-gray-300">
                    @error('date') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Karyawan</label>
                    <select wire:model="employee_id" class="w-full rounded-md border-gray-300">
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->first_name }}</option>
                        @endforeach
                    </select>
                    @error('employee_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Shift</label>
                    <select wire:model="shift_id" class="w-full rounded-md border-gray-300">
                        <option value="">-- Pilih Shift --</option>
                        @foreach ($shifts as $shift)
                            <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                        @endforeach
                    </select>
                    @error('shift_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-md text-sm">
                        Simpan
                    </button>
                    <button type="button" wire:click="cancel" class="px-4 py-2 border border-gray-300 rounded-md text-sm">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    @endif

    <table class="w-full text-sm border border-gray-200 rounded-md overflow-hidden">
        <thead class="bg-gray-100 text-left text-gray-600">
            <tr>
                <th class="px-4 py-2">Tanggal Shift</th>
                <th class="px-4 py-2">Karyawan</th>
                <th class="px-4 py-2">Shift</th>
                <th class="px-4 py-2"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($schedules as $schedule)
                <tr wire:key="schedule-{{ $schedule->id }}" class="border-t border-gray-200">
                    <td class="px-4 py-2">{{ $schedule->date }}</td>
                    <td class="px-4 py-2">{{ $schedule->employee?->first_name ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $schedule->shift?->name ?? '-' }}</td>
                    <td class="px-4 py-2 text-right space-x-2">
                        <button wire:click="edit({{ $schedule->id }})" class="text-blue-600 hover:underline">Edit</button>
                        <button wire:click="delete({{ $schedule->id }})" wire:confirm="Hapus Schedule ini?" class="text-red-600 hover:underline">Hapus</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-gray-400">Belum ada Schedule.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        {{ $schedules->links() }}
    </div>
</div>
