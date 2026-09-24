<?php
namespace App\Domain\Educacao\CentralMatriculas\Models;

use Carbon\Carbon;
use ECidade\Educacao\MatriculaOnline\Model\Escolas;
use ECidade\Educacao\MatriculaOnline\Model\Fase;
use Etapa;
use Illuminate\Database\Eloquent\Model;

/**
 * @package App\Domain\Educacao\CentralMatriculas\Models
 * @property integer $mo64_codigo
 * @property integer $mo64_fase
 * @property integer $mo64_escola
 * @property integer $mo64_etapa
 * @property integer $mo64_turno
 * @property integer $mo64_vagas
 * @property Escola $escola
 */
class VagaPcd extends Model
{
    protected $table = "plugins.vagaspcd";
    protected $primaryKey = 'mo64_codigo';

    protected $fillable = [
        'mo64_fase',
        'mo64_escola',
        'mo64_etapa',
        'mo64_turno',
        'mo64_vagas'
    ];

    public function escola()
    {
        return $this->belongsTo(Escola::class, 'mo64_escola', 'mo53_codigo');
    }

    public function scopeFase($query, Fase $fase)
    {
        return $query->where('mo64_fase', $fase->getCodigo());
    }
    public function scopeEscola($query, Escolas $escola)
    {
        return $query->where('mo64_escola', $escola->getCodigo());
    }
    public function scopeEtapa($query, Etapa $etapa)
    {
        return $query->where('mo64_etapa', $etapa->getCodigo());
    }
    public function scopeTurno($query, \Turno $turno)
    {
        return $query->where('mo64_turno', $turno->getCodigoTurno());
    }
}
