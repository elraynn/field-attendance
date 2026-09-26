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
    <div class="bg-white rounded-2xl shadow-lg shadow-gray-200/60 border border-gray-100 overflow-hidden">
        <div class="h-1.5 bg-indigo-600"></div>

        <div class="p-8">
            <div class="flex flex-col items-center mb-6">
                <span class="inline-flex items-center justify-center w-11 h-11 rounded-xl bg-indigo-600 text-white font-bold mb-3">FA</span>
                <h1 class="text-xl font-semibold text-gray-900">Field Attendance</h1>
                <p class="text-sm text-gray-500 mt-1 text-center">Masuk buat kelola data karyawan & absensi</p>
            </div>

            <form wire:submit="authenticate" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" wire:model="email" autofocus class="w-full rounded-lg border-gray-300 focus:border-indigo-400 focus:ring-indigo-400">
                    @error('email') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" wire:model="password" class="w-full rounded-lg border-gray-300 focus:border-indigo-400 focus:ring-indigo-400">
                    @error('password') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" wire:model="remember" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-400">
                    Ingat saya
                </label>

                <button type="submit" class="w-full px-4 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                    Masuk
                </button>
            </form>
        </div>
    </div>
</div>
