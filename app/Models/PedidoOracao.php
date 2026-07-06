<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoOracao extends Model
{
    public const STATUS_NOVO = 'novo';

    public const STATUS_EM_ORACAO = 'em_oracao';

    public const STATUS_INATIVO = 'inativo';

    public const STATUS_CONCLUIDO = 'concluido';

    public const STATUSES = [
        self::STATUS_NOVO,
        self::STATUS_EM_ORACAO,
        self::STATUS_INATIVO,
        self::STATUS_CONCLUIDO,
    ];

    protected $table = 'pedidos_oracao';

    protected $fillable = ['nome', 'pedido', 'anonimo', 'status', 'compartilhar'];

    protected $casts = [
        'anonimo' => 'boolean',
        'compartilhar' => 'boolean',
    ];
}
