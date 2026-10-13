<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Calderoni Services LLC</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        body {
            background-color: #f3f4f6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
    </style>
</head>
<body>

    <div class="w-full max-w-sm bg-white p-8 rounded-2xl shadow-xl border border-gray-100 space-y-6">
        
        <!-- Cabecera -->
        <div class="text-center space-y-3">
            <!-- Contenedor del Logo -->
            <div class="flex justify-center">
                <img src="{{ asset('images/logo1.png') }}" alt="Logo Calderoni Services" class="h-28.1 w-auto object-contain">
            </div>
        </div>

        <!-- Alerta de Errores -->
        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-600 text-xs p-3 rounded-xl shadow-sm">
                <ul class="space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Formulario de Login -->
        <form method="POST" action="{{ route('login') }}" class="space-y-4 text-xs">
            @csrf

            <!-- Identificador (Correo o Usuario) -->
            <div class="space-y-1">
                <label for="login" class="font-bold text-gray-700 uppercase">Correo o Usuario</label>
                <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-800 text-sm"
                    placeholder="ejemplo@correo o usuario">
            </div>

            <!-- Contraseña -->
            <div class="space-y-1">
                <label for="password" class="font-bold text-gray-700 uppercase">Contraseña</label>
                <input type="password" id="password" name="password" required
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-800 text-sm"
                    placeholder="••••••••">
            </div>

            <!-- Recordarme -->
            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 cursor-pointer text-gray-600">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-green-600 focus:ring-green-500 w-4 h-4">
                    <span>Recordarme en este dispositivo</span>
                </label>
            </div>

            <!-- Botón Submit -->
            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-xl text-xs font-bold shadow-md transition-colors uppercase tracking-wider">
                Iniciar Sesión
            </button>
        </form>

        <!-- Pie de página -->
        <div class="text-center text-[11px] text-gray-400 pt-2 border-t border-gray-100">
            <p>Calderoni Services LLC &copy; {{ date('Y') }}</p>
        </div>

    </div>

</body>
</html>