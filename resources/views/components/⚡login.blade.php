<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function authenticate(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $this->addError('email', 'Email atau password salah.');

            return;
        }

        session()->regenerate();

        $this->redirect(route('dashboard'), navigate: false);
    }
};
?>

<div class="w-full max-w-sm">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <h1 class="text-xl font-semibold text-gray-900 text-center">Field Attendance</h1>
        <p class="text-sm text-gray-500 text-center mt-1 mb-6">Masuk buat kelola data karyawan & absensi</p>

        <form wire:submit="authenticate" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" wire:model="email" autofocus class="w-full rounded-md border-gray-300 focus:border-blue-300 focus:ring">
                @error('email') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" wire:model="password" class="w-full rounded-md border-gray-300 focus:border-blue-300 focus:ring">
                @error('password') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" wire:model="remember">
                Ingat saya
            </label>

            <button type="submit" class="w-full px-4 py-2 bg-gray-900 text-white rounded-md text-sm font-medium hover:bg-gray-700">
                Masuk
            </button>
        </form>
    </div>
</div>
