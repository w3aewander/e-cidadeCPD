<?php

namespace App\Domain\Educacao\CentralMatriculas\Enums;

use ECidade\Enum\Enum;

class TipoCampoOpcionalEnum extends Enum
{
    const ESTADO_CIVIL = 1;
    const CARTA0_SUS = 2;
    const MATRICULA_SERVIDOR = 3;
    const BOLSA_FAMILIA = 4;
    const CPF_ALUNO = 5;
    const COR_RACA = 6;

    const NOME_SOCIAL = 7;
    const MAE_VITIMA_VIOLENCIA = 8;
    const RENDA_FAMILIAR = 9;

    public function name()
    {
        $data = array(
            self::ESTADO_CIVIL => "Estado Civil",
            self::CARTA0_SUS => "Cartão Sus",
            self::MATRICULA_SERVIDOR => "Matricula Servidor",
            self::BOLSA_FAMILIA => "Bolsa Familia",
            self::CPF_ALUNO => "CPF Aluno",
            self::COR_RACA => "Cor/Raça",
            self::NOME_SOCIAL => "Nome Social",
            self::MAE_VITIMA_VIOLENCIA => "Mãe Vitima de Violência",
            self::RENDA_FAMILIAR => "Renda Familiar",
        );

        if (empty($data[$this->getValue()])) {
            throw new Exception('Tipo não encontrado.');
        }

        return $data[$this->getValue()];
    }
}
