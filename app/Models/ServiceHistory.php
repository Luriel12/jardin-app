<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'items',
        'total_materials',
        'labor_price',
        'labor_desc',
        'grand_total',
        'service_date',
    ];

    protected $casts = [
        'items' => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}