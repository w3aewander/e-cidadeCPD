<?php

namespace App\Domain\Saude\Ambulatorial\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $sd112_sequencial
 * @property string $sd112_descricao
 * @property boolean $sd112_ativo
*/
class UnidadesOrigem extends Model
{
    protected $table = 'ambulatorial.unidadesorigem';
    protected $primaryKey = 'sd112_sequencial';
    protected $fillable = ['sd112_sequencial', 'sd112_descricao','sd112_ativo'];
    public $timestamps = false;
}
