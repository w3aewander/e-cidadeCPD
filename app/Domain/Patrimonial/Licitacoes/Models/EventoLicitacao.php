<?php

namespace App\Domain\Patrimonial\Licitacoes\Models;

use Illuminate\Database\Eloquent\Model;

class EventoLicitacao extends Model
{
    protected $table = 'licitacao.liclicitaevento';
    protected $primaryKey = 'l46_sequencial';
    public $timestamps = false;
}
