<?php

namespace App\Domain\Educacao\MatriculaOnline\Enums;

use ECidade\Enum\Enum;

class TiposDocumentosEnum extends Enum
{
    const LEGISLACAO = 1;
    const TERMOS_USO = 2;
    const LISTA_ESPERA = 3;
    const MANUAL = 4;
    /**
     * @return string
     * @throws Exception
     */
    public function name()
    {
        $data = array(
            self::LEGISLACAO => "Legislação",
            self::TERMOS_USO => "Termos de Uso",
            self::LISTA_ESPERA => "Lista de Espera",
            self::MANUAL => "Manual"
        );

        if (empty($data[$this->getValue()])) {
            throw new Exception('Tipo não encontrado.');
        }

        return $data[$this->getValue()];
    }
}
