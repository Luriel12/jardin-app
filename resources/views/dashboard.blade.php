<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Jardinería - Calderoni Services LLC</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- jsPDF CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <!-- Leaflet CSS (Mapas) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        #map, #editMap { height: 250px; border-radius: 0.5rem; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 font-sans pb-12">

    <!-- Header -->
    <header class="bg-green-600 text-white p-4 shadow-md sticky top-0 z-50">
        <div class="max-w-2xl mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">🌿 Control de Jardines</h1>
            <div class="flex items-center gap-2">
                <a href="{{ route('report') }}" class="bg-emerald-700 text-white font-semibold px-3 py-1.5 rounded-lg text-sm shadow hover:bg-emerald-800">
                    📊 Reportes
                </a>
                <button onclick="openCreateModal()" class="bg-white text-green-700 font-semibold px-3 py-1.5 rounded-lg text-sm shadow hover:bg-green-50">
                    + Nuevo Cliente
                </button>
                <!-- Botón Mi Cuenta -->
                <button onclick="openAccountModal()" class="bg-emerald-800 text-white font-semibold px-3 py-1.5 rounded-lg text-sm shadow hover:bg-emerald-900 flex items-center gap-1">
                    👤 Cuenta
                </button>
                <!-- Botón Cerrar Sesión -->
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="bg-red-600 text-white font-semibold px-3 py-1.5 rounded-lg text-sm shadow hover:bg-red-700" title="Cerrar sesión">
                        🚪 Salir
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="max-w-2xl mx-auto p-4">

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 mb-4 rounded shadow-sm text-sm" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif

       <!-- Buscador y Filtros -->
        <form action="{{ route('dashboard') }}" method="GET" class="mb-4 flex flex-col sm:flex-row gap-2">
            <!-- Campo de Búsqueda -->
            <div class="relative flex-1">
                <input type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="🔍 Buscar por nombre, calle o número..." 
                    class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm shadow-sm focus:ring-2 focus:ring-green-500 focus:outline-none">
            </div>

            <div class="flex gap-2">
                <!-- Botón o Toggle de Suspendidos -->
                @if(request('status') === 'suspended')
                    <a href="{{ route('dashboard', array_filter(['search' => request('search')])) }}" 
                    class="bg-red-100 text-red-700 border border-red-300 px-4 py-2.5 rounded-xl text-sm font-medium flex items-center justify-center gap-1 shadow-sm transition hover:bg-red-200">
                        🚫 Mostrando Suspendidos (Quitar)
                    </a>
                    <input type="hidden" name="status" value="suspended">
                @else
                    <a href="{{ route('dashboard', array_merge(request('search') ? ['search' => request('search')] : [], ['status' => 'suspended'])) }}" 
                    class="bg-gray-100 text-gray-700 border border-gray-200 hover:bg-red-50 hover:text-red-600 hover:border-red-200 px-4 py-2.5 rounded-xl text-sm font-medium flex items-center justify-center gap-1 transition shadow-sm">
                        🚫 Solo Suspendidos
                    </a>
                @endif

                <!-- Botones de Acción / Limpieza -->
                @if(request('search') || request('status'))
                    <a href="{{ route('dashboard') }}" 
                    class="bg-gray-200 text-gray-700 hover:bg-gray-300 px-4 py-2.5 rounded-xl text-sm font-medium flex items-center justify-center transition">
                        Limpiar
                    </a>
                @endif

                <button type="submit" 
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-xl text-sm font-medium shadow transition">
                    Buscar
                </button>
            </div>
        </form>

        <!-- Lista de Clientes -->
        <div class="space-y-4">
            @if(count($clients) > 0)
                @foreach($clients as $client)
                    <div class="bg-white p-4 rounded-2xl shadow border relative {{ $client->is_active ? 'border-gray-100' : 'border-red-200 bg-red-50/30 opacity-75' }}">
                        <div>
                            <div class="flex justify-between items-start">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-lg text-gray-900">{{ $client->name }}</h3>
                                    
                                    <!-- NOTIFICACIÓN 1: Badge de Proforma Pendiente en el Encabezado -->
                                    @if(isset($client->proforma) && $client->proforma)
                                        <span class="inline-flex items-center gap-1 bg-amber-500 text-white text-[10px] uppercase font-black px-2 py-0.5 rounded-full shadow-sm animate-pulse">
                                            📋 Proforma Pendiente
                                        </span>
                                    @endif
                                </div>

                                <span class="bg-amber-100 text-amber-800 text-xs font-bold px-2.5 py-1 rounded-full">
                                    {{ $client->days_ago == 9999 ? 'Nunca atendido' : $client->days_ago . ' días sin atender' }}
                                </span>
                            </div>

                            <!-- Dirección detallada -->
                            <p class="text-sm text-gray-600 mt-1">
                                📍 
                                @if($client->street || $client->house_number)
                                    <span class="font-medium text-gray-800">
                                        {{ $client->street }} {{ $client->house_number ? 'N° ' . $client->house_number : '' }}
                                    </span>
                                    @if($client->address) 
                                        <span class="text-xs text-gray-500 block">Ref: {{ $client->address }}</span> 
                                    @endif
                                @else
                                    {{ $client->address ?? 'Sin dirección escrita' }}
                                @endif
                            </p>

                            @if($client->phone)
                                <p class="text-sm text-gray-600 mt-0.5" id="listPhone-{{ $client->id }}">📞 <a href="tel:{{ $client->phone }}" class="text-green-600 underline">{{ $client->phone }}</a></p>
                            @endif
                            <p class="text-xs text-gray-400 mt-1">Último servicio: {{ $client->last_service_date ?? 'Ninguno' }}</p>

                            <div class="flex items-center justify-between border-t pt-3 mt-3">
                                <div class="flex items-center gap-2">
                                    @if($client->is_active)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-800">
                                            ● Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800">
                                            ● Suspendido
                                        </span>
                                    @endif
                                </div>

                                <!-- Formulario para alternar el estado -->
                                <form action="{{ route('clients.toggleStatus', $client->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-xs px-3 py-1 rounded-lg font-semibold transition-colors shadow-sm {{ $client->is_active ? 'bg-amber-100 text-amber-800 hover:bg-amber-200' : 'bg-green-100 text-green-800 hover:bg-green-200' }}">
                                        {{ $client->is_active ? 'Suspender Servicio ⏸️' : 'Activar ▶️' }}
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                @if($client->latitude && $client->longitude)
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ $client->latitude }},{{ $client->longitude }}" target="_blank" class="text-xs bg-blue-50 text-blue-600 font-medium px-3 py-2 rounded-lg hover:bg-blue-100 flex items-center gap-1">
                                        🗺️ Cómo llegar
                                    </a>
                                @else
                                    <span class="text-xs text-gray-400">Sin GPS</span>
                                @endif

                                <!-- Botón Proforma con Notificación Visual -->
                                <div class="relative inline-block">
                                    <button type="button" 
                                        @if($client->is_active) 
                                            onclick="openProformaModal({{ $client->id }}, '{{ $client->phone ?? 'N/A' }}')" 
                                            class="text-xs bg-purple-50 text-purple-700 font-medium px-3 py-2 rounded-lg hover:bg-purple-100 flex items-center gap-1 shadow-sm relative {{ isset($client->proforma) && $client->proforma ? 'ring-2 ring-amber-400 font-bold' : '' }}"
                                        @else 
                                            disabled 
                                            class="text-xs bg-gray-100 text-gray-400 font-medium px-3 py-2 rounded-lg flex items-center gap-1 cursor-not-allowed opacity-60"
                                        @endif>
                                        📄 Proforma
                                    </button>

                                    <!-- NOTIFICACIÓN 2: Punto Rojo/Amarillo de Alerta sobre el Botón -->
                                    @if(isset($client->proforma) && $client->proforma && $client->is_active)
                                        <span class="absolute -top-1 -right-1 flex h-3 w-3">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                                        </span>
                                    @endif
                                </div>

                                <button onclick="openEditModal({{ $client->id }})" class="text-xs bg-gray-100 text-gray-700 font-medium px-3 py-2 rounded-lg hover:bg-gray-200">
                                    ✏️ Editar
                                </button>
                            </div>

                            <!-- Botón Atender -->
                            <button type="button" 
                                @if($client->is_active) 
                                    onclick="openAttendModal({{ $client->id }}, '{{ addslashes($client->name) }}')" 
                                    class="bg-green-600 hover:bg-green-700 text-white font-medium text-xs px-4 py-2 rounded-lg shadow"
                                @else 
                                    disabled 
                                    class="bg-gray-200 text-gray-400 font-medium text-xs px-4 py-2 rounded-lg shadow-none cursor-not-allowed opacity-60"
                                @endif>
                                ✔ Atender
                            </button>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-12 bg-white rounded-xl shadow-sm border border-gray-100">
                    <p class="text-gray-500">No se encontraron clientes.</p>
                    <button onclick="openCreateModal()" class="mt-3 text-green-600 font-semibold text-sm underline">Agrega un cliente nuevo</button>
                </div>
            @endif
        </div>
    </main>

    <!-- Modal Único para Crear / Editar Clientes -->
    <div id="clientModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl w-full max-w-lg p-6 relative max-h-[90vh] overflow-y-auto shadow-2xl">
            <h2 id="modalTitle" class="text-lg font-bold mb-4 text-gray-900">Registrar Nuevo Cliente</h2>
            
            <form id="clientForm" action="{{ route('clients.store') }}" method="POST" class="space-y-4">
                @csrf
                <div id="methodField"></div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Nombre del Cliente / Jardín</label>
                    <input type="text" id="fieldName" name="name" required class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-green-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Teléfono</label>
                    <input type="text" id="fieldPhone" name="phone" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-green-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Calle</label>
                        <input type="text" id="fieldStreet" name="street" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-green-500 focus:outline-none" placeholder="Ej. Av. Principal">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">N° de Casa</label>
                        <input type="text" id="fieldHouseNumber" name="house_number" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-green-500 focus:outline-none" placeholder="Ej. #123">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Referencia adicional (Opcional)</label>
                    <input type="text" id="fieldAddress" name="address" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-green-500 focus:outline-none" placeholder="Ej. Casa de dos pisos portón negro">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Selecciona la ubicación en el mapa:</label>
                    <div id="map"></div>
                    <p class="text-xs text-gray-400 mt-1">Mueve el marcador o haz clic en el mapa sobre la ubicación exacta.</p>
                </div>

                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Fecha del último servicio (Opcional)</label>
                    <input type="date" id="fieldLastService" name="last_service_date" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-green-500 focus:outline-none">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">Cancelar</button>
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium shadow hover:bg-green-700">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de Gestión de Cuenta (Perfil de Usuario) -->
    <div id="accountModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl w-full max-w-md p-6 relative shadow-2xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">Gestionar Mi Cuenta</h3>
                <button onclick="closeAccountModal()" class="text-gray-400 hover:text-gray-600 font-bold text-xl">&times;</button>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                @csrf
                @method('patch')

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Nombre Completo</label>
                    <input type="text" name="name" value="{{ auth()->user()->name }}" required 
                        class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-green-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Nombre de Usuario (Username)</label>
                    <input type="text" name="username" value="{{ auth()->user()->username ?? '' }}" required 
                        class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-green-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Correo Electrónico</label>
                    <input type="email" name="email" value="{{ auth()->user()->email }}" required 
                        class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-green-500 focus:outline-none">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeAccountModal()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">Cancelar</button>
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium shadow hover:bg-green-700">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de Proforma -->
    <div id="proformaModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl w-full max-w-3xl p-6 relative max-h-[90vh] overflow-y-auto shadow-2xl">
            
            <input type="hidden" id="profPhone" value="">

            <div id="proforma-pdf-container" class="bg-white p-4 space-y-4 text-sm text-gray-800">
                <div class="border-b pb-3 mb-3 flex justify-between items-start">
                    <div>
                        <h2 class="text-base font-black text-gray-900 tracking-wide">CALDERONI SERVICES LLC</h2>
                        <p class="text-[10px] text-gray-500 mt-0.5">Proforma de Servicios</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-semibold text-gray-600">DATE: <span id="profDate" class="font-normal text-gray-800"></span></p>
                    </div>
                </div>

                <div class="bg-gray-50 p-2.5 rounded-xl text-xs space-y-1 border border-gray-100">
                    <p><strong>NAME:</strong> <span id="profName" class="text-gray-800 uppercase"></span></p>
                    <p><strong>ADDRESS:</strong> <span id="profAddress" class="text-gray-800 uppercase"></span></p>
                </div>

                <div class="mb-3">
                    <div class="flex justify-between items-center mb-1.5 no-print">
                        <label class="text-[11px] font-bold text-gray-700 uppercase">DESCRIPTION WORK, PARTS OR MATERIALS</label>
                        <button type="button" onclick="addProformaRow()" class="bg-green-600 text-white text-[10px] px-2 py-0.5 rounded-lg font-medium hover:bg-green-700">+ Añadir Ítem</button>
                    </div>

                    <div class="overflow-x-auto border rounded-xl">
                        <table class="w-full text-left border-collapse text-xs" id="proformaTable">
                            <thead>
                                <tr class="bg-gray-100 text-[10px] text-gray-600 border-b">
                                    <th class="p-1.5 w-12 text-center">QTY</th>
                                    <th class="p-1.5">DESCRIPTION</th>
                                    <th class="p-1.5 w-20 text-right">PRICE</th>
                                    <th class="p-1.5 w-20 text-right">AMOUNT</th>
                                    <th class="p-1.5 w-8 no-print"></th>
                                </tr>
                            </thead>
                            <tbody id="proformaItems"></tbody>
                        </table>
                    </div>
                </div>

                <div class="space-y-3 border-t pt-3">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-700 uppercase mb-1">LABOR PERFORMED / NOTES</label>
                        <textarea id="profLaborDesc" rows="2" class="w-full border border-gray-300 rounded-lg p-1.5 text-xs focus:ring-2 focus:ring-green-500 focus:outline-none" placeholder="Ej. INCLUDES TRANSPORTATION..."></textarea>
                    </div>

                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-200 space-y-1.5 text-xs">
                        <div class="flex justify-between font-medium">
                            <span>TOTAL MAT.:</span>
                            <span id="profTotalMat">$0.00</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-medium">LABOR ($):</span>
                            <input type="number" id="profLaborPrice" value="0.00" step="0.01" oninput="calculateProformaTotals()" class="w-24 border border-gray-300 rounded-lg p-1 text-right text-xs focus:ring-2 focus:ring-green-500 focus:outline-none">
                        </div>
                        <div class="flex justify-between font-bold text-sm border-t pt-1.5 text-amber-900 bg-amber-50 p-1.5 rounded-lg">
                            <span>TOT. AMOUNT:</span>
                            <span id="profGrandTotal">$0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <div id="proformaFooter" class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="closeProformaModal()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-xl text-xs font-semibold">Cancel</button>
                <button type="button" onclick="saveCurrentProforma()" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-xl text-xs font-bold shadow">💾 Save Proforma</button>
                <button type="button" onclick="downloadA4PDFDirect()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-xs font-bold shadow">🖨️ Print / Download A4</button>
                <button type="button" onclick="shareTirillaWhatsApp()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-xl text-xs font-bold shadow">💬 Share WhatsApp (Thermal)</button>
            </div>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let map, marker;
        const defaultLat = 0.9592;
        const defaultLng = -79.6588;
        let activeModalClientId = null;

        function initMap(lat = defaultLat, lng = defaultLng) {
            setTimeout(() => {
                if (!map) {
                    map = L.map('map').setView([lat, lng], 15); 
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap'
                    }).addTo(map);

                    marker = L.marker([lat, lng], { draggable: true }).addTo(map);

                    marker.on('dragend', function (e) {
                        updateLatLng(marker.getLatLng());
                    });

                    map.on('click', function (e) {
                        marker.setLatLng(e.latlng);
                        updateLatLng(e.latlng);
                    });

                    updateLatLng(marker.getLatLng());
                } else {
                    map.invalidateSize();
                    map.setView([lat, lng], 15);
                    marker.setLatLng([lat, lng]);
                    updateLatLng(marker.getLatLng());
                }
            }, 200);
        }

        function openCreateModal() {
            document.getElementById('modalTitle').innerText = 'Register New Client';
            document.getElementById('clientForm').action = "{{ route('clients.store') }}";
            document.getElementById('methodField').innerHTML = '';
            
            document.getElementById('fieldName').value = '';
            document.getElementById('fieldPhone').value = '';
            document.getElementById('fieldStreet').value = '';
            document.getElementById('fieldHouseNumber').value = '';
            document.getElementById('fieldAddress').value = '';
            document.getElementById('fieldLastService').value = '';

            document.getElementById('clientModal').classList.remove('hidden');
            initMap(defaultLat, defaultLng);
        }

        function openEditModal(clientId) {
            fetch(`/clients/${clientId}/edit`)
                .then(response => response.json())
                .then(client => {
                    document.getElementById('modalTitle').innerText = 'Edit Client: ' + client.name;
                    document.getElementById('clientForm').action = `/clients/${clientId}`;
                    document.getElementById('methodField').innerHTML = '@method("PUT")';

                    document.getElementById('fieldName').value = client.name || '';
                    document.getElementById('fieldPhone').value = client.phone || '';
                    document.getElementById('fieldStreet').value = client.street || '';
                    document.getElementById('fieldHouseNumber').value = client.house_number || '';
                    document.getElementById('fieldAddress').value = client.address || '';
                    document.getElementById('fieldLastService').value = client.last_service_date || '';

                    document.getElementById('clientModal').classList.remove('hidden');
                    
                    const lat = client.latitude ? parseFloat(client.latitude) : defaultLat;
                    const lng = client.longitude ? parseFloat(client.longitude) : defaultLng;
                    initMap(lat, lng);
                });
        }

        function closeModal() {
            document.getElementById('clientModal').classList.add('hidden');
        }

        /* Account Modal Functions */
        function openAccountModal() {
            document.getElementById('accountModal').classList.remove('hidden');
        }

        function closeAccountModal() {
            document.getElementById('accountModal').classList.add('hidden');
        }

        function updateLatLng(latlng) {
            document.getElementById('latitude').value = latlng.lat;
            document.getElementById('longitude').value = latlng.lng;
        }

        // Open Proforma Modal (Normal Mode: Save or Update)
        function openProformaModal(clientId, clientPhone = 'N/A') {
            loadProformaDataAndRender(clientId, clientPhone, false);
        }

        // Open Attend Modal (Payment Mode: Enforces Proforma & Shows Payment Button)
        async function openAttendModal(clientId, clientName) {
            activeModalClientId = clientId;

            try {
                let response = await fetch(`/clients/${clientId}/proforma-data`);
                let proforma = await response.json();

                if (!proforma || !proforma.items || proforma.items.length === 0) {
                    alert(`⚠️ Attention: To attend and charge ${clientName}, you must first create and save a proforma.`);
                    return;
                }

                loadProformaDataAndRender(clientId, 'N/A', true);

            } catch (error) {
                console.error('Error verifying proforma for attendance:', error);
                alert('An error occurred while checking client status.');
            }
        }

        // Unified helper to fetch data and paint the modal depending on the mode
        function loadProformaDataAndRender(clientId, clientPhone, isAttending) {
            activeModalClientId = clientId;

            Promise.all([
                fetch(`/clients/${clientId}/edit`).then(res => res.json()),
                fetch(`/clients/${clientId}/proforma-data`).then(res => res.json()).catch(() => null)
            ]).then(([client, proforma]) => {
                
                const proformaExists = proforma && proforma.items && proforma.items.length > 0;
                const footer = document.getElementById('proformaFooter');

                if (footer) {
                    if (isAttending) {
                        footer.innerHTML = `
                            <button type="button" onclick="closeProformaModal()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-xl text-xs font-semibold">Cancel</button>
                            <button type="button" onclick="submitFinalAttendance(${clientId})" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-xl text-xs font-bold shadow flex items-center justify-center gap-1.5">
                                ✔ Confirm Payment & Attend
                            </button>
                        `;
                    } else {
                        const saveBtnText = proformaExists ? '🔄 Update Proforma' : '💾 Save Proforma';
                        footer.innerHTML = `
                            <button type="button" onclick="closeProformaModal()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-xl text-xs font-semibold">Cancel</button>
                            <button type="button" onclick="saveCurrentProforma()" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-xl text-xs font-bold shadow flex items-center justify-center gap-1.5">${saveBtnText}</button>
                            <button type="button" onclick="downloadA4PDFDirect()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-xs font-bold shadow flex items-center justify-center gap-1.5">🖨️ Print / Download A4</button>
                            <button type="button" onclick="shareTirillaWhatsApp()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-xl text-xs font-bold shadow flex items-center justify-center gap-1.5">💬 WhatsApp (Thermal)</button>
                        `;
                    }
                }

                document.getElementById('profName').innerText = client.name || '';
                document.getElementById('profPhone').value = client.phone || clientPhone || 'N/A';
                
                let fullAddress = '';
                if (client.street || client.house_number) {
                    fullAddress = `${client.street || ''} N° ${client.house_number || ''} ${client.address ? '(' + client.address + ')' : ''}`;
                } else {
                    fullAddress = client.address || 'No address provided';
                }
                document.getElementById('profAddress').innerText = fullAddress;

                const today = new Date();
                const dateStr = `${today.getMonth() + 1}/${today.getDate()}/${today.getFullYear()}`;
                document.getElementById('profDate').innerText = dateStr;

                document.getElementById('proformaItems').innerHTML = '';

                if (proformaExists) {
                    proforma.items.forEach(item => {
                        addProformaRow(item.desc || item.description || '', item.qty || 1, parseFloat(item.price) || 0);
                    });
                    document.getElementById('profLaborDesc').value = proforma.labor_desc || '';
                    document.getElementById('profLaborPrice').value = parseFloat(proforma.labor_price || 0).toFixed(2);
                } else {
                    addProformaRow('SPECTICIDE', 4, 0.00);
                    addProformaRow('FUNGICIDE', 4, 0.00);
                    addProformaRow('DUMP', 1, 0.00);
                    document.getElementById('profLaborDesc').value = 'INCLUDES LABOR';
                    document.getElementById('profLaborPrice').value = '50.00';
                }

                calculateProformaTotals();
                document.getElementById('proformaModal').classList.remove('hidden');
            }).catch(err => {
                console.error("Error opening proforma:", err);
            });
        }

        function closeProformaModal() {
            document.getElementById('proformaModal').classList.add('hidden');
        }

        // Save temporary proforma via AJAX
        async function saveCurrentProforma() {
            if (!activeModalClientId) return;

            let items = [];
            document.querySelectorAll('#proformaItems tr').forEach(row => {
                items.push({
                    qty: row.querySelector('.qty-input')?.value || 1,
                    desc: row.querySelector('.desc-input')?.value || '',
                    price: row.querySelector('.price-input')?.value || 0,
                    amount: row.querySelector('.amount-cell')?.innerText || '$0.00'
                });
            });

            if (items.length === 0) {
                items.push({
                    qty: 1,
                    desc: 'MATERIALS',
                    price: 0,
                    amount: '$0.00'
                });
            }

            const payload = {
                items: items,
                total_materials: parseFloat(document.getElementById('profTotalMat').innerText.replace('$', '')) || 0,
                labor_price: parseFloat(document.getElementById('profLaborPrice').value) || 0,
                labor_desc: document.getElementById('profLaborDesc').value,
                grand_total: parseFloat(document.getElementById('profGrandTotal').innerText.replace('$', '')) || 0,
                _token: document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
            };

            try {
                let response = await fetch(`/clients/${activeModalClientId}/proforma`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(payload)
                });

                let result = await response.json();

                if (result.success) {
                    // 1. Cerramos el modal limpiamente
                    if (typeof closeProformaModal === 'function') {
                        closeProformaModal();
                    }

                    // 2. Refrescamos la pantalla para actualizar la tarjeta del cliente con el nuevo badge y estado
                    window.location.reload();
                } else {
                    alert(result.message || 'An error occurred while saving the proforma.');
                }
            } catch (error) {
                console.error('Error saving proforma:', error);
                alert('An error occurred while saving the proforma.');
            }
        }

        // Confirmar pago y direccionar a la vista A4 de Imprimir/Descargar
        async function submitFinalAttendance(clientId) {
            let items = [];
            document.querySelectorAll('#proformaItems tr').forEach(row => {
                items.push({
                    qty: row.querySelector('.qty-input')?.value || 1,
                    desc: row.querySelector('.desc-input')?.value || '',
                    price: row.querySelector('.price-input')?.value || 0,
                    amount: row.querySelector('.amount-cell')?.innerText || '$0.00'
                });
            });

            if (items.length === 0) {
                items.push({ qty: 1, desc: 'MATERIALS', price: 0, amount: '$0.00' });
            }

            const totalMat = document.getElementById('profTotalMat').innerText;
            const laborPrice = document.getElementById('profLaborPrice').value;
            const laborDesc = document.getElementById('profLaborDesc').value;
            const grandTotal = document.getElementById('profGrandTotal').innerText;

            const payload = {
                attendance_data: JSON.stringify({ items, totalMat, laborPrice, laborDesc, grandTotal }),
                _token: document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
            };

            try {
                let response = await fetch(`/clients/${clientId}/attend-detailed`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(payload)
                });

                let result = await response.json();

                if (result.success) {
                    closeProformaModal();
                    // 1. Para IMPRIMIR / DESCARGAR FORMAL: Abre la vista A4 en formato PAYMENT RECEIPT
                    window.open(`/documents/a4?type=recibo&client_id=${clientId}`, '_blank');
                    // 2. Refresca el panel principal
                    window.location.reload();
                } else {
                    alert(result.message || 'An error occurred while processing payment.');
                }
            } catch (error) {
                console.error('Payment error:', error);
                alert('A network error occurred while recording the payment.');
            }
        }

        function addProformaRow(desc = '', qty = 1, price = 0.00) {
            const tbody = document.getElementById('proformaItems');
            const row = document.createElement('tr');
            row.className = 'border-b hover:bg-gray-50';
            row.innerHTML = `
                <td class="p-1.5 text-center">
                    <input type="number" value="${qty}" min="1" oninput="calculateProformaTotals()" class="w-12 border rounded p-1 text-center text-xs qty-input">
                </td>
                <td class="p-1.5">
                    <input type="text" value="${desc}" placeholder="Description..." class="w-full border rounded p-1 text-xs desc-input">
                </td>
                <td class="p-1.5 text-right">
                    <input type="number" value="${typeof price === 'number' ? price.toFixed(2) : price}" step="0.01" oninput="calculateProformaTotals()" class="w-20 border rounded p-1 text-right text-xs price-input">
                </td>
                <td class="p-1.5 text-right font-medium text-xs amount-cell">$0.00</td>
                <td class="p-1.5 text-center no-print">
                    <button type="button" onclick="this.closest('tr').remove(); calculateProformaTotals();" class="text-red-500 hover:text-red-700 font-bold">×</button>
                </td>
            `;
            tbody.appendChild(row);
            calculateProformaTotals();
        }

        function calculateProformaTotals() {
            const rows = document.querySelectorAll('#proformaItems tr');
            let totalMat = 0;

            rows.forEach(row => {
                const qty = parseFloat(row.querySelector('.qty-input')?.value) || 0;
                const price = parseFloat(row.querySelector('.price-input')?.value) || 0;
                const amount = qty * price;
                
                row.querySelector('.amount-cell').innerText = '$' + amount.toFixed(2);
                totalMat += amount;
            });

            document.getElementById('profTotalMat').innerText = '$' + totalMat.toFixed(2);
            const laborPrice = parseFloat(document.getElementById('profLaborPrice')?.value) || 0;
            const grandTotal = totalMat + laborPrice;

            document.getElementById('profGrandTotal').innerText = '$' + grandTotal.toFixed(2);
        }

        /* STANDARDIZED ACTIONS */

        // 1. Print / Download PDF uses EXCLUSIVELY A4 Format
        function downloadA4PDFDirect() {
            if (!activeModalClientId) {
                alert('No client selected.');
                return;
            }
            window.open(`/documents/a4?type=proforma&client_id=${activeModalClientId}`, '_blank');
        }

        // 2. Share via WhatsApp uses EXCLUSIVELY 80mm Thermal Receipt Format
        async function shareTirillaWhatsApp() {
            try {
                const { doc, clientName, grandTotal } = await generatePdfReceipt('INVOICE');
                const pdfBlob = doc.output('blob');
                const fileName = `INVOICE-${clientName.replace(/\s+/g, '_')}.pdf`;
                const file = new File([pdfBlob], fileName, { type: 'application/pdf' });

                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    await navigator.share({
                        title: 'INVOICE - Calderoni Services LLC',
                        text: `Hello ${clientName}, here is your INVOICE for a total of ${grandTotal}.`,
                        files: [file],
                    });
                } else {
                    doc.save(fileName);
                    const message = encodeURIComponent(`Hello ${clientName}, here is your INVOICE PDF for a total of ${grandTotal}.`);
                    window.open(`https://api.whatsapp.com/send?text=${message}`, '_blank');
                }
            } catch (error) {
                console.error('Error sharing via WhatsApp:', error);
                alert('Could not share document via WhatsApp: ' + error.message);
            }
        }

        // Generate 80mm Thermal PDF Receipt
        async function generatePdfReceipt(docType = 'INVOICE') {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({
                unit: 'mm',
                format: [80, 180]
            });

            const clientName = document.getElementById('profName')?.innerText || 'Valued Customer';
            const clientAddress = document.getElementById('profAddress')?.innerText || 'No address provided';
            const clientPhone = document.getElementById('profPhone')?.value || 'N/A';
            const profDate = document.getElementById('profDate')?.innerText || '';
            const totalMat = document.getElementById('profTotalMat')?.innerText || '$0.00';
            const laborPrice = parseFloat(document.getElementById('profLaborPrice')?.value || 0).toFixed(2);
            const grandTotal = document.getElementById('profGrandTotal')?.innerText || '$0.00';
            const laborDesc = document.getElementById('profLaborDesc')?.value || '';

            let y = 10;

            doc.setFont("helvetica", "bold");
            doc.setFontSize(11);
            doc.text("CALDERONI SERVICES LLC", 5, y);
            
            y += 5;
            doc.setFont("helvetica", "normal");
            doc.setFontSize(8);
            doc.text(docType, 5, y);
            
            y += 5;
            doc.text(`Date: ${profDate}`, 5, y);

            y += 4;
            doc.line(5, y, 75, y);
            y += 5;

            doc.setFont("helvetica", "bold");
            doc.text("CUSTOMER:", 5, y);
            doc.setFont("helvetica", "normal");
            doc.text(clientName, 25, y);

            y += 4;
            doc.setFont("helvetica", "bold");
            doc.text("PHONE:", 5, y);
            doc.setFont("helvetica", "normal");
            doc.text(clientPhone, 25, y);

            y += 4;
            doc.setFont("helvetica", "bold");
            doc.text("ADDRESS:", 5, y);
            doc.setFont("helvetica", "normal");
            
            const splitAddress = doc.splitTextToSize(clientAddress, 48);
            doc.text(splitAddress, 25, y);
            y += (splitAddress.length * 4) + 2;

            doc.line(5, y, 75, y);
            y += 5;

            doc.setFont("helvetica", "bold");
            doc.setFontSize(8);
            doc.text("QTY", 5, y);
            doc.text("DESCRIPTION", 18, y);
            doc.text("TOTAL", 60, y);

            y += 4;
            doc.line(5, y, 75, y);
            y += 5;

            doc.setFont("helvetica", "normal");
            const rows = document.querySelectorAll('#proformaItems tr');
            
            if (rows.length > 0) {
                rows.forEach(row => {
                    const qty = row.querySelector('.qty-input')?.value || '1';
                    const desc = row.querySelector('.desc-input')?.value || '';
                    const amount = row.querySelector('.amount-cell')?.innerText || '$0.00';

                    doc.text(String(qty), 5, y);
                    
                    const splitDesc = doc.splitTextToSize(desc, 38);
                    doc.text(splitDesc, 18, y);
                    
                    doc.text(amount, 60, y);

                    y += (Math.max(splitDesc.length, 1) * 4) + 2;
                });
            } else {
                doc.text("1", 5, y);
                doc.text("MATERIALS", 18, y);
                doc.text("$0.00", 60, y);
                y += 6;
            }

            y += 2;
            doc.line(5, y, 75, y);
            y += 5;

            if (laborDesc) {
                doc.setFont("helvetica", "bold");
                doc.text("Labor / Notes:", 5, y);
                y += 4;
                doc.setFont("helvetica", "normal");
                const splitNotes = doc.splitTextToSize(laborDesc, 50);
                doc.text(splitNotes, 5, y);
                y += (splitNotes.length * 4) + 4;
            }

            doc.setFont("helvetica", "normal");
            doc.text("Total Materials:", 5, y);
            doc.text(totalMat, 60, y);

            y += 4;
            doc.text("Labor / Service Fee:", 5, y);
            doc.text(`$${laborPrice}`, 60, y);

            y += 5;
            doc.setFont("helvetica", "bold");
            doc.setFontSize(10);
            doc.text("TOTAL DUE:", 5, y);
            doc.text(grandTotal, 55, y);

            return { doc, clientName, grandTotal };
        }
    </script>
</body>
</html>