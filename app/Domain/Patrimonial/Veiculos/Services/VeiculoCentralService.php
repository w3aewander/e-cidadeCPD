<?php

namespace App\Domain\Patrimonial\Veiculos\Services;

use Illuminate\Support\Facades\DB;

class VeiculoCentralService
{

    public function buscarVeiculosCentralRetirada($codCentral)
    {
        $sql = "SELECT DISTINCT
                ve01_codigo,
                ve01_placa,
                ve20_descr,
                ve21_descr,
                ve22_descr,
                ve23_descr,
                ve01_chassi,
                ve01_certif,
                ve01_anofab,
                ve01_anomod,
                ve01_quantcapacidad,
                case
                    when (SELECT COUNT(*) FROM veiculos.veicretirada where ve60_veiculo = ve01_codigo) = 0 then true
                	when veiculos.veicdevolucao.ve61_veicretirada is null then false
                	else true 
                end as disponivel,
                (select ve60_codigo 
                 from veiculos.veicretirada
                 where ve60_veiculo = veiculos.ve01_codigo
                 order by ve60_codigo desc
                 limit 1) as codigo_retirada
            FROM
                veiculos
                INNER JOIN ceplocalidades ON ceplocalidades.cp05_codlocalidades = veiculos.ve01_ceplocalidades
                INNER JOIN veiccadtipo ON veiccadtipo.ve20_codigo = veiculos.ve01_veiccadtipo
                INNER JOIN veiccadmarca ON veiccadmarca.ve21_codigo = veiculos.ve01_veiccadmarca
                INNER JOIN veiccadmodelo ON veiccadmodelo.ve22_codigo = veiculos.ve01_veiccadmodelo
                INNER JOIN veiccadcor ON veiccadcor.ve23_codigo = veiculos.ve01_veiccadcor
                INNER JOIN veiccadtipocapacidade ON
                veiccadtipocapacidade.ve24_codigo = veiculos.ve01_veiccadtipocapacidade
                INNER JOIN veiccadcategcnh ON veiccadcategcnh.ve30_codigo = veiculos.ve01_veiccadcategcnh
                INNER JOIN veiccadproced ON veiccadproced.ve25_codigo = veiculos.ve01_veiccadproced
                INNER JOIN veiccadpotencia ON veiccadpotencia.ve31_codigo = veiculos.ve01_veiccadpotencia
                INNER JOIN veiccadcateg AS a ON a.ve32_codigo = veiculos.ve01_veiccadcateg
                INNER JOIN veictipoabast ON veictipoabast.ve07_sequencial = veiculos.ve01_veictipoabast
                INNER JOIN cepestados ON cepestados.cp03_sigla = ceplocalidades.cp05_sigla
                INNER JOIN veiccentral ON veiccentral.ve40_veiculos = veiculos.ve01_codigo
                INNER JOIN veiccadcentral ON veiccadcentral.ve36_sequencial = veiccentral.ve40_veiccadcentral
                LEFT  join veiculos.veicdevolucao on ve61_veicretirada = (
                        select
                            veicretirada.ve60_codigo
                        from 
                            veiculos.veicretirada
                        where 
                            veiculos.veicretirada.ve60_veiculo = veiculos.veiculos.ve01_codigo
                        order by veicretirada.ve60_codigo desc
                        limit 1
                    )
            WHERE
                ve01_ativo = '1'
                AND ve36_sequencial IN ($codCentral)
            ORDER BY
                ve01_codigo";

        return DB::select($sql);
    }

    /**
     * @throws \Exception
     */
    public function disponibilidadeVeiculo($codVeiculo)
    {
        $sql = "SELECT DISTINCT ve60_veiculo
        FROM veicretirada
        LEFT JOIN db_usuarios ON db_usuarios.id_usuario = veicretirada.ve60_usuario
        INNER JOIN db_depart ON db_depart.coddepto = veicretirada.ve60_coddepto
        INNER JOIN veiculos ON veiculos.ve01_codigo = veicretirada.ve60_veiculo
        INNER JOIN veiccentral ON veiccentral.ve40_veiculos = veiculos.ve01_codigo
        INNER JOIN veiccadcentral ON veiccadcentral.ve36_sequencial = veiccentral.ve40_veiccadcentral
        INNER JOIN veicmotoristas ON veicmotoristas.ve05_codigo = veicretirada.ve60_veicmotoristas
        INNER JOIN veiccadtipo ON veiccadtipo.ve20_codigo = veiculos.ve01_veiccadtipo
        INNER JOIN veiccadmarca ON veiccadmarca.ve21_codigo = veiculos.ve01_veiccadmarca
        INNER JOIN veiccadmodelo ON veiccadmodelo.ve22_codigo = veiculos.ve01_veiccadmodelo
        INNER JOIN veiccadcor ON veiccadcor.ve23_codigo = veiculos.ve01_veiccadcor
        INNER JOIN veiculoscomb ON veiculoscomb.ve06_veiculos = veiculos.ve01_codigo
        INNER JOIN veiccadcomb ON veiccadcomb.ve26_codigo = veiculoscomb.ve06_veiccadcomb
        INNER JOIN veiccadcategcnh ON veiccadcategcnh.ve30_codigo = veiculos.ve01_veiccadcategcnh
        INNER JOIN cgm ON cgm.z01_numcgm = veicmotoristas.ve05_numcgm
        INNER JOIN veiccadcategcnh AS a ON a.ve30_codigo = veicmotoristas.ve05_veiccadcategcnh
        LEFT JOIN veicdevolucao ON veicretirada.ve60_codigo = veicdevolucao.ve61_veicretirada
        LEFT JOIN veiccadcentraldepart ON veiccadcentraldepart.ve37_veiccadcentral = veiccadcentral.ve36_sequencial
        WHERE ve60_veiculo = {$codVeiculo} AND ve61_codigo IS null";

        $result = DB::select($sql);

        if (count($result) > 0) {
            return [
                'data' => [ 'disponivel' => false ],
                'mensagem' => 'Este veiculo não está disponível para retirada.'
            ];
        }

        return [ 'data' => [ 'disponivel' => true ], 'mensagem' => 'Este veiculo está disponível para retirada!' ];
    }
}
