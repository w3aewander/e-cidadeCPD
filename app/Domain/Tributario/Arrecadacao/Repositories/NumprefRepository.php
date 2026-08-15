<?php

namespace App\Domain\Tributario\Arrecadacao\Repositories;

use App\Domain\Tributario\Arrecadacao\Models\Numpref;

class NumprefRepository
{
    /**
     * @var Numpref
     */
    private $numpref;

    public function __construct()
    {
        $this->numpref = new Numpref();
    }

    /**
     * Retorna os dados com base no ano
     * @param $ano
     * @param string[] $campos
     * @return Numpref|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|null
     */
    public function getByAno($ano, $campos = ["*"])
    {
        return $this->numpref->where(
            "k03_anousu",
            "=",
            $ano
        )->select($campos)->first();
    }

    /**
     * @return bool
     */
    public function buscaParametroCustasHonorarios()
    {
        $ano = db_getsession("DB_anousu");
        $instit = db_getsession("DB_instit");
        $oNumpref = $this->numpref->where(
            "k03_anousu",
            "=",
            $ano
        )->where(
            "k03_instit",
            "=",
            $instit
        )->select(['k03_custashonorarios'])
            ->first();
        return $oNumpref->k03_custashonorarios == 't';
    }
}
