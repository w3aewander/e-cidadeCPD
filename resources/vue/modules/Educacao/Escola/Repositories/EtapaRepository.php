<?php

namespace App\Domain\Educacao\Escola\Repositories;

use App\Domain\Core\Base\Repository\BaseRepository;
use App\Domain\Educacao\Escola\Models\Etapa;

class EtapaRepository extends BaseRepository
{
    protected $modelClass = Etapa::class;

    public function get($codigo)
    {
        return $this->newQuery()->find($codigo);
    }
    public function getByRegimeMatricula($regime)
    {
        return $this->newQuery()
            ->join('serieregimemat', 'ed223_i_serie', '=', 'ed11_i_codigo')
            ->join('regimemat', 'ed218_i_codigo', '=', 'ed223_i_regimemat')
            ->where('ed223_i_regimemat', $regime)->get();
    }

    public function getEtapasNoIntervalo($etapaI, $etapaF)
    {
        $inicial = $this->newQuery()->find($etapaI);
        $final = $this->newQuery()->find($etapaF);
        $etapasNoIntervalo = $this->newQuery()->where('ed11_i_ensino', $inicial->ed11_i_ensino)
            ->whereBetween('ed11_i_sequencia', [$inicial->ed11_i_sequencia, $final->ed11_i_sequencia])->get();

        return $etapasNoIntervalo;
    }
}
