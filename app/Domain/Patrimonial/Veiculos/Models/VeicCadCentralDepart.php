<?php

namespace App\Domain\Patrimonial\Veiculos\Models;

use App\Domain\Configuracao\Departamento\Models\Departamento;
use Illuminate\Database\Eloquent\Model;

class VeicCadCentralDepart extends Model
{
    protected $table = 'veiculos.veiccadcentraldepart';
    protected $primaryKey = 've37_sequencial';
    public $timestamps = false;
    protected $fillable = [
        've37_veiccadcentral',
        've37_coddepto'
    ];

    public function departamento()
    {
        return $this->hasOne(
            Departamento::class,
            'coddepto',
            've37_coddepto'
        );
    }

    public function departamentoCentralPrincipal()
    {
        return $this->hasOne(
            VeicCadCentral::class,
            've36_coddepto',
            've37_coddepto'
        );
    }
}
