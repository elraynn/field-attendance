<?php

use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showForm = false;

    public string $employee_id = '';

    public string $type = 'izin';

    public string $start_date = '';

    public string $end_date = '';

    public string $reason = '';

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function submitRequest(): void
    {
        $validated = $this->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'type' => ['required', Rule::in(['izin', 'cuti'])],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        LeaveRequest::create($validated);

        $this->resetForm();
        $this->showForm = false;
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    public function decide(int $leaveRequestId, string $toStatus): void
    {
        $leaveRequest = LeaveRequest::findOrFail($leaveRequestId);

        try {
            $leaveRequest->status = LeaveRequest::workflow()->apply($leaveRequest->status, $toStatus, auth()->user()->role);
            $leaveRequest->save();
        } catch (\InvalidArgumentException $e) {
            $this->addError('workflow', $e->getMessage());
        }
    }

    private function resetForm(): void
    {
        $this->reset(['employee_id', 'type', 'start_date', 'end_date', 'reason']);
        $this->type = 'izin';
        $this->resetErrorBag();
    }

    public function with(): array
    {
        return [
            'leaveRequests' => LeaveRequest::query()
                ->with('employee')
                ->when($this->search, fn ($query) => $query->whereHas(
                    'employee',
                    fn ($q) => $q->where('first_name', 'like', "%{$this->search}%")
                ))
                ->latest()
                ->paginate(10),
            'employees' => Employee::where('is_active', true)->orderBy('first_name')->get(),
        ];
    }
};
?>

<div class="max-w-5xl mx-auto py-10 px-4">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Izin & Cuti</h1>
        <div class="flex gap-2">
            <a href="{{ route('leave-requests.export.excel') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">
                Export Excel
            </a>
            <a href="{{ route('leave-requests.export.pdf') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">
                Export PDF
            </a>
            <button wire:click="create" class="px-4 py-2 bg-gray-900 text-white rounded-md text-sm hover:bg-gray-700">
                + Ajukan Izin/Cuti
            </button>

        </div>
    </div>

    <div class="mb-4">
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Cari nama karyawan..."
            class="w-full rounded-md border-gray-300 focus:border-blue-300 focus:ring"
        >
    </div>

    @error('workflow') <p class="mb-4 text-sm text-red-600">{{ $message }}</p> @enderror

    @if ($showForm)
        <div class="mb-6 p-4 border border-gray-200 rounded-md bg-gray-50">
            <form wire:submit="submitRequest" x-on:keydown.enter.prevent class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Karyawan</label>
                    <select wire:model="employee_id" class="w-full rounded-md border-gray-300">
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
                        @endforeach
                    </select>
                    @error('employee_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Jenis</label>
                    <select wire:model="type" class="w-full rounded-md border-gray-300">
                        <option value="izin">Izin</option>
                        <option value="cuti">Cuti</option>
                    </select>
                    @error('type') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Dari Tanggal</label>
                        <input type="date" wire:model="start_date" class="w-full rounded-md border-gray-300">
                        @error('start_date') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Sampai Tanggal</label>
                        <input type="date" wire:model="end_date" class="w-full rounded-md border-gray-300">
                        @error('end_date') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Alasan</label>
                    <textarea wire:model="reason" rows="3" class="w-full rounded-md border-gray-300"></textarea>
                    @error('reason') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-md text-sm">
                        Ajukan
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
                <th class="px-4 py-2">Karyawan</th>
                <th class="px-4 py-2">Jenis</th>
                <th class="px-4 py-2">Tanggal</th>
                <th class="px-4 py-2">Alasan</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($leaveRequests as $leaveRequest)
                @php
                    $statusColor = match ($leaveRequest->status) {
                        'disetujui' => 'bg-green-100 text-green-700',
                        'ditolak' => 'bg-red-100 text-red-700',
                        default => 'bg-yellow-100 text-yellow-700',
                    };
                @endphp
                <tr wire:key="leave-request-{{ $leaveRequest->id }}" class="border-t border-gray-200">
                    <td class="px-4 py-2">{{ $leaveRequest->employee?->first_name }} {{ $leaveRequest->employee?->last_name }}</td>
                    <td class="px-4 py-2 capitalize">{{ $leaveRequest->type }}</td>
                    <td class="px-4 py-2">{{ $leaveRequest->start_date }} &ndash; {{ $leaveRequest->end_date }}</td>
                    <td class="px-4 py-2 max-w-xs truncate">{{ $leaveRequest->reason }}</td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 rounded-full text-xs {{ $statusColor }}">
                            {{ ucfirst($leaveRequest->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-right space-x-2">
                        @foreach (LeaveRequest::workflow()->availableFrom($leaveRequest->status, auth()->user()->role) as $nextStatus)
                            <button
                                wire:click="decide({{ $leaveRequest->id }}, '{{ $nextStatus }}')"
                                wire:confirm="{{ $nextStatus === 'disetujui' ? 'Setujui' : 'Tolak' }} pengajuan ini?"
                                class="{{ $nextStatus === 'disetujui' ? 'text-green-600' : 'text-red-600' }} hover:underline"
                            >
                                {{ $nextStatus === 'disetujui' ? 'Setujui' : 'Tolak' }}
                            </button>
                        @endforeach
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-6 text-center text-gray-400">Belum ada pengajuan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        {{ $leaveRequests->links() }}
    </div>
</div>
