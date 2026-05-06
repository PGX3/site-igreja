<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoOracao extends Model
{
    protected $table = 'pedidos_oracao';

    protected $fillable = ['nome', 'pedido', 'anonimo', 'lido'];
}
