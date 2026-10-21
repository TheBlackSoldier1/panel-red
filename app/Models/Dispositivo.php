<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dispositivo extends Model
{
    // Campos permitidos al guardar un dispositivo.
    protected $fillable = [
        'nombre',
        'direccion_ip',
    ];
}