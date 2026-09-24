<?php

namespace App\Domain\Patrimonial\Veiculos\Services;

class MotoristaService
{
    public static function formataDepartamentos($departamentos)
    {
        $departamentosCentrais = [];

        foreach ($departamentos as $key => $departamento) {
            if (!empty($departamento->departamentoCentralPrincipal)) {
                $departamentosCentrais['central'] = $departamento->departamento;
                $departamentosCentrais['central']['codigo_central'] =
                    $departamento->departamentoCentralPrincipal->ve36_sequencial;
            }

            $departamentosCentrais['departamentos'][$key] = $departamento->departamento;
        }

        return $departamentosCentrais;
    }
}
