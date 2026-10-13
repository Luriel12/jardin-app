<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'house_number',
        'street',
        'address',
        'latitude',
        'longitude',
        'last_service_date',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function proforma()
    {
        return $this->hasOne(Proforma::class);
    }

    public function serviceHistories()
    {
        return $this->hasMany(ServiceHistory::class);
    }
}