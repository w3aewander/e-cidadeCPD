<?php

namespace App\Domain\Saude\Ambulatorial\Repositories;

use App\Domain\Saude\Ambulatorial\Models\UnidadesOrigem;

class UnidadesOrigemRepository extends UnidadesOrigem
{

    protected $model;

    public function __construct()
    {
        $this->model = new UnidadesOrigem();
    }
    public function getByUnidade($dados)
    {
        $unidadesOrigem = UnidadesOrigem::find($dados['codigo']);
        return $unidadesOrigem;
    }

    public function get()
    {
        $campos = [
            'sd112_sequencial as codigo',
            'sd112_descricao as descricao',
            'sd112_ativo as ativo'
        ];
        $unidadesOrigem = UnidadesOrigem::select($campos)
        ->orderBy('sd112_sequencial', 'DESC')
        ->get();
        return $unidadesOrigem;
    }

    public function createUnidade($dados)
    {
        $this->model->sd112_descricao = $dados["descricao"];
        $this->model->sd112_ativo = $dados["ativo"];
        return $this->model->save();
    }

    public function updateUnidade($dados)
    {
        $unidadesOrigem = UnidadesOrigem::find($dados['codigo']);

        if ($unidadesOrigem) {
            $unidadesOrigem->sd112_descricao = $dados["descricao"];
            $unidadesOrigem->sd112_ativo = $dados["ativo"];
            $unidadesOrigem->save();
        }
        return $unidadesOrigem;
    }
       
    public function deleteUnidade($dados)
    {
        $unidadesOrigem = UnidadesOrigem::find($dados['codigo']);
        if ($unidadesOrigem) {
            $unidadesOrigem->delete();
        }
        return $unidadesOrigem;
    }
}
