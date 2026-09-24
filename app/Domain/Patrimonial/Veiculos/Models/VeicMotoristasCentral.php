<?php

namespace App\Domain\Patrimonial\Veiculos\Models;

use Illuminate\Database\Eloquent\Model;

class VeicMotoristasCentral extends Model
{
    protected $table = 'veiculos.veicmotoristascentral';
    protected $primaryKey = 've41_sequencial';

    public function centraisDepartamentos()
    {
        return $this->hasMany(
            VeicCadCentralDepart::class,
            've37_veiccadcentral',
            've41_veiccadcentral'
        )->with('departamento');
    }
}
