<?php
namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ServiceRecord;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Proforma;
use App\Models\ServiceHistory;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status'); // Recogemos el parámetro de estado
        
        // Query principal para la tabla
        $query = Client::query();
        
        // Query base para los contadores (respeta la búsqueda actual si existe)
        $countQuery = Client::query();
        
        // 1. Filtro por término de búsqueda (nombre, calle o número de casa)
        if ($search) {
            $searchFilter = function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('street', 'like', "%{$search}%")
                ->orWhere('house_number', 'like', "%{$search}%");
            };
            
            $query->where($searchFilter);
            $countQuery->where($searchFilter);
        }

        // Calculamos activos e inactivos (is_active = 0 es suspendido/inactivo según tu lógica)
        $activeCount = (clone $countQuery)->where('is_active', '!=', 0)->count();
        $inactiveCount = (clone $countQuery)->where('is_active', 0)->count();

        // 2. Filtro exclusivo para clientes suspendidos en la tabla principal
        if ($status === 'suspended') {
            $query->where('is_active', 0); // 0 = suspendido/inactivo
        }

        // 3. Ordenamiento condicional
        if ($status === 'suspended') {
            // EXCLUSIVO SUSPENDIDOS:
            $query->orderByRaw("CASE WHEN last_service_date IS NULL THEN 1 ELSE 0 END ASC")
                ->orderBy('last_service_date', 'DESC');
        } else {
            // VISTA GENERAL:
            // Activos primero, suspendidos al final.
            $query->orderByRaw("CASE WHEN is_active = 0 THEN 1 ELSE 0 END ASC")
                ->orderByRaw("CASE WHEN last_service_date IS NULL THEN 1 ELSE 0 END DESC")
                ->orderBy('last_service_date', 'ASC');
        }

        $clients = $query->get()
                    ->map(function ($client) {
                        if ($client->last_service_date) {
                            $client->days_ago = (int) Carbon::parse($client->last_service_date)->diffInDays(now());
                        } else {
                            $client->days_ago = 9999; 
                        }
                        return $client;
                    });

        return view('dashboard', compact('clients', 'search', 'activeCount', 'inactiveCount'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'last_service_date' => 'nullable|date',
        ]);

        Client::create($request->all());
        return redirect()->route('dashboard')->with('success', 'Cliente agregado correctamente.');
    }

    // Registrar atención con el monto cobrado
    public function attend(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $client = Client::findOrFail($id);
        $today = now()->toDateString();

        $client->last_service_date = $today;
        $client->save();

        // Creamos el registro y lo guardamos en una variable
        $serviceRecord = ServiceRecord::create([
            'client_id' => $client->id,
            'amount' => $request->amount,
            'service_date' => $today,
        ]);

        // Redirigimos a la vista del recibo pasando el ID del registro
        return redirect()->route('clients.receipt', $serviceRecord->id);
    }

    // Nueva función para mostrar el recibo
    public function receipt($recordId)
    {
        $record = ServiceRecord::with('client')->findOrFail($recordId);
        return view('receipt', compact('record'));
    }

    public function edit($id)
    {
        $client = Client::findOrFail($id);
        return response()->json($client);
    }

    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $client = Client::findOrFail($id);
        $client->update($request->all());
        return redirect()->route('dashboard')->with('success', 'Cliente actualizado correctamente.');
    }

    public function report(Request $request)
    {
        $query = \App\Models\ServiceHistory::with('client')->latest('service_date');

        // Filtro por texto (Nombre del cliente o calle)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->whereHas('client', function($clientQuery) use ($search) {
                    $clientQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('street', 'like', "%{$search}%");
                })->orWhere('id', 'like', "%{$search}%");
            });
        }

        // Filtro por Rango de Fechas
        if ($request->filled('start_date')) {
            $query->whereDate('service_date', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('service_date', '<=', $request->input('end_date'));
        }

        $records = $query->get();
        $totalIncome = $records->sum('grand_total');

        return view('report', compact('records', 'totalIncome'));
    }

    public function toggleStatus(Client $client)
    {
        // Cambia el estado actual al contrario
        $client->is_active = !$client->is_active;
        $client->save();

        $statusMsg = $client->is_active ? 'activado' : 'suspendido';

        return back()->with('success', "El servicio para el cliente {$client->name} ha sido {$statusMsg} exitosamente.");
    }

    // 1. Guardar o actualizar la proforma temporal
    public function storeProforma(Request $request, Client $client)
    {
        $validated = $request->validate([
            'items' => 'nullable|array',
            'total_materials' => 'required|numeric',
            'labor_price' => 'required|numeric',
            'labor_desc' => 'nullable|string',
            'grand_total' => 'required|numeric',
        ]);

        // REGLA: Si no hay productos/materiales, creamos un ítem genérico con valor 0
        $items = $validated['items'] ?? [];
        if (empty($items)) {
            $items = [
                [
                    'qty' => 1,
                    'desc' => 'MATERIALS',
                    'price' => 0.00,
                    'amount' => '$0.00'
                ]
            ];
        }

        $validated['items'] = $items;

        Proforma::updateOrCreate(
            ['client_id' => $client->id],
            $validated
        );

        return response()->json(['success' => true, 'message' => 'Proforma guardada correctamente.']);
    }

    // 2. Obtener la proforma guardada (para cargarla en el modal)
    public function getProforma(Client $client)
    {
        return response()->json($client->proforma);
    }

    // 3. Procesar el cobro final (Atender) y limpiar la proforma temporal
    public function attendDetailed(Request $request, Client $client)
    {
        if (!$client->proforma) {
            return response()->json([
                'success' => false, 
                'message' => 'No se puede atender al cliente: Debe crear y guardar una proforma primero.'
            ], 400);
        }

        $data = json_decode($request->input('attendance_data'), true);

        // Guardar en el historial de servicios definitivo
        $serviceHistory = $client->serviceHistories()->create([
            'items' => $data['items'],
            'total_materials' => str_replace('$', '', $data['totalMat']),
            'labor_price' => $data['laborPrice'],
            'labor_desc' => $data['laborDesc'],
            'grand_total' => str_replace('$', '', $data['grandTotal']),
            'service_date' => now(),
        ]);

        // Actualizar último servicio
        $client->update(['last_service_date' => now()]);

        // Eliminar la proforma temporal porque ya se cobró
        $client->proforma->delete();

        // Retornamos éxito y la URL para ver/imprimir el recibo
        return response()->json([
            'success' => true,
            'redirect_url' => route('service.receipt', $serviceHistory->id), // Ajusta el nombre de tu ruta al recibo si es diferente
            'message' => "¡Servicio cobrado exitosamente!"
        ]);
    }

    public function showReceipt(ServiceHistory $record)
    {
        // Carga la vista de la tirilla del recibo con el registro correspondiente
        return view('receipt', compact('record')); // Cambia 'receipt' por el nombre exacto de tu archivo blade (ej. 'clients.receipt' si está en una carpeta)
    }

    public function showA4Document(Request $request)
    {
        $type = $request->get('type', 'proforma');
        $clientId = $request->get('client_id');
        $recordId = $request->get('record_id');

        // MODO RECIBO DE PAGO FINALIZADO
        if ($type === 'recibo') {
            $record = $recordId 
                ? \App\Models\ServiceHistory::with('client')->findOrFail($recordId)
                : \App\Models\ServiceHistory::where('client_id', $clientId)->latest('id')->firstOrFail();
                
            $client = $record->client;

            return view('a4_invoice', [
                'documentTitle'   => 'PAYMENT RECEIPT',
                'documentNumber'  => 'REC-' . str_pad($record->id, 5, '0', STR_PAD_LEFT),
                'documentDate'    => \Carbon\Carbon::parse($record->service_date)->format('m/d/Y H:i'),
                'clientName'      => $client->name,
                'clientAddress'   => trim(($client->street ?? '') . ' ' . ($client->house_number ?? '')),
                'clientPhone'     => $client->phone ?? 'N/A',
                'items'           => $record->items ?? [],
                'laborPrice'      => $record->labor_price ?? 0,
                'laborDesc'       => $record->labor_desc ?? '',
                'totalMaterials'  => $record->total_materials ?? 0,
                'grandTotal'      => $record->grand_total ?? 0,
            ]);
        }

        // MODO PROFORMA PREVIA
        $client = \App\Models\Client::with('proforma')->findOrFail($clientId);
        $proforma = $client->proforma;

        return view('a4_invoice', [
            'documentTitle'   => 'INVOICE',
            'documentNumber'  => 'PROF-' . str_pad($client->id, 5, '0', STR_PAD_LEFT),
            'documentDate'    => date('m/d/Y'),
            'clientName'      => $client->name,
            'clientAddress'   => trim(($client->street ?? '') . ' ' . ($client->house_number ?? '')),
            'clientPhone'     => $client->phone ?? 'N/A',
            'items'           => $proforma->items ?? [],
            'laborPrice'      => $proforma->labor_price ?? 0,
            'laborDesc'       => $proforma->labor_desc ?? '',
            'totalMaterials'  => $proforma->total_materials ?? 0,
            'grandTotal'      => $proforma->grand_total ?? 0,
        ]);
    }
}