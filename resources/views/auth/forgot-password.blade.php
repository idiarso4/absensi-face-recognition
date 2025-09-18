<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="bg-white shadow-lg rounded-lg p-8">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-100">
                        <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                    </div>
                    <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                        Lupa Password?
                    </h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Masukkan alamat email Anda dan kami akan mengirimkan link untuk mereset password.
                    </p>
                </div>

                <form class="mt-8 space-y-6" method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Alamat Email
                        </label>
                        <input id="email" name="email" type="email" autocomplete="email" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Masukkan email Anda">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Kirim Link Reset Password
                        </button>
                    </div>

                    @if (session('status'))
                        <div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                            {{ session('status') }}
                        </div>
                    @endif
                </form>

                <div class="mt-6 text-center">
                    <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:text-blue-500">
                        Kembali ke Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>