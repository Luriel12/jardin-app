<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Income Report - Calderoni Services</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 font-sans pb-12">

    <!-- HEADER -->
    <header class="bg-green-600 text-white p-4 shadow-md sticky top-0 z-50">
        <div class="max-w-4xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2">
                <span class="text-xl">📊</span>
                <h1 class="text-lg font-bold tracking-wide">Income Report</h1>
            </div>
            <a href="{{ route('dashboard') }}" class="bg-white text-green-700 font-semibold px-3 py-1.5 rounded-xl text-xs shadow hover:bg-green-50 transition flex items-center gap-1">
                ← Back to Dashboard
            </a>
        </div>
    </header>

    <main class="max-w-4xl mx-auto p-4 space-y-4">
        
        <!-- SUMMARY CARD -->
        <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-100 flex justify-between items-center">
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Accumulated Income</p>
                <h2 class="text-3xl font-black text-green-700 mt-1">${{ number_format($totalIncome ?? 0, 2) }}</h2>
            </div>
            <div class="text-right bg-gray-50 border border-gray-100 px-4 py-3 rounded-xl">
                <p class="text-xs text-gray-500 font-medium">Services Recorded:</p>
                <p class="text-lg font-bold text-gray-700">{{ count($records) }}</p>
            </div>
        </div>

        <!-- ADVANCED FILTER MODULE -->
        <div class="bg-white p-5 rounded-2xl shadow-md border border-gray-100 space-y-4">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Filter Reports</h3>
            
            <form action="{{ route('report') }}" method="GET" class="space-y-3">
                <!-- Search text -->
                <div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="🔍 Search by customer name or street..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-xs focus:ring-2 focus:ring-green-500 focus:outline-none transition">
                </div>

                <!-- Date range & Buttons -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">From Date</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-green-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">To Date</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-green-500 focus:outline-none">
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-4 rounded-xl text-xs shadow transition">
                            Filter
                        </button>
                        <a href="{{ route('report') }}" class="w-1/2 text-center bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2.5 px-3 rounded-xl text-xs transition flex items-center justify-center">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- TRANSACTION LIST -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
            <div class="p-4 border-b border-gray-100 bg-gray-50 font-bold text-xs text-gray-700 uppercase tracking-wider">
                Service & Payment History
            </div>
            
            <div class="divide-y divide-gray-100">
                @forelse($records as $record)
                    <div class="p-4 flex justify-between items-center hover:bg-gray-50 gap-4 transition">
                        <div class="space-y-0.5">
                            <h3 class="font-bold text-sm text-gray-900 uppercase">{{ $record->client->name ?? 'Deleted Customer' }}</h3>
                            <p class="text-xs text-gray-500">📍 {{ $record->client->street ?? '' }} {{ $record->client->house_number ? 'N° ' . $record->client->house_number : '' }}</p>
                            <p class="text-[11px] text-gray-400 font-mono">Date: {{ \Carbon\Carbon::parse($record->service_date)->format('m/d/Y H:i') }} | Receipt #{{ str_pad($record->id, 5, '0', STR_PAD_LEFT) }}</p>
                        </div>
                        
                        <div class="flex items-center gap-3">
                            <span class="bg-green-100 text-green-800 font-bold px-3 py-1.5 rounded-xl text-sm">
                                ${{ number_format($record->grand_total, 2) }}
                            </span>
                            <a href="{{ route('documents.a4') }}?type=recibo&record_id={{ $record->id }}" target="_blank" class="text-xs bg-blue-50 text-blue-600 font-medium px-3 py-2 rounded-lg hover:bg-blue-100 flex items-center gap-1 shadow-sm transition">
                                🖨️ View Receipt
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-gray-400 text-xs">
                        No payment or service records found matching your filters.
                    </div>
                @endforelse
            </div>
        </div>
    </main>
</body>
</html>