<?php

namespace App\Domain\Patrimonial\Veiculos\Services;

use App\Domain\Core\Base\Http\Response\DBJsonResponse;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;

class VeicDevolucaoService
{
    /**
     * @throws \Exception
     */
    public function veiculosParaDevolucao($cgm)
    {
        $motorista = VeicRetiradaService::verificaCgmMotorista($cgm);

        $sql = "SELECT DISTINCT
            veicretirada.ve60_codigo as codigo_retirada,
            veicretirada.ve60_veiculo as codigo_veiculo,
            veicretirada.ve60_veicmotoristas as codigo_motorista,
            veicretirada.ve60_datasaida as data_saida,
            veicretirada.ve60_horasaida as hora_saida,
            veicretirada.ve60_medidasaida as medida_saida,
            veicretirada.ve60_destino as destino,
            veicretirada.ve60_coddepto as departamento_retirada,
            veicretirada.ve60_usuario as usuario_ecidade_retirada,
            veicretirada.ve60_data as data,
            veicretirada.ve60_hora as hora,
            veiculos.ve01_placa as placa_veiculo,
            veiculos.ve01_anomod as ano_modelo_veiculo,
            veictipoabast.ve07_sigla as sigla_medida,
            veiccadmarca.ve21_descr as marca_veiculo,
            veiccadmodelo.ve22_descr as modelo_veiculo
        FROM
            veicretirada
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
        INNER JOIN veictipoabast ON veictipoabast.ve07_sequencial = veiculos.ve01_veictipoabast
        LEFT JOIN veicdevolucao ON veicretirada.ve60_codigo = veicdevolucao.ve61_veicretirada
        LEFT JOIN veiccadcentraldepart ON veiccadcentraldepart.ve37_veiccadcentral = veiccadcentral.ve36_sequencial
        WHERE
            ve61_codigo IS NULL

            AND veicretirada.ve60_veicmotoristas = {$motorista->ve05_codigo}
        ORDER BY
            codigo_retirada";

        return DB::Select($sql);
    }

    /**
     * @throws \Exception
     */
    public function verificaRetiradaDevolucao($codigoRetirada)
    {
        $sql = "SELECT DISTINCT
                ve61_veicretirada,
                ve60_codigo,
                ve60_veiculo,
                ve01_placa,
                ve60_datasaida,
                ve60_horasaida,
                ve60_medidasaida,
                ve60_veicmotoristas,
                ve61_veicmotoristas,
                z01_nome,
                ve07_sigla as sigla_medida
            FROM
                veicretirada
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
            INNER JOIN veictipoabast ON veictipoabast.ve07_sequencial = ve01_veictipoabast
            WHERE
                ve60_codigo = {$codigoRetirada}
                AND ve61_codigo IS null";

        $result = DB::Select($sql);

        if (count($result) > 0) {
            return $result;
        }

        throw new \Exception('Este veiculo ja foi devolvido');
    }

    /**
     * @throws \Exception
     */
    public function devolucaoVeiculo($params)
    {
        $dataHoraDevolucao = "{$params['data_devolucao']} {$params['hora_devolucao']}";
        $dataHoraSaida = "{$params['data_saida']} {$params['hora_saida']}";

        if ($dataHoraDevolucao !== "" and $dataHoraSaida !== "") {
            $dataHoraDevolucao = DateTime::createFromFormat(
                'Y-m-d H:i',
                $dataHoraDevolucao
            );

            $dataHoraSaida = DateTime::createFromFormat(
                'Y-m-d H:i',
                $dataHoraSaida
            );

            if ($dataHoraDevolucao < $dataHoraSaida) {
                throw new \Exception('Data e Hora de Devolução devem ser maiores ou iguais a Data e Hora de Retirada!');
            }
        } else {
            throw new \Exception('Não foi possível validar data e hora de devolução/saida!');
        }

        if ($params['medida_devolucao'] and $params['medida_saida']) {
            if ((int) $params['medida_devolucao'] < (int) $params['medida_saida']) {
                throw new \Exception('Medida na devolução deve ser maior ou igual que a medida na retirada!');
            }
        }

        /*
        * Verificamos se existem abastecimentos registrados para esse veículo,
        * caso existam, verifica se a devolução é maior ou igual que a data e a hora do último abastecimento.
        */

        $this->verificaAbastecimentos(
            $params['cod_veiculo'],
            $params['cod_retirada'],
            $dataHoraDevolucao
        );

        /*
        * Verificamos se ja foi realizada a devolução para esta retirada.
        */
        $this->verificaDevolucaoParaRetirada($params['cod_retirada']);

        //inserir no banco a devolucao
        $proximoCodigo = DB::select("select nextval('veicdevolucao_ve61_codigo_seq')");

        if (empty($proximoCodigo)) {
            throw new \Exception('Não foi possível achar o proximo codigo!');
        }

        $motorista = VeicRetiradaService::verificaCgmMotorista($params['cgm']);

        $devolucao = new \App\Domain\Patrimonial\Veiculos\Models\VeicDevolucao();
        $devolucao->ve61_codigo = $proximoCodigo[0]->nextval;
        $devolucao->ve61_cpfcnpj = $params['cpfcnpj'];
        $devolucao->ve61_veicretirada = $params['cod_retirada'];
        $devolucao->ve61_veicmotoristas = $motorista->ve05_codigo;
        $devolucao->ve61_datadevol = $params['data_devolucao'];
        $devolucao->ve61_horadevol = $params['hora_devolucao'];
        $devolucao->ve61_usuario = null;
        $devolucao->ve61_data = Carbon::now()->toDateString();
        $devolucao->ve61_hora = Carbon::now()->format('H:i');
        $devolucao->ve61_medidadevol = $params['medida_devolucao'];

        if ($devolucao->save()) {
            return new DBJsonResponse($devolucao, "Devolução realizada com sucesso!");
        }

        return new DBJsonResponse("Houve um problema ao salvar a devolução!", 500);
    }

    /**
     * @throws \Exception
     */
    public function verificaAbastecimentos($codigoVeiculo, $codRetirada, $dataHoraDevolucao)
    {
        $sql = "SELECT
                    ve70_dtabast,
                    ve70_hora
                FROM
                    veicabast
                INNER JOIN db_usuarios ON db_usuarios.id_usuario = veicabast.ve70_usuario
                INNER JOIN veiculoscomb ON veiculoscomb.ve06_veiculos = veicabast.ve70_veiculos
                INNER JOIN veiccadcomb ON veiccadcomb.ve26_codigo = veiculoscomb.ve06_veiccadcomb
                INNER JOIN veiculos ON veiculos.ve01_codigo = veicabast.ve70_veiculos
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
                INNER JOIN veiccadcateg ON veiccadcateg.ve32_codigo = veiculos.ve01_veiccadcateg
                LEFT JOIN veicabastretirada ON veicabastretirada.ve73_veicabast = veicabast.ve70_codigo
                LEFT JOIN veicabastanu ON veicabastanu.ve74_veicabast = veicabast.ve70_codigo
                WHERE
                    ve70_veiculos = {$codigoVeiculo}
                    AND ve73_veicretirada = {$codRetirada}
                ORDER BY
                    ve70_dtabast DESC,
                    ve70_hora DESC";

        $result = DB::Select($sql);

        if (count($result) > 0) {
            $dataHoraUltimoAbastecimento = "{$result[0]->ve70_dtabast} {$result[0]->ve70_hora}";
            $dataHoraUltimoAbastecimento = DateTime::createFromFormat(
                'Y-m-d H:i',
                $dataHoraUltimoAbastecimento
            );

            if ($dataHoraDevolucao  < $dataHoraUltimoAbastecimento) {
                throw new \Exception(
                    'Data e Hora da Devolução não podem ser menores que Data e Hora do Abastecimento.'
                );
            }
        }
    }

    public function verificaDevolucaoParaRetirada($codRetirada)
    {
        $sql = "select ve61_codigo from veicdevolucao  where ve61_veicretirada = {$codRetirada}";
        $result = DB::select($sql);

        if (count($result) > 0) {
            throw new \Exception("Encontrada devolução {$result[0]->ve61_codigo} cadastrada para esta retirada!");
        }
    }
}
