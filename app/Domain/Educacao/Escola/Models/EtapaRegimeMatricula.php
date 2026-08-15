<?php

namespace App\Domain\Educacao\Escola\Models;

use App\Domain\Financeiro\Planejamento\Models\Comissao;
use Illuminate\Database\Eloquent\Model;

class EtapaRegimeMatricula extends Model
{
    protected $table = 'escola.serieregimemat';
    protected $primaryKey = 'ed223_i_codigo';
    public $timestamps = false;
    public $incrementing = false;

    public function regimeMatricula()
    {
        return $this
            ->belongsTo(RegimeMatricula::class, 'ed223_i_regimemat', 'ed218_i_codigo');
    }
}
