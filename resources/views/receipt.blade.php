<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - Calderoni Services</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: #f3f4f6;
            margin: 0;
            padding: 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .receipt-box {
            width: 100%;
            max-width: 320px;
            background: #ffffff;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
        }
        @media print {
            body { background: white; padding: 0; }
            .no-print { display: none !important; }
            .receipt-box { box-shadow: none !important; border: none !important; width: 100% !important; max-width: 100% !important; }
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body>

    <!-- Thermal Receipt Box Container -->
    <div id="pdf-container" class="receipt-box space-y-4 text-sm text-gray-800">
        
        <!-- Business Header -->
        <div class="text-center border-b pb-3">
            <h1 class="text-base font-black tracking-wider text-gray-900">CALDERONI SERVICES LLC</h1>
            <p class="text-[10px] text-gray-500 mt-0.5">Professional Landscaping & Maintenance</p>
            <p class="text-[10px] font-bold text-emerald-600 mt-1 uppercase tracking-wider">OFFICIAL PAYMENT RECEIPT</p>
        </div>

        <!-- Receipt Metadata -->
        <div class="space-y-1 text-xs text-gray-600 border-b pb-3">
            <div class="flex justify-between">
                <span>Date:</span>
                <span id="recDate" class="font-medium text-gray-800">{{ \Carbon\Carbon::parse($record->service_date)->format('m/d/Y H:i') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Receipt N°:</span>
                <span id="recNumber" class="font-medium text-gray-800">#{{ str_pad($record->id, 5, '0', STR_PAD_LEFT) }}</span>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="space-y-1 text-xs border-b pb-3">
            <p class="font-bold text-gray-700 uppercase">CUSTOMER:</p>
            <p id="recName" class="text-gray-900 font-semibold uppercase">{{ $record->client->name }}</p>
            <p id="recAddress" class="text-gray-600">📍 {{ $record->client->street }} {{ $record->client->house_number ? 'N° ' . $record->client->house_number : '' }}</p>
            @if($record->client->phone)
                <p id="recPhone" class="text-gray-600">📞 {{ $record->client->phone }}</p>
            @endif
        </div>

        <!-- Transaction Details (Items & Labor) -->
        <div class="space-y-2 border-b pb-3">
            <div class="flex justify-between text-xs font-bold text-gray-700">
                <span>QTY / DESCRIPTION</span>
                <span>TOTAL</span>
            </div>
            
            <div class="space-y-1.5" id="receiptItemsList">
                @php
                    $items = is_string($record->items) ? json_decode($record->items, true) :$record->items;
                @endphp

                @if(!empty($items) && is_array($items))
                    @foreach($items as$item)
                        <div class="flex justify-between text-xs receipt-item-row">
                            <span class="desc text-gray-700">
                                <span class="font-semibold text-gray-900">{{ $item['qty'] ?? 1 }}x</span> {{ $item['desc'] ?? $item['description'] ?? 'MATERIALS' }}
                            </span>
                            <span class="amount font-semibold text-gray-900">{{ $item['amount'] ?? '$0.00' }}</span>
                        </div>
                    @endforeach
                @else
                    <div class="flex justify-between text-xs receipt-item-row">
                        <span class="desc text-gray-700">
                            <span class="font-semibold text-gray-900">1x</span> MATERIALS
                        </span>
                        <span class="amount font-semibold text-gray-900">$0.00</span>
                    </div>
                @endif

                @if($record->labor_price > 0)
                    <div class="flex justify-between text-xs receipt-item-row border-t pt-1">
                        <span class="desc text-gray-700 font-medium">Labor / Service Fee:</span>
                        <span class="amount font-semibold text-gray-900">${{ number_format($record->labor_price, 2) }}</span>
                    </div>
                    @if($record->labor_desc)
                        <p class="text-[10px] text-gray-500 italic">Note: {{ $record->labor_desc }}</p>
                    @endif
                @endif
            </div>
        </div>

        <!-- Total Paid -->
        <div class="bg-emerald-50/50 p-3 rounded-xl border border-emerald-100 flex justify-between items-center">
            <span class="font-bold text-gray-800 text-xs">TOTAL PAID:</span>
            <span id="recGrandTotal" class="text-base font-black text-emerald-700">${{ number_format($record->grand_total, 2) }}</span>
        </div>

        <!-- Thank You Note -->
        <div class="text-center text-[10px] text-gray-400 pt-1">
            <p>Thank you for choosing Calderoni Services LLC!</p>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="no-print w-full max-w-[320px] space-y-2 mt-4">
        <button onclick="sharePDFOnWhatsApp()" class="w-full bg-green-600 hover:bg-green-700 text-white py-2.5 rounded-xl text-xs font-bold shadow flex items-center justify-center gap-2 transition">
            💬 Share Receipt via WhatsApp (Thermal)
        </button>
        <button onclick="openA4Receipt()" class="w-full bg-slate-900 hover:bg-slate-800 text-white py-2.5 rounded-xl text-xs font-bold shadow flex items-center justify-center gap-2 transition">
            🖨️ Print / Download A4 Format
        </button>
        <a href="{{ route('dashboard') }}" class="block text-center w-full bg-gray-200 hover:bg-gray-300 text-gray-700 py-2.5 rounded-xl text-xs font-semibold transition">
            ← Back to Dashboard
        </a>
    </div>

    <script>
        // 1. Open A4 Format for Print / PDF Download
        function openA4Receipt() {
            window.open(`/documents/a4?type=recibo&record_id={{ $record->id }}`, '_blank');
        }

        // 2. Share 80mm Thermal Receipt via WhatsApp
        async function sharePDFOnWhatsApp() {
            try {
                const { jsPDF } = window.jspdf;
                if (!jsPDF) {
                    throw new Error("jsPDF library is not loaded.");
                }

                const doc = new jsPDF({
                    unit: 'mm',
                    format: [80, 160]
                });

                const container = document.getElementById('pdf-container');
                if (!container) {
                    alert('Receipt container not found.');
                    return;
                }

                const recDate = container.querySelector('#recDate')?.innerText || '';
                const recNumber = container.querySelector('#recNumber')?.innerText || '#00000';
                const clientName = container.querySelector('#recName')?.innerText || 'Valued Customer';
                const clientAddress = container.querySelector('#recAddress')?.innerText.replace('📍 ', '') || 'No address provided';
                const clientPhone = container.querySelector('#recPhone')?.innerText.replace('📞 ', '') || 'N/A';
                const totalPaid = container.querySelector('#recGrandTotal')?.innerText || '$0.00';

                let y = 10;

                // Header
                doc.setFont("helvetica", "bold");
                doc.setFontSize(10);
                doc.text("CALDERONI SERVICES LLC", 40, y, { align: "center" });
                
                y += 4;
                doc.setFont("helvetica", "normal");
                doc.setFontSize(8);
                doc.text("Professional Landscaping & Maintenance", 40, y, { align: "center" });
                
                y += 5;
                doc.setFont("helvetica", "bold");
                doc.text("OFFICIAL PAYMENT RECEIPT", 40, y, { align: "center" });

                y += 5;
                doc.setFont("helvetica", "normal");
                doc.setFontSize(7);
                doc.text(`Date: ${recDate.trim()}`, 5, y);
                doc.text(`Receipt N°: ${recNumber.trim()}`, 45, y);

                y += 3;
                doc.line(5, y, 75, y);
                y += 4;

                // Customer Data
                doc.setFont("helvetica", "bold");
                doc.text("CUSTOMER:", 5, y);
                doc.setFont("helvetica", "normal");
                doc.text(clientName.trim(), 23, y);

                y += 4;
                doc.setFont("helvetica", "bold");
                doc.text("ADDRESS:", 5, y);
                doc.setFont("helvetica", "normal");
                const splitAddress = doc.splitTextToSize(clientAddress.trim(), 48);
                doc.text(splitAddress, 23, y);
                y += (splitAddress.length * 4);

                y += 1;
                doc.setFont("helvetica", "bold");
                doc.text("PHONE:", 5, y);
                doc.setFont("helvetica", "normal");
                doc.text(clientPhone.trim(), 23, y);

                y += 4;
                doc.line(5, y, 75, y);
                y += 4;

                // Table Header
                doc.setFont("helvetica", "bold");
                doc.setFontSize(8);
                doc.text("QTY / DESCRIPTION", 5, y);
                doc.text("TOTAL", 62, y);

                y += 3;
                doc.line(5, y, 75, y);
                y += 4;

                // Receipt Items
                doc.setFont("helvetica", "normal");
                const rows = container.querySelectorAll('.receipt-item-row');

                rows.forEach(row => {
                    const descEl = row.querySelector('.desc');
                    const amountEl = row.querySelector('.amount');

                    const desc = descEl ? descEl.innerText : '';
                    const amount = amountEl ? amountEl.innerText : '';

                    const splitDesc = doc.splitTextToSize(desc.trim(), 48);
                    doc.text(splitDesc, 5, y);
                    doc.text(amount.trim(), 62, y);
                    y += (Math.max(splitDesc.length, 1) * 4) + 1;
                });

                y += 1;
                doc.line(5, y, 75, y);
                y += 4;

                // Total Paid
                doc.setFont("helvetica", "bold");
                doc.setFontSize(9);
                doc.text("TOTAL PAID:", 5, y);
                doc.text(totalPaid.trim(), 52, y);

                y += 6;
                doc.setFont("helvetica", "italic");
                doc.setFontSize(7);
                doc.text("Thank you for choosing Calderoni Services LLC!", 40, y, { align: "center" });

                const pdfBlob = doc.output('blob');
                const fileName = `Receipt-Calderoni-${recNumber.replace('#', '')}.pdf`;
                const file = new File([pdfBlob], fileName, { type: 'application/pdf' });

                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    await navigator.share({
                        title: 'Payment Receipt - Calderoni Services LLC',
                        text: `Hello ${clientName.trim()}, thank you for your payment! Here is your official receipt for ${totalPaid}.`,
                        files: [file],
                    });
                } else {
                    // Fallback: download PDF and open WhatsApp web with text message
                    doc.save(fileName);
                    const message = encodeURIComponent(`Hello ${clientName.trim()}, thank you for your payment! Here is your official receipt PDF for ${totalPaid}.`);
                    window.open(`https://api.whatsapp.com/send?text=${message}`, '_blank');
                }
            } catch (error) {
                console.error('Error sharing receipt:', error);
                alert('Could not share document: ' + error.message);
            }
        }
    </script>
</body>
</html>