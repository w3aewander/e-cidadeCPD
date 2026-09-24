<?php

namespace App\Domain\Patrimonial\Veiculos\Services;

use App\Domain\Configuracao\Usuario\Models\UsuarioCgm;
use App\Domain\Core\Base\Http\Response\DBJsonResponse;
use App\Domain\Patrimonial\Veiculos\Models\Motorista;
use App\Domain\Patrimonial\Veiculos\Models\VeicRetirada;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VeicRetiradaService
{
    /**
     * @throws \Exception
     */
    public function inserirRetirada($params)
    {
        if ($params['ultima_medida'] > $params['medida_saida']) {
            throw new \Exception('Medida de saida menor que a ultima medida registrada!');
        }

        $motorista = self::verificaCgmMotorista($params['cgm']);
        $proximoCodigo = DB::select("select nextval('veicretirada_ve60_codigo_seq')");

        if (empty($proximoCodigo)) {
            throw new \Exception('Não foi possível achar o proximo codigo!');
        }

        $retirada = new VeicRetirada();

        $retirada->ve60_codigo = $proximoCodigo[0]->nextval;
        $retirada->ve60_usuario = null;
        $retirada->ve60_cpfcnpj = $params['cpfcnpj'];
        $retirada->ve60_veiculo = $params['cod_veiculo'];
        $retirada->ve60_veicmotoristas = $motorista->ve05_codigo;
        $retirada->ve60_datasaida = $params['data_saida'];
        $retirada->ve60_horasaida = $params['hora_saida'];
        $retirada->ve60_destino = $params['destino'];
        $retirada->ve60_coddepto = $params['cod_departamento'];
        $retirada->ve60_medidasaida = $params['medida_saida'];
        $retirada->ve60_passageiro = $params['passageiro'];
        $retirada->ve60_data = Carbon::now()->toDateString();
        $retirada->ve60_hora = Carbon::now()->format('H:i');

        if ($retirada->save()) {
            return new DBJsonResponse($retirada, "Retirada realizada com sucesso!");
        }

        return new DBJsonResponse("Houve um problema ao salvar o retirada!", 500);
    }

    /**
     * @throws \Exception
     */
    public static function verificaCgmMotorista($cgm)
    {
        $motorista = Motorista::where('ve05_numcgm', $cgm)->get();

        if (count($motorista) == 0) {
            throw new \Exception('Nenhum motorista encontrado para este CGM');
        }

        if (count($motorista) > 1) {
            throw new \Exception('Mais de um motorista associado ao CGM');
        }

        return Motorista::where('ve05_numcgm', $cgm)
            ->first();
    }

    public function ultimaMedidaVeiculo($codVeiculo)
    {
        $sql = "SELECT *
            FROM (
                -- Último abastecimento
                (
                    SELECT
                        ve70_dtabast AS data,
                        ve70_hora AS hora,
                        ve70_medida AS ultimamedida,
                        'ABASTECIMENTO' AS tipo,
                        ve07_sigla as sigla_medida
                    FROM
                        veicabast
                    inner join veiculos on veiculos.ve01_codigo = veicabast.ve70_veiculos
                    inner join veictipoabast on veictipoabast.ve07_sequencial = veiculos.ve01_veictipoabast
                    WHERE
                        ve70_veiculos = {$codVeiculo}

                        AND NOT EXISTS (
                            SELECT 1
                            FROM veicabastanu
                            WHERE ve74_veicabast = ve70_codigo
                        )
                    ORDER BY
                        ve70_dtabast DESC,
                        ve70_hora DESC,
                        ve70_codigo DESC
                    LIMIT 1
                )

                UNION ALL

                -- Última manutenção
                (
                    SELECT
                        ve62_dtmanut AS data,
                        ve62_hora AS hora,
                        ve62_medida AS ultimamedida,
                        'MANUTENCAO' AS tipo,
                        ve07_sigla as sigla_medida
                    FROM
                        veicmanut
                    inner join veiculos on veiculos.ve01_codigo = veicmanut.ve62_veiculos
                    inner join veictipoabast on veictipoabast.ve07_sequencial = veiculos.ve01_veictipoabast
                    WHERE
                        ve62_veiculos = {$codVeiculo}

                    ORDER BY
                        ve62_dtmanut DESC,
                        ve62_hora DESC,
                        ve62_codigo DESC
                    LIMIT 1
                )

                UNION ALL

                -- Última devolução
                (
                    SELECT
                        ve61_datadevol AS data,
                        ve61_horadevol AS hora,
                        ve61_medidadevol AS ultimamedida,
                        'DEVOLUCAO' AS tipo,
                        ve07_sigla as sigla_medida
                    FROM
                        veicdevolucao
                        INNER JOIN veicretirada ON ve60_codigo = ve61_veicretirada
                        inner join veiculos on veiculos.ve01_codigo = veicretirada.ve60_veiculo
        	            inner join veictipoabast on veictipoabast.ve07_sequencial = veiculos.ve01_veictipoabast
                    WHERE
                        ve60_veiculo = {$codVeiculo}

                    ORDER BY
                        ve61_datadevol DESC,
                        ve61_horadevol DESC,
                        ve61_codigo DESC
                    LIMIT 1
                )

                UNION ALL

                -- Última retirada
                (
                    SELECT
                        ve60_datasaida AS data,
                        ve60_horasaida AS hora,
                        ve60_medidasaida AS ultimamedida,
                        'RETIRADA' AS tipo,
                        ve07_sigla as sigla_medida
                    FROM
                        veicretirada
                    inner join veiculos on veiculos.ve01_codigo = veicretirada.ve60_veiculo
                    inner join veictipoabast on veictipoabast.ve07_sequencial = veiculos.ve01_veictipoabast
                    WHERE
                        ve60_veiculo = {$codVeiculo}

                    ORDER BY
                        ve60_datasaida DESC,
                        ve60_horasaida DESC,
                        ve60_codigo DESC
                    LIMIT 1
                )
            ) AS registros_veiculo
            ORDER BY
                data DESC,
                hora DESC,
                ultimamedida DESC LIMIT 1";

        return DB::Select($sql);
    }
}
