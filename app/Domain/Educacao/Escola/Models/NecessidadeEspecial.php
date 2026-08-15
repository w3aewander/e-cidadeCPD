<?php

namespace App\Domain\Educacao\Escola\Models;

use Illuminate\Database\Eloquent\Model;

class NecessidadeEspecial extends Model
{
    protected $table = 'escola.necessidade';
    protected $primaryKey = 'ed48_i_codigo';
    public $timestamps = false;

    public function subdivisoes()
    {
        return $this->hasMany(
            NecessidadeSubdivisao::class,
            'ed185_necessidade',
            'ed48_i_codigo'
        );
    }
}
