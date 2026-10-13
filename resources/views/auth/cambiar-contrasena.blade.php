<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar contraseña</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="{{ asset('assets/qr.png') }}" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900">
    <div class="min-h-screen flex flex-col justify-center items-center pt-6 sm:pt-0">
        <div class="mb-8">
            <img src="{{ asset('assets/gedis.png') }}" alt="GEDIS" width="400" height="100">
        </div>

        <form action="{{ route('password.cambiar.store') }}" method="POST" class="px-6 py-8 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg w-full sm:max-w-md">
            @csrf
            <h1 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Cambia tu contraseña</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                La contraseña con la que entraste es temporal. Elige una propia para continuar. Debe tener al menos 8 caracteres y ser distinta de la temporal.
            </p>

            <div class="mt-4">
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contraseña nueva</label>
                <input id="password" class="p-2 mt-2 rounded-xl border w-full focus:border-indigo-500" type="password" name="password" placeholder="Contraseña nueva" required autofocus autocomplete="new-password" minlength="8">
                @error('password')
                    <div class="mt-2 text-red-600 text-sm">{{ $message }}</div>
                @enderror
            </div>

            <div class="mt-4">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirmar contraseña</label>
                <input id="password_confirmation" class="p-2 mt-2 rounded-xl border w-full focus:border-indigo-500" type="password" name="password_confirmation" placeholder="Confirmar contraseña" required autocomplete="new-password" minlength="8">
            </div>

            <div class="flex items-center justify-end mt-4">
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-900 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-700">Guardar contraseña</button>
            </div>
        </form>

        <form action="{{ route('users.logout') }}" method="POST" class="mt-4">
            @csrf
            <button type="submit" class="text-sm font-medium text-gray-600 hover:text-red-900 dark:text-gray-300">Cerrar sesión</button>
        </form>
    </div>
</body>
</html>
