<?php

namespace App\Domain\Tributario\Fiscalizacao\Services;

use Illuminate\Database\Capsule\Manager as DB;

final class CheckUseModuloFiscalizacaoService
{
    /**
     * Verifica se o cliente usa o modulo fiscalizacao
     *
     * @return boolean
     * */
    public static function verify()
    {
        $idModuloFiscalizacao = 229038;
        $isLibcliente = DB::table('db_itensmenu')
            ->select('libcliente')
            ->where('id_item', $idModuloFiscalizacao)
            ->where('libcliente', true)
            ->first();

        if (!empty($isLibcliente) && $isLibcliente->libcliente === true) {
            return true;
        }

        return false;
    }
}
