<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proforma extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'items',
        'total_materials',
        'labor_price',
        'labor_desc',
        'grand_total',
    ];

    protected $casts = [
        'items' => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}