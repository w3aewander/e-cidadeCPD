<?php

namespace App\Domain\Patrimonial\Ouvidoria\Services;

use App\Domain\Patrimonial\Protocolo\Model\Cgm;
use App\Domain\Patrimonial\Protocolo\Model\Mensageria;
use App\Domain\Patrimonial\Protocolo\Model\Processo\Processo;
use App\Domain\Patrimonial\Protocolo\Services\MensageriaService;
use ECidade\Patrimonial\Protocolo\Servicos\AndamentoProcessoService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use App\Domain\Patrimonial\Ouvidoria\HelperServices\MenuHelper;
use Illuminate\Support\Facades\Log;

class ProcessoEletronicoService
{

    public function formulario($tipoProcesso)
    {
        $sql = "
            SELECT
                tipoproc.p51_codigo AS id,
                tipoprocessoformulario.p108_formulario AS formulario
            FROM
                tipoproc
            INNER join  tipoprocessoformulario ON p108_tipoproc = p51_codigo
            WHERE
            p51_codigo = {$tipoProcesso}
        ";
        return DB::selectOne($sql);
    }

    public function getMensagens($parametrosRequest)
    {
        $parametros = array();
        $parametros['nome'] = $parametrosRequest['nome'];
        $parametros['cpfcnpj'] = $parametrosRequest['cpfcnpj'];
        $parametros['processo'] = $parametrosRequest['codigoProcesso'];

        $mensageriaService = new MensageriaService();
        $utlimaVisualizacao = $mensageriaService->getVisualizacao($parametros);

        $result = Mensageria::with('documentos', 'referencia')
            ->where('p123_processo', $parametros['processo'])
            ->orderBy('p123_data_criacao')
            ->get();

        return $this->coverterMensagensParaObjecto($result, $utlimaVisualizacao);
    }

    public function saveVisualizacao($p78_sequencial)
    {
        try {
            $usuario_id = db_getsession('DB_id_usuario');
            $instituicao_id = db_getsession('DB_instit');
            $departamento_id = db_getsession('DB_coddepto');

            $sql = "INSERT INTO protocolo.historicovisualizacaoprocandam (
                    p113_usuario_id
                    , p113_instituicao_id
                    , p113_departamento_id
                    , p113_procandamint_id
                    , p113_data_registro
                    )VALUES(
                  {$usuario_id}
                , {$instituicao_id}
                , {$departamento_id}
                , {$p78_sequencial}
                , NOW()
            );
           ";
            $rs = pg_query($sql);
            if ($rs == false) {
                throw new \Exception("Error ao salvar visualização! " . pg_last_error());
            }

            $sql = "SELECT
                    TO_CHAR(p113_data_registro,'dd/mm/yyyy HH24:MI:SS') AS data_visualizacao,
                    db_usuarios.login AS usuario_visualizou
                    FROM protocolo.historicovisualizacaoprocandam
                    LEFT JOIN db_usuarios ON id_usuario = p113_usuario_id
                    WHERE
                        p113_procandamint_id = {$p78_sequencial}
                   ";

            $rs = pg_query($sql);
            $data = collect(pg_fetch_object($rs));
            return [
                "success" => true,
                "message" => "Visualização salva com sucessso!",
                "data" => $data
            ];
        } catch (\Exception $ex) {
            return ["success" => false, "message" => $ex->getMessage()];
        }
    }

    public function getMenu($formas_reclamacao, $cpfCnpj = null)
    {
        $filtrarTiposDeProcessoPersona = "null";

        if (!empty($cpfCnpj)) {
            $filtrarTiposDeProcessoPersona = "
                SELECT
                   protocolo.personacgm.p121_persona
                FROM
                   protocolo.cgm
                INNER JOIN protocolo.personacgm ON
                protocolo.personacgm.p121_cgm = protocolo.cgm.z01_numcgm
                WHERE
                    protocolo.cgm.z01_cgccpf = '{$cpfCnpj}'
                GROUP BY p121_persona
            ";
        }

        $sql = "
                SELECT
                    distinct 
                    codigo AS instituicao_codigo ,
                    nomeinst AS instituicao_nome ,
                    o40_orgao AS orgao_codigo ,
                    trim(o40_descr) AS orgao_nome ,
                    '' AS categoria_descricao ,
                    '/storage/2020/4/28/1588080821.png' AS categoria_logo_url ,
                    db_depart.coddepto AS tipoprocesso_depto_id ,
                    db_depart.descrdepto AS tipoprocesso_depto_descricao ,
                    tipoproc.p51_codigo AS tipoprocesso_codigo ,
                    tipoproc.p51_descr AS tipoprocesso_descricao ,
                    tipoproc.p51_linksaibamais AS linksaibamais ,
                    tipoproc.p51_itemmenu AS item_menu ,
                    tipoproc.p51_dtlimite AS p51_dtlimite ,
                    tipoprocformareclamacao.p43_formareclamacao AS tipoprocesso_formareclamacao ,
                    tipoprocessoformulario.p108_rota AS rota,
                    tipoproc.p51_identificado AS identificado
                FROM
                    tipoproc
                INNER JOIN tipoprocformareclamacao ON
                    p43_tipoproc = p51_codigo
                INNER JOIN tipoprocdepto ON
                    p41_tipoproc = p51_codigo
                INNER JOIN db_depart ON
                    coddepto = p41_coddepto
                INNER JOIN db_departorg ON
                    db01_coddepto = coddepto
                INNER JOIN orcunidade ON
                            o41_orgao = db01_orgao
                            AND o41_unidade = db01_unidade
                            AND o41_anousu = db01_anousu
                            AND o41_instit = p51_instit
                INNER JOIN orcorgao ON
                    o40_orgao = db01_orgao
                    AND o40_anousu = db01_anousu
                INNER JOIN db_config ON
                    codigo = p51_instit
                LEFT JOIN tipoprocessoformulario ON
                    p108_tipoproc = p51_codigo
                LEFT JOIN ouvidoria.tipoprocpersona ON
                    ouvidoria.tipoprocpersona.ov34_tipoproc
                     = p51_codigo
                WHERE
                    tipoprocformareclamacao.p43_formareclamacao IN ({$formas_reclamacao})
                    AND o40_anousu::TEXT = TO_CHAR(NOW(), 'yyyy')
                AND (p51_dtlimite IS NULL
                    OR p51_dtlimite >= now())
                AND CASE WHEN p51_identificado  = TRUE THEN
                  ouvidoria.tipoprocpersona.ov34_persona  IN (
                       {$filtrarTiposDeProcessoPersona}
                ) OR ouvidoria.tipoprocpersona.ov34_sequencial  IS NULL  ELSE TRUE END

                GROUP BY
                instituicao_codigo,
                instituicao_nome,
                orgao_codigo,
                orgao_nome,
                tipoprocesso_codigo,
                categoria_logo_url,
                tipoprocesso_depto_id,
                tipoprocesso_depto_descricao,
                tipoprocesso_formareclamacao,
                linksaibamais,
                item_menu,
                rota,
                p51_dtlimite,
                p51_identificado
                ORDER BY
                categoria_descricao ,
                tipoprocesso_descricao;
        ";

        $result = DB::select($sql);

        if ($formas_reclamacao == 9) {
            return MenuHelper::coverterMenuPrimeiroAcesso($result);
        }
        return MenuHelper::converterMenusParaObjeto($result);
    }

    private function coverterMensagensParaObjecto($mensagesResult, $utlimaVisualizacao)
    {
        $resultados = collect();

        foreach ($mensagesResult as $mensagem) {
            $anexos = collect();
            if (isset($mensagem->documentos) && (count($mensagem->documentos) > 0)) {
                foreach ($mensagem->documentos as $documento) {
                    $anexo = (object)[
                        'id_estorage' => $documento->p124_codigo_storage,
                        'descricao' => 'Documento da mensagem enviada pela Ouvidoria.',
                        'content' => null,
                        'type' => null
                    ];
                    $anexos->push($anexo);
                }
            }

            $resultado = (object)[
                    'codigo' => $mensagem->p123_id,
                    'data' => (new \DateTime($mensagem->p123_data_criacao))->format('d/m/Y'),
                    'hora' => (new \DateTime($mensagem->p123_data_criacao))->format('H:i'),
                    'mensagem' => $mensagem->p123_mensagem,
                    'anexos' => $anexos,
                    'interna' => $mensagem->p123_interna,
                    'mensagem_referenciada' => [
                        'codigo' =>  isset($mensagem->referencia->p123_id) ? $mensagem->referencia->p123_id : null,
                        'mensagem' =>  isset($mensagem->referencia->p123_mensagem) ?
                            $mensagem->referencia->p123_mensagem: null,
                    ],
                    'ultima_visualizacao' => $utlimaVisualizacao->p125_nome,
            ];
            $resultados->push($resultado);
        }
        return $resultados->values();
    }

    public function getAtendimentoPorId($id)
    {
        $sql = "SELECT
        ouvidoriaatendimento.ov01_dataatend AS data_atendimento,
        ouvidoriaatendimento.ov01_horaatend AS hora_atendimento,
        ouvidoriaatendimento.ov01_numero AS numero_atendimento,
        ouvidoriaatendimento.ov01_anousu AS ano_atendimento,
        cidadao.ov02_nome AS nome_cidadao,
        cidadao.ov02_cnpjcpf AS cnpjcpf,
        ouvidoriaatendimento.ov01_sequencial AS codigo_atendimento,
        ouvidoriaatendimento.ov01_requerente AS nome_requerente,
        protprocesso.p58_numero AS numero_processo,
        protprocesso.p58_ano AS ano_processo,
        protprocesso.p58_codproc AS codigo_processo,
        ouvidoriaatendimento.ov01_tipoprocesso AS tipo_processo,
        ouvidoriaatendimento.ov01_depart AS departamento,
        tipoproc.p51_descr AS nome_tipo_processo,
        (
            SELECT
               p69_arquivado
            FROM
                protocolo.arqproc
            LEFT JOIN protocolo.procarquiv ON
            p68_codarquiv = p67_codarquiv
            LEFT JOIN protocolo.arqandam ON
            p69_codarquiv = p67_codarquiv
            WHERE p67_codproc  = p58_codproc
            GROUP BY
            p67_codproc,
            p69_arquivado
            ORDER BY MAX(p67_codarquiv) DESC
                    LIMIT 1
        )  AS arquivado,
         p58_obs ilike 'Recadastramento acesso arquivado pelo sistema' as recadastramento_aprovado
        FROM
         ouvidoriaatendimento
        INNER JOIN protocolo.tipoproc
                ON protocolo.tipoproc.p51_codigo  =  ouvidoria.ouvidoriaatendimento.ov01_tipoprocesso
        LEFT JOIN ouvidoriaatendimentocidadao
               ON ouvidoriaatendimentocidadao.ov10_ouvidoriaatendimento  = ouvidoriaatendimento.ov01_sequencial
        LEFT JOIN cidadao
               ON cidadao.ov02_sequencial = ouvidoriaatendimentocidadao.ov10_cidadao
              AND ouvidoriaatendimentocidadao.ov10_seq = cidadao.ov02_seq
        LEFT JOIN processoouvidoria
               ON processoouvidoria.ov09_ouvidoriaatendimento = ouvidoriaatendimento.ov01_sequencial
        LEFT JOIN protprocesso ON processoouvidoria.ov09_protprocesso = protprocesso.p58_codproc
        LEFT JOIN ouvidoriaatendimentoprocessoeletronico
        ON ouvidoriaatendimentoprocessoeletronico.ov33_ouvidoriaatendimento = ouvidoriaatendimento.ov01_sequencial
        WHERE
             ov01_sequencial IN ({$id})
        ORDER BY ouvidoriaatendimento.ov01_dataatend DESC,ouvidoriaatendimento.ov01_horaatend DESC;";

        return DB::selectOne($sql);
    }

    public function getAtendimentoPorCpfCnpj($cpfCnpj, $perPage, $filtro)
    {
        $sql = "SELECT
        TO_CHAR(ouvidoriaatendimento.ov01_dataatend, 'DD/MM/YYYY') AS data_atendimento,
        ouvidoriaatendimento.ov01_horaatend AS hora_atendimento,
        ouvidoriaatendimento.ov01_numero AS numero_atendimento,
        ouvidoriaatendimento.ov01_anousu AS ano_atendimento,
        cidadao.ov02_nome AS nome_cidadao,
        cidadao.ov02_cnpjcpf AS cnpjcpf,
        ouvidoriaatendimento.ov01_sequencial AS codigo_atendimento,
        ouvidoriaatendimento.ov01_requerente AS nome_requerente,
        protprocesso.p58_numero AS numero_processo,
        protprocesso.p58_ano AS ano_processo,
        protprocesso.p58_codproc AS sequencial_processo,
        protprocesso.p58_codproc_crypt as codigo_processo,
        ouvidoriaatendimento.ov01_tipoprocesso AS tipo_processo,
        ouvidoriaatendimento.ov01_depart AS departamento,
        ouvidoriaatendimento.ov01_status AS atendimento_status_codigo,
        ouvidoriaatendimento_status.ov35_descricao AS status,
        tipoproc.p51_descr AS nome_tipo_processo,
        (
            SELECT
               p69_arquivado
            FROM
                protocolo.arqproc
            LEFT JOIN protocolo.procarquiv ON
            p68_codarquiv = p67_codarquiv
            LEFT JOIN protocolo.arqandam ON
            p69_codarquiv = p67_codarquiv
            WHERE p67_codproc  = p58_codproc
            GROUP BY
            p67_codproc,
            p69_arquivado
            ORDER BY MAX(p67_codarquiv) DESC
                    LIMIT 1
        )  AS arquivado
        FROM
         ouvidoriaatendimento
        INNER JOIN ouvidoriaatendimento_status
                ON  ouvidoriaatendimento_status.ov35_id = ouvidoriaatendimento.ov01_status
        INNER JOIN protocolo.tipoproc
                ON protocolo.tipoproc.p51_codigo  =  ouvidoriaatendimento.ov01_tipoprocesso
        LEFT JOIN ouvidoriaatendimentocidadao
               ON ouvidoriaatendimentocidadao.ov10_ouvidoriaatendimento  = ouvidoriaatendimento.ov01_sequencial
        LEFT JOIN cidadao
               ON cidadao.ov02_sequencial = ouvidoriaatendimentocidadao.ov10_cidadao
              AND ouvidoriaatendimentocidadao.ov10_seq = cidadao.ov02_seq
        LEFT JOIN processoouvidoria
               ON processoouvidoria.ov09_ouvidoriaatendimento = ouvidoriaatendimento.ov01_sequencial
        LEFT JOIN protprocesso ON processoouvidoria.ov09_protprocesso = protprocesso.p58_codproc
        LEFT JOIN ouvidoriaatendimentoprocessoeletronico
        ON ouvidoriaatendimentoprocessoeletronico.ov33_ouvidoriaatendimento = ouvidoriaatendimento.ov01_sequencial
        WHERE
             ov02_cnpjcpf  = '{$cpfCnpj}'";

        $bindings = [];

        if ($filtro !== 'semfiltro') {
            $filtro = '%' . $filtro . '%';
            $sql .= " AND (
                    tipoproc.p51_descr ILIKE ? OR
                    CONCAT(ouvidoriaatendimento.ov01_numero, '/', ouvidoriaatendimento.ov01_anousu) ILIKE ? OR
                    CONCAT(protprocesso.p58_numero, '/', protprocesso.p58_ano) ILIKE ? OR
                    ouvidoriaatendimento_status.ov35_descricao ILIKE ? OR
                    TO_CHAR(ouvidoriaatendimento.ov01_dataatend, 'DD/MM/YYYY') ILIKE ? OR
                    ouvidoriaatendimento.ov01_horaatend ILIKE ?
                )";
            $bindings = [$filtro, $filtro, $filtro, $filtro, $filtro, $filtro];
        }

        $sql .= " ORDER BY ouvidoriaatendimento.ov01_dataatend DESC";

        $results = DB::select($sql, $bindings);

        if (empty($perPage)) {
            $perPage = 5;
        }

        $page = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            array_slice(
                $results,
                ($page - 1) * $perPage,
                $perPage
            ),
            count($results),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );
    }

    public function getDetalheProcesso($codigoProcesso)
    {
        $sql = "
        SELECT
            codigo AS   codigo_andamento,
            data_despacho AS    data_andamento,
            hora_despacho AS      hora_andamento,
            concat(despacho,' ',motivo) AS  despacho,
            id_estorage AS id_estorage,
            descricao AS     descricao
        FROM
           (
                    SELECT
                    p61_codandam AS codigo,
                    p61_despacho AS despacho,
                    p61_dtandam AS data_despacho,
                    p61_hora AS hora_despacho,
                    NULL AS id_estorage,
                    NULL AS descricao,
                    (
                        SELECT
                           p67_historico
                        FROM
                           protocolo.arqproc
                        LEFT JOIN protocolo.procarquiv on
                        p68_codarquiv = p67_codarquiv
                        LEFT JOIN protocolo.arqandam on
                        p69_codarquiv = p67_codarquiv
                        WHERE
                            p67_codproc  = {$codigoProcesso}
                        GROUP BY
                            p67_codproc,
                            p67_historico
                        ORDER BY MAX(p67_codarquiv) DESC
                        LIMIT 1
                    )  as motivo,
                    'PADRAO' AS tipo
                FROM
                    procandam
                WHERE
                    p61_codproc = {$codigoProcesso}
                    AND p61_publico IS TRUE
                UNION
                SELECT
                    DISTINCT p78_sequencial AS codigo,
                    p78_despacho AS despacho,
                    p78_data AS data_despacho,
                    p78_hora AS hora_despacho,
                    p01_documento AS id_estorage,
                    p01_descricao AS descricao,
                    '' as motivo,
                    'INTERNO' AS tipo
                FROM
                    procandamint
                LEFT JOIN procandamintand ON
                    p86_codandam = p78_codandam
                LEFT JOIN proctransferintand ON
                    p87_codandam = p78_codandam
                LEFT JOIN protprocessodocumento ON
                    p01_procandamint = p78_sequencial
                    AND p01_estorage IS TRUE
                WHERE
                    p78_codandam IN (
                    SELECT
                        p61_codandam
                    FROM
                        procandam
                    WHERE
                        p61_codproc = {$codigoProcesso}
                    )
                    AND p78_publico IS TRUE
                    AND p86_codandam IS NULL
                    AND p87_codandam IS NULL
           ) AS processo
           ORDER BY data_despacho DESC,hora_despacho DESC,codigo DESC
        ";

        $result = DB::select($sql);
        return $this->converterDetalheProcessoParaEstruturaDeDados($result);
    }

    public function converterDetalheProcessoParaEstruturaDeDados($andamentos)
    {

        $andamentos = collect($andamentos)->groupBy(function ($andamento) {
            return $andamento->codigo_andamento;
        })->toArray();

        return collect($andamentos)->flatMap(function ($andamento, $index) {

            $anexos = array_map(function ($item) {

                return (object)[
                    'id_estorage' => !empty($item->id_estorage) ? $item->id_estorage : null,
                    'descricao' => !empty($item->descricao) ? $item->descricao : null,
                    'content' => null,
                    'type' => null
                ];
            }, $andamento);

            $andamento = current($andamento);

            if (empty($andamento->id_estorage)) {
                $anexos = [];
            }

            return [
                $index => (object)[
                    'codigo' => $andamento->codigo_andamento,
                    'data' => (new \DateTime($andamento->data_andamento))->format('d/m/Y'),
                    'hora' => $andamento->hora_andamento,
                    'despacho' => $andamento->despacho,
                    'anexos' => $anexos,
                    'flagMensagem' => false
                ]
            ];
        })->values();
    }

    /**
     * @throws \Exception
     */
    public function mensagemOuvidoria(
        $codigoProcesso,
        $codigoAndamento,
        $mensagem,
        $respostaOuvidoria = false,
        $anexos = []
    ) {

        $parametros = new \stdClass();
        $parametros->codigoProcesso = (int)$codigoProcesso;
        $parametros->codigoAndamento = (int)$codigoAndamento;
        $parametros->mensagem = $mensagem;
        $parametros->respostaOuvidoria = !($respostaOuvidoria == 0);
        $processo = Processo::find($codigoProcesso);
        if (empty($processo->p58_coddepto)) {
            throw new \Exception("Processo não encontrado!");
        }
        pg_exec("select fc_putsession('DB_coddepto','{$processo->p58_coddepto}')");
        $parametros->acao = "mensagemCidadao";
        if ($parametros->respostaOuvidoria) {
            $parametros->acao = "respostaCidadao";
        }
        if (!empty($anexos)) {
            $parametros->anexos = array();
            foreach ($anexos as $anexo) {
                self::validaAnexoBase64eAdicionaCaminhoTemporario($anexo);
                $parametros->anexos[] = $anexo;
            }
        }
        $service = new AndamentoProcessoService($parametros);
        $service->salvarMensagemOuvidoria();
    }

    /**
     * @throws \Exception
     */
    public static function validaAnexoBase64eAdicionaCaminhoTemporario(&$anexo)
    {
        if (empty($anexo["nome"])) {
            throw new \Exception("Nome do arquivo não informado!");
        }

        if (empty($anexo["conteudo"])) {
            throw new \Exception("Conteudo do arquivo {$anexo["nome"]} não informado!");
        }

        if (empty($anexo["type"])) {
            throw new \Exception("Type do arquivo {$anexo["nome"]} não informado!");
        }

        $conteudo = explode("base64,", $anexo["conteudo"]);
        $file = base64_decode($conteudo[count($conteudo) - 1]);

        $typesPartes = explode("/", $anexo["type"]);
        $PATH_TEMP_FILE = 'tmp/ouvidoriaatendimento' . date('dmY') . time() . ".";
        $caminhoarquivo = $PATH_TEMP_FILE . $typesPartes[count($typesPartes) - 1];
        file_put_contents($caminhoarquivo, $file);
        if (!is_readable($caminhoarquivo)) {
            throw new \Exception("Arquivo {$anexo["nome"]} mal formado!");
        }
        $anexo["caminho"] = $caminhoarquivo;
    }

    public function personas($cpfCnpj)
    {
        $cgms = Cgm::cpfCnpj($cpfCnpj)->get();
        if (!$cgms) {
            throw new \Exception("CPF/CNPJ não possuí CGM!");
        }

        $cgms = implode(",", $cgms->pluck(['z01_numcgm'])->toArray());

        $sql = "SELECT
                DISTINCT
                protocolo.persona.p120_sequencial AS codigo,
                protocolo.persona.p120_descricao AS persona,
                protocolo.persona.p120_objetivo AS descricao
                FROM protocolo.personacgm
                INNER JOIN protocolo.persona  ON protocolo.persona.p120_sequencial  = protocolo.personacgm.p121_persona
                WHERE
                protocolo.personacgm.p121_cgm IN ({$cgms}); ";
        return DB::select($sql);
    }
}
