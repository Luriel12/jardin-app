<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $documentTitle ?? 'Service Document' }} - Calderoni Services</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: #f3f4f6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1e293b;
            padding: 24px;
            margin: 0;
            display: flex;
            justify-content: center;
        }
        .a4-sheet {
            width: 210mm;
            min-height: 297mm;
            background: #ffffff;
            padding: 18mm;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        @media print {
            body { background: none; padding: 0; }
            .no-print { display: none !important; }
            .a4-sheet { box-shadow: none; border-radius: 0; width: 100%; min-height: auto; padding: 10mm; }
        }
    </style>
    <!-- html2pdf.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <!-- jsPDF CDN for Thermal generation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body>

    <div class="a4-sheet" id="a4-content">
        <div>
            <!-- HEADER WITH LOGO & SYSTEM PALETTE -->
            <div class="flex justify-between items-start border-b-2 border-slate-800 pb-6 mb-6">
                <!-- Logo & Brand -->
                <div class="flex items-center gap-4">
                    <img id="companyLogoImg" src="{{ asset('images/logo.png') }}" alt="Calderoni Services Logo" class="h-16 w-auto object-contain" onerror="this.style.display='none'">
                    <div>
                        <h1 class="text-2xl font-black tracking-wide text-slate-900">CALDERONI SERVICES LLC</h1>
                        <p class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Professional Landscaping & Property Maintenance</p>
                    </div>
                </div>

                <!-- Document Title Box -->
                <div class="text-right">
                    <div class="bg-slate-900 text-white px-4 py-2 rounded-xl shadow-sm">
                        <p id="docTitleText" class="text-sm font-bold uppercase tracking-wider">{{ $documentTitle ?? 'INVOICE' }}</p>
                    </div>
                    <p id="docNumberText" class="text-xs font-mono font-bold text-slate-600 mt-2">N° {{ $documentNumber ?? 'PROF-00001' }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">Date: <span id="docDateText" class="font-medium text-slate-800">{{ $documentDate ?? date('m/d/Y') }}</span></p>
                </div>
            </div>

            <!-- CLIENT INFORMATION -->
            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 mb-6 text-xs">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Customer Information</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <p class="text-slate-500">Customer Name:</p>
                        <p id="clientNameText" class="text-sm font-bold text-slate-900 uppercase">{{ $clientName ?? 'VALUED CUSTOMER' }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-slate-500">Service Address:</p>
                        <p id="clientAddressText" class="font-medium text-slate-800">📍 {{ $clientAddress ?? 'No address provided' }}</p>
                        @if(!empty($clientPhone) &&$clientPhone !== 'N/A')
                            <p class="text-slate-500 mt-1">Phone: <span id="clientPhoneText" class="font-medium text-slate-800">📞 {{ $clientPhone }}</span></p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- DETAILS TABLE -->
            <table class="w-full text-xs text-left mb-6 border-collapse">
                <thead>
                    <tr class="bg-slate-900 text-white uppercase tracking-wider font-semibold text-[11px]">
                        <th class="p-3 rounded-l-xl text-center w-16">Qty</th>
                        <th class="p-3">Description</th>
                        <th class="p-3 text-right w-28">Unit Price</th>
                        <th class="p-3 text-right rounded-r-xl w-28">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="receiptItemsList">
                    @php
                        $itemList = [];
                        if (isset($items)) {
                            $itemList = is_string($items) ? json_decode($items, true) :$items;
                        }
                    @endphp

                    @php
                        if (!empty($itemList) && is_array($itemList)) {
                            foreach ($itemList as$item) {
                                $qty =$item['qty'] ?? 1;
                                $desc =$item['desc'] ?? $item['description'] ?? 'MATERIALS';$price = number_format((float)str_replace('$', '',$item['price'] ?? 0), 2);
                                $amount =$item['amount'] ?? '$0.00';
                                
                                echo '<tr class="hover:bg-slate-50/50 receipt-item-row">';
                                echo '<td class="p-3 text-center font-bold text-slate-700 qty">' . $qty . '</td>';
                                echo '<td class="p-3 uppercase text-slate-800 font-medium desc">' . htmlspecialchars($desc) . '</td>';
                                echo '<td class="p-3 text-right text-slate-600">$' .$price . '</td>';
                                echo '<td class="p-3 text-right font-bold text-slate-900 amount">' . htmlspecialchars($amount) . '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr class="receipt-item-row">';
                            echo '<td class="p-3 text-center font-bold text-slate-700 qty">1</td>';
                            echo '<td class="p-3 uppercase text-slate-800 font-medium desc">MATERIALS</td>';
                            echo '<td class="p-3 text-right text-slate-600">$0.00</td>';
                            echo '<td class="p-3 text-right font-bold text-slate-900 amount">$0.00</td>';
                            echo '</tr>';
                        }
                    @endphp

                    @if(isset($laborPrice) &&$laborPrice > 0)
                        <tr class="bg-amber-50/40 receipt-item-row">
                            <td class="p-3 text-center font-bold text-amber-700 qty">1</td>
                            <td class="p-3 uppercase font-semibold text-slate-800 desc">
                                Labor / Service Fee
                                @if(!empty($laborDesc))
                                    <span class="block text-[10px] text-slate-500 normal-case font-normal mt-0.5">{{ $laborDesc }}</span>
                                @endif
                            </td>
                            <td class="p-3 text-right text-slate-600">${{ number_format($laborPrice, 2) }}</td>
                            <td class="p-3 text-right font-bold text-slate-900 amount">${{ number_format($laborPrice, 2) }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div>
            <!-- TOTALS SUMMARY -->
            <div class="flex justify-end mb-8">
                <div class="w-72 bg-slate-50 border border-slate-200 rounded-2xl p-4 text-xs space-y-2">
                    <div class="flex justify-between text-slate-600">
                        <span>Total Materials:</span>
                        <span class="font-medium text-slate-800">${{ number_format($totalMaterials ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Labor / Service Fee:</span>
                        <span class="font-medium text-slate-800">${{ number_format($laborPrice ?? 0, 2) }}</span>
                    </div>
                    <div class="border-t border-slate-200 pt-2 flex justify-between items-center text-sm font-black text-slate-900">
                        <span>TOTAL DUE:</span>
                        <span id="grandTotalText" class="text-base text-emerald-600">${{ number_format($grandTotal ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- FOOTER & THANK YOU -->
            <div class="text-center text-xs text-slate-400 border-t border-slate-100 pt-4">
                <p class="font-semibold text-slate-600">Thank you for choosing Calderoni Services LLC!</p>
                <p class="text-[10px] mt-0.5">Please contact us directly for any questions regarding this document.</p>
            </div>
        </div>
    </div>

    <!-- FLOATING ACTION BUTTONS (Hidden when printing) -->
    <div class="no-print fixed bottom-6 right-6 bg-white/90 backdrop-blur-md p-2.5 rounded-2xl shadow-2xl border border-slate-200 flex gap-2">
        <button onclick="shareThermalWhatsApp()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-xl text-xs font-bold shadow flex items-center gap-1.5 transition">
            💬 WhatsApp (Thermal)
        </button>
        <button onclick="window.print()" class="bg-slate-900 hover:bg-slate-800 text-white px-4 py-2.5 rounded-xl text-xs font-bold shadow flex items-center gap-1.5 transition">
            🖨️ Print
        </button>
        <button onclick="downloadA4PDF()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-xs font-bold shadow flex items-center gap-1.5 transition">
            📥 Download A4
        </button>
        <button onclick="smartGoBack()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2.5 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition">
            ← Back
        </button>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            
            // Si la URL NO tiene el parámetro 'reloaded', la recargamos automáticamente una sola vez
            if (!urlParams.has('reloaded')) {
                const separator = window.location.search ? '&' : '?';
                window.location.href = window.location.href + separator + 'reloaded=1';
            }
        });
        
        // Navegación de retorno inteligente (Detecta si viene de Reportes o Dashboard)
        function smartGoBack() {
            // Si la ventana tiene un abridor o es una pestaña nueva, la cerramos limpiamente
            if (window.opener || window.history.length <= 1) {
                window.close();
            } else {
                // Fallback por si acaso el navegador bloquea el cierre directo
                window.history.back();
            }
        }

        // Descarga de PDF en formato A4 formal
        function downloadA4PDF() {
            const element = document.getElementById('a4-content');
            const clientName = "{{ $clientName ?? 'Customer' }}".replace(/\s+/g, '_');
            const opt = {
                margin:      [10, 10, 10, 10],
                filename:    `Document-A4-${clientName}.pdf`,
                image:       { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true },
                jsPDF:       { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().from(element).set(opt).save();
        }

        // Función auxiliar para cargar el logo y convertirlo a Base64 de forma segura
        async function getLogoBase64(url) {
            return new Promise((resolve) => {
                const img = new Image();
                img.crossOrigin = 'anonymous';
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    canvas.width = img.naturalWidth;
                    canvas.height = img.naturalHeight;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0);
                    resolve({
                        dataUrl: canvas.toDataURL('image/png'),
                        width: img.naturalWidth,
                        height: img.naturalHeight
                    });
                };
                img.onerror = () => resolve(null);
                img.src = url;
            });
        }

        // Variable global o de ámbito para controlar el bloqueo
        let isSharingThermal = false;

        async function shareThermalWhatsApp() {
            // Si ya hay un proceso de compartición activo, ignoramos el nuevo clic
            if (isSharingThermal) return;
            isSharingThermal = true;

            try {
                const { jsPDF } = window.jspdf;
                if (!jsPDF) {
                    throw new Error("jsPDF library is not loaded.");
                }

                // Generamos el formato térmico (80 x 150 mm)
                const doc = new jsPDF({
                    unit: 'mm',
                    format: [80, 150]
                });

                let y = 8; // Empezamos un poquito más arriba para dar espacio al logo

                // --- Inserción Segura del Logo ---
                const logoInfo = await getLogoBase64("{{ asset('images/logo.png') }}");
                if (logoInfo) {
                    const imgWidth = 24; // Ancho fijo en mm para la tirilla térmica
                    const imgHeight = (logoInfo.height / logoInfo.width) * imgWidth; // Alto proporcional
                    const imgX = (80 - imgWidth) / 2; // Centrado horizontal (80mm ancho total)

                    doc.addImage(logoInfo.dataUrl, 'PNG', imgX, y, imgWidth, imgHeight);
                    y += imgHeight + 3; // Bajamos el cursor 'y' dejando espacio limpio
                }

                const docTitle = document.getElementById('docTitleText')?.innerText || 'SERVICE DOCUMENT';
                const recDate = document.getElementById('docDateText')?.innerText || '';
                const recNumber = document.getElementById('docNumberText')?.innerText || 'N° PROF-00001';
                const clientName = document.getElementById('clientNameText')?.innerText || 'Valued Customer';
                const clientAddress = document.getElementById('clientAddressText')?.innerText.replace('📍 ', '') || 'No address provided';
                const clientPhone = document.getElementById('clientPhoneText')?.innerText.replace('📞 ', '') || 'N/A';
                const totalPaid = document.getElementById('grandTotalText')?.innerText || '$0.00';

                // Header Texts
                doc.setFont("helvetica", "bold");
                doc.setFontSize(10);
                doc.text("CALDERONI SERVICES LLC", 40, y, { align: "center" });
                
                y += 4;
                doc.setFont("helvetica", "normal");
                doc.setFontSize(7.5);
                doc.text("Professional Landscaping & Maintenance", 40, y, { align: "center" });
                
                y += 5;
                doc.setFont("helvetica", "bold");
                doc.text(docTitle, 40, y, { align: "center" });

                y += 5;
                doc.setFont("helvetica", "normal");
                doc.setFontSize(7);
                doc.text(`Date: ${recDate.trim()}`, 5, y);
                doc.text(`${recNumber.trim()}`, 45, y);

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
                const rows = document.querySelectorAll('.receipt-item-row');

                rows.forEach(row => {
                    const qtyEl = row.querySelector('.qty');
                    const descEl = row.querySelector('.desc');
                    const amountEl = row.querySelector('.amount');

                    const qtyText = qtyEl ? qtyEl.innerText.trim() + 'x ' : '1x ';
                    const descText = descEl ? descEl.innerText.trim() : '';
                    const fullDesc = qtyText + descText;
                    const amount = amountEl ? amountEl.innerText.trim() : '$0.00';

                    const splitDesc = doc.splitTextToSize(fullDesc, 48);
                    doc.text(splitDesc, 5, y);
                    doc.text(amount, 62, y);
                    y += (Math.max(splitDesc.length, 1) * 4) + 1;
                });

                y += 1;
                doc.line(5, y, 75, y);
                y += 4;

                // Total
                doc.setFont("helvetica", "bold");
                doc.setFontSize(9);
                doc.text("TOTAL:", 5, y);
                doc.text(totalPaid.trim(), 55, y);

                y += 6;
                doc.setFont("helvetica", "italic");
                doc.setFontSize(7);
                doc.text("Thank you for choosing Calderoni Services LLC!", 40, y, { align: "center" });

                const pdfBlob = doc.output('blob');
                
                // Validación en mayúsculas y prefijos exactos
                const upperTitle = docTitle.toUpperCase();
                const isProforma = upperTitle.includes('PROFORMA') || upperTitle.includes('INVOICE');
                const filePrefix = isProforma ? 'INVOICE' : 'PAYMENT_RECEIPT';
                const fileName = `${filePrefix}-${clientName.replace(/\s+/g, '_')}.pdf`;

                const file = new File([pdfBlob], fileName, { type: 'application/pdf' });

                // Texto dinámico según el tipo de documento en inglés
                const docTypeLabel = isProforma ? 'invoice' : 'payment receipt';

                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    await navigator.share({
                        title: docTitle + ' - Calderoni Services LLC',
                        text: `Hello ${clientName.trim()}, here is your ${docTypeLabel} for a total of ${totalPaid}.`,
                        files: [file],
                    });
                } else {
                    doc.save(fileName);
                    const message = encodeURIComponent(`Hello ${clientName.trim()}, here is your ${docTypeLabel} for a total of ${totalPaid}.`);
                    window.open(`https://api.whatsapp.com/send?text=${message}`, '_blank');
                }
            } catch (error) {
                if (error.name !== 'AbortError') {
                    console.error('Error sharing thermal WhatsApp:', error);
                    alert('Could not share document via WhatsApp: ' + error.message);
                }
            } finally {
                isSharingThermal = false;
            }
        }
    </script>
</body>
</html>