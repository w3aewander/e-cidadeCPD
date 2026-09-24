<?php

namespace App\Domain\Patrimonial\Protocolo\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * @property $p114_codigo
 * @property $p114_atividade
 * @property $p114_status
 * @property $p114_descricao
 */
class AtividadeExecucao extends Model
{
    const GERAR = 1;
    const CONFERIR = 2;
    const PRIMEIRA_ASSINATURA = 3;
    const SEGUNDA_ASSINATURA = 5;
    const TERCEIRA_ASSINATURA = 6;
    const ASSINATURA_ECIDADE = 7;
    const ARQUIVAR = 4;

    protected $table = 'protocolo.atividadesexecucao';
    protected $primaryKey = 'p114_codigo';
    public $timestamps = false;
}
