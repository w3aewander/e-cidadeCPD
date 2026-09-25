<?php

namespace App\Domain\Educacao\Escola\Models;

use Illuminate\Database\Eloquent\Model;

class NecessidadeSubdivisao extends Model
{
    protected $table = 'escola.necessidadesubdivisao';
    protected $primaryKey = 'ed185_sequencial';
    public $timestamps = false;
}
