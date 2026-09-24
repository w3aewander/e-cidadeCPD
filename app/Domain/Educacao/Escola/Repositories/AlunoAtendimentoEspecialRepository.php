<?php

namespace App\Domain\Educacao\Escola\Repositories;

use App\Domain\Core\Base\Repository\BaseRepository;
use App\Domain\Educacao\Escola\Enums\NacionalidadeEnum;
use App\Domain\Educacao\Escola\Models\AlunoAtendimentoEspecial;
use App\Domain\Educacao\Escola\Resources\AtendimentoEspecialResource;

class AlunoAtendimentoEspecialRepository extends BaseRepository
{
    /**
     * Model
     *
     * @var AlunoAtendimentoEspecial
     */
    protected $modelClass = AlunoAtendimentoEspecial::class;

    public function getAtendimentosEspecial($aluno)
    {
        return $this->newQuery()
            ->where('ed197_aluno', $aluno)
            ->distinct()
            ->orderBy('ed197_data_emissao')
            ->get();
    }

    public function getTiposAtendimentos($aluno)
    {
        return $this->newQuery()
            ->where('ed197_aluno', $aluno)
            ->distinct()
            ->get();
    }

    public function deleteAtendimentoEspecial($codigo)
    {
        return $this->newQuery()
            ->where('ed197_codigo', $codigo)
            ->delete();
    }

    public function persist($parametros)
    {
        return $this->newQuery()->create(AtendimentoEspecialResource::toArrayModel($parametros));
    }

    public function update($parametros)
    {
  
        return $this->newQuery()
                    ->where('ed197_codigo', $parametros['codigo'])
                    ->update(AtendimentoEspecialResource::toArrayModel($parametros));
    }
}
