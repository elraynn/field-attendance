<?php

use App\Models\Department;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $name = '';
    public string $code = '';
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

    public function edit(int $departmentId): void
    {
        $department = Department::findOrFail($departmentId);

        $this->editingId = $department->id;
        $this->name = $department->name;
        $this->code = $department->code ?? '';
        $this->is_active = $department->is_active;
        $this->showForm = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->editingId) {
            Department::findOrFail($this->editingId)->update($validated);
        } else {
            Department::create($validated);
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function delete(int $departmentId): void
    {
        Department::findOrFail($departmentId)->delete();
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('departments', 'name')->ignore($this->editingId)],
            'code' => ['nullable', 'string', 'max:20', Rule::unique('departments', 'code')->ignore($this->editingId)],
            'is_active' => ['boolean'],
        ];
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'code', 'editingId']);
        $this->is_active = true;
        $this->resetErrorBag();
    }

    public function with(): array
    {
        return [
            'departments' => Department::query()
                ->when($this->search, fn ($query) => $query->where('name', 'like', "%{$this->search}%"))
                ->orderBy('name')
                ->paginate(10),
        ];
    }
};
?>

<div class="max-w-4xl mx-auto py-10 px-4">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Departemen</h1>
        <button wire:click="create" class="px-4 py-2 bg-gray-900 text-white rounded-md text-sm hover:bg-gray-700">
            + Tambah Departemen
        </button>
    </div>

    <input
        type="text"
        wire:model.live.debounce.300ms="search"
        placeholder="Cari departemen..."
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
                    <label class="block text-sm font-medium text-gray-700">Kode</label>
                    <input type="text" wire:model="code" class="w-full rounded-md border-gray-300">
                    @error('code') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
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
                <th class="px-4 py-2">Kode</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($departments as $department)
                <tr wire:key="department-{{ $department->id }}" class="border-t border-gray-200">
                    <td class="px-4 py-2">{{ $department->name }}</td>
                    <td class="px-4 py-2">{{ $department->code ?? '-' }}</td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 rounded-full text-xs {{ $department->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                            {{ $department->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-right space-x-2">
                        <button wire:click="edit({{ $department->id }})" class="text-blue-600 hover:underline">Edit</button>
                        <button wire:click="delete({{ $department->id }})" wire:confirm="Hapus departemen ini?" class="text-red-600 hover:underline">Hapus</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-gray-400">Belum ada departemen.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        {{ $departments->links() }}
    </div>
</div>
