<?php

namespace App\Domain\ProcessoEletronico\Resources;

use App\Domain\Tributario\Cadastro\Models\Iptubase;
use App\Domain\Tributario\Cadastro\Models\Proprietario;
use Illuminate\Database\Eloquent\Collection;

class ImoveisResource
{
    /**
     * Retorna um object para resource de imoveis
     *
     * @param \App\Domain\Tributario\Cadastro\Models\Iptubase $iptubase
     *
     * @return object
     */
    public static function toObject(Iptubase $iptubase)
    {
        $proprietario = [];

        if (isset($iptubase->proprietario)) {
            $proprietario = self::toObjectProprietario($iptubase->proprietario);
        }

        $iptubase = self::toObjectIptubase($iptubase);

        return (object) array_merge($iptubase, $proprietario);
    }

    /**
     * Retorna um object para resource de iptubase
     *
     * @param \App\Domain\Tributario\Cadastro\Models\Iptubase $iptubase
     *
     * @return array
     */
    public static function toObjectIptubase(Iptubase $iptubase)
    {
        return [
            'matricula' => $iptubase->j01_matric
        ];
    }

    /**
     * Retorna um object para resource de proprietario
     *
     * @param \App\Domain\Tributario\Cadastro\Models\Iptubase $iptubase
     *
     * @return array
     */
    public static function toObjectProprietario(Proprietario $proprietario)
    {
        return [
            'tipo'        => $proprietario->j01_tipoimp,
            'bairro'      => trim($proprietario->j13_descr),
            'endereco'    => $proprietario->tipopri . ' ' . trim($proprietario->nomepri),
            'complemento' => $proprietario->j39_compl,
            'numero'      => $proprietario->j39_numero,
            'setor'       => $proprietario->j34_setor,
            'quadra'      => $proprietario->j34_quadra,
            'lote'        => $proprietario->j34_lote
        ];
    }

    /**
     * Monta a estrutura para retorno via API
     *
     * @param Illuminate\Database\Eloquent\Collection $collection
     *
     * @return object[]
     */
    public static function toResponse(Collection $collection)
    {
        return $collection->map(function (Iptubase $iptubase) {
            return self::toObject($iptubase);
        });
    }
}
