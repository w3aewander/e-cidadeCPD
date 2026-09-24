<?php

namespace App\Domain\Educacao\MatriculaOnline\Models;

use Illuminate\Database\Eloquent\Model;
use test\Mockery\HasUnknownClassAsTypeHintOnMethod;

class Fase extends Model
{
    protected $table = 'plugins.fase';
    public $timestamps = false;
    protected $primaryKey = 'mo04_codigo';
    public $incrementing = true;
    protected $dates = [
        'mo04_datacorte',
        'mo04_dtfim',
        'mo04_dtini'
    ];

    protected $fillable = [
        'mo04_codigo',
        'mo04_desc',
        'mo04_anousu',
        'mo04_dtfim',
        'mo04_dtini',
        'mo04_ciclo',
        'mo04_datacorte',
        'mo04_processada',
        'mo04_encerrada',
        'mo04_publicos_alvo',
        'mo04_exibe_escola_origem',
        'mo04_hora_final',
        'mo04_hora_inicial',
        'mo04_opcoes_escolha'
    ];

    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class, 'mo04_ciclo', 'mo09_codigo');
    }
}
