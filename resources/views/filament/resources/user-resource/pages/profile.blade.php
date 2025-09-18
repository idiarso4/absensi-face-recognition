<x-filament-panels::page>
    <div class="max-w-2xl mx-auto">
        <div class="bg-white shadow-lg rounded-lg p-6">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Manajemen Profil</h1>
                <p class="text-gray-600 mt-2">Perbarui informasi profil dan password Anda</p>
            </div>

            <form wire:submit="save" class="space-y-6">
                {{ $this->form }}

                <div class="flex justify-end">
                    <x-filament::button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                        Simpan Perubahan
                    </x-filament::button>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page>
