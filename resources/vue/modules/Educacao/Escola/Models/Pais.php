<?php

namespace App\Domain\Educacao\Escola\Models;

use Illuminate\Database\Eloquent\Model;

class Pais extends Model
{
    protected $table = "escola.pais";
    protected $primaryKey = "ed228_i_codigo";
    public $timestamps = false;
}
