<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Record; // O el nombre del modelo que estés usando para tus recibos

class ReceiptController extends Controller
{
    public function show(int $id)
    {
        // Busca el recibo por su ID junto con la relación del cliente
        // Si usas otro nombre de modelo, cámbialo aquí (ej. Record, Receipt, etc.)
         $record = Record::with('client')->findOrFail($id);

        // Retorna la vista del recibo que diseñamos antes (ej. resources/views/receipt.blade.php)
        return view('receipt', compact('record'));
    }
}