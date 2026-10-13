<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    use HasFactory;

    // Si tu tabla se llama diferente en la base de datos, descomenta y ajusta esta línea:
    // protected $table = 'nombre_de_tu_tabla';

    protected $guarded = [];

    // Relación con el cliente (según tu código anterior)
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}