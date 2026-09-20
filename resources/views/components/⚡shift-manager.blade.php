<?php

use App\Models\Shift;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $name = '';
    public string $start_time = '';
    public string $end_time = '';

    public bool $is_active = true;

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

    public function edit(int $shiftId): void
    {
        $shift = Shift::findOrFail($shiftId);

        $this->editingId = $shift->id;
        $this->name = $shift->name;
        $this->start_time = $shift->start_time ?? '';
        $this->end_time = $shift->end_time ?? '';

        $this->is_active = $shift->is_active;
        $this->showForm = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->editingId) {
            Shift::findOrFail($this->editingId)->update($validated);
        } else {
            Shift::create($validated);
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function delete(int $shiftId): void
    {
        Shift::findOrFail($shiftId)->delete();
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'start_time' => ['required', 'date_format:H:i'], // Replaced 'time'
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],   // Replaced 'time' and added 'after'
            'is_active' => ['boolean'],
        ];
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'start_time', 'end_time', 'editingId']);
        $this->is_active = true;
        $this->resetErrorBag();
    }

    public function with(): array
    {
        return [
            'shifts' => Shift::query()
                ->when($this->search, fn ($query) => $query->where('name', 'like', "%{$this->search}%"))
                ->orderBy('name')
                ->paginate(10),
        ];
    }
};
?>

<div class="max-w-4xl mx-auto py-10 px-4">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Shift</h1>
        <button wire:click="create" class="px-4 py-2 bg-gray-900 text-white rounded-md text-sm hover:bg-gray-700">
            + Tambah Shift
        </button>
    </div>

    <input
        type="text"
        wire:model.live.debounce.300ms="search"
        placeholder="Cari Shift..."
        class="w-full mb-4 rounded-md border-gray-300 focus:border-blue-300 focus:ring"
    >

    @if ($showForm)
        <div class="mb-6 p-4 border border-gray-200 rounded-md bg-gray-50">
            <form wire:submit="save" class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama</label>
                    <input type="text" wire:model="name" class="w-full rounded-md border-gray-300">
                    @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Jam Mulai</label>
                    <input type="time" wire:model="start_time" class="w-full rounded-md border-gray-300">
                    @error('start_time') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Jam Selesai</label>
                    <input type="time" wire:model="end_time" class="w-full rounded-md border-gray-300">
                    @error('end_time') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" wire:model="is_active">
                    Aktif
                </label>

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
                <th class="px-4 py-2">Nama</th>
                <th class="px-4 py-2">Jam</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($shifts as $shift)
                <tr wire:key="shift-{{ $shift->id }}" class="border-t border-gray-200">
                    <td class="px-4 py-2">{{ $shift->name }}</td>
                    <td class="px-4 py-2">{{ $shift->start_time ?? '-' }} - {{ $shift->end_time ?? '-' }}</td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 rounded-full text-xs {{ $shift->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                            {{ $shift->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-right space-x-2">
                        <button wire:click="edit({{ $shift->id }})" class="text-blue-600 hover:underline">Edit</button>
                        <button wire:click="delete({{ $shift->id }})" wire:confirm="Hapus Shift ini?" class="text-red-600 hover:underline">Hapus</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-gray-400">Belum ada Shift.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        {{ $shifts->links() }}
    </div>
</div>
