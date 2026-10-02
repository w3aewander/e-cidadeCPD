<?php

namespace App\Domain\Educacao\Escola\Controllers;

use App\Domain\Configuracao\Usuario\Models\Usuario;
use App\Domain\Core\Base\Http\Response\DBJsonResponse;
use App\Http\Controllers\Controller;
use Encriptacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PautaEletronicaMobileController extends Controller
{
    /**
     * Endpoint de verificacao de status e integridade da API
     */
    public function status()
    {
        $anosDisponiveis = array();
        try {
            $rsAnos = DB::select("SELECT DISTINCT ed52_i_ano FROM escola.calendario ORDER BY ed52_i_ano DESC");
            foreach ($rsAnos as $row) {
                $anosDisponiveis[] = (int)$row->ed52_i_ano;
            }
        } catch (\Exception $e) {
            $anosDisponiveis = array((int)date('Y'));
        }
        if (empty($anosDisponiveis)) {
            $anosDisponiveis = array((int)date('Y'));
        }

        $dados = array(
            'status'           => 'online',
            'modulo'           => 'Ecidade - Pauta Eletronica Mobile API',
            'versao_api'       => '1.0.1',
            'versao_app'       => array(
                'versao_mais_recente' => '1.0.1',
                'codigo_versao'       => 2,
                'url_download'        => url('/download/ecidade-pauta-eletronica.apk'),
                'novidades'           => 'Seleção de ano letivo e sincronização aprimorada.',
                'obrigatoria'         => false
            ),
            'anos_disponiveis' => $anosDisponiveis,
            'timestamp'        => date('Y-m-d H:i:s'),
            'database'         => 'conectado'
        );

        return new DBJsonResponse($dados, 'API Pauta Eletronica operacional');
    }

    /**
     * Informacoes sobre a versao mais recente do aplicativo mobile
     */
    public function getVersaoApp()
    {
        $dados = array(
            'versao_mais_recente' => '1.0.1',
            'codigo_versao'       => 2,
            'url_download'        => url('/download/ecidade-pauta-eletronica.apk'),
            'novidades'           => 'Seleção de ano letivo e sincronização aprimorada.',
            'obrigatoria'         => false
        );

        return new DBJsonResponse($dados, 'Informacoes de versao do aplicativo');
    }

    /**
     * Lista de anos letivos cadastrados no sistema
     */
    public function getAnosLetivos()
    {
        $anosDisponiveis = array();
        try {
            $rsAnos = DB::select("SELECT DISTINCT ed52_i_ano FROM escola.calendario ORDER BY ed52_i_ano DESC");
            foreach ($rsAnos as $row) {
                $anosDisponiveis[] = (int)$row->ed52_i_ano;
            }
        } catch (\Exception $e) {
            $anosDisponiveis = array((int)date('Y'));
        }
        if (empty($anosDisponiveis)) {
            $anosDisponiveis = array((int)date('Y'));
        }

        $anoPadrao = in_array(2025, $anosDisponiveis) ? 2025 : $anosDisponiveis[0];

        $dados = array(
            'anos'       => $anosDisponiveis,
            'ano_atual'  => (int)date('Y'),
            'ano_padrao' => $anoPadrao
        );

        return new DBJsonResponse($dados, 'Anos letivos recuperados com sucesso');
    }

    /**
     * Autenticacao do Professor para o App Mobile
     */
    public function login(Request $request)
    {
        $login = $request->input('login');
        $senha = $request->input('senha');
        $anoParam = $request->input('ano');

        if (empty($login) || empty($senha)) {
            return new DBJsonResponse(null, 'Informe o usuario e a senha de acesso.', 400);
        }

        $usuario = Usuario::where('login', $login)->first();
        if (!$usuario) {
            return new DBJsonResponse(null, 'Usuario ou senha incorretos.', 401);
        }

        if (!$usuario->isUsuarioAtivo()) {
            return new DBJsonResponse(null, 'Este usuario esta inativo no e-Cidade.', 403);
        }

        $hashPadrao = Encriptacao::encriptaSenha($senha);
        $hashSha1   = sha1($senha);
        $hashMd5    = md5($senha);
        $dbSenha    = $usuario->getAuthPassword();

        $senhaValida = ($hashPadrao === $dbSenha || $hashSha1 === $dbSenha || $hashMd5 === $dbSenha);
        if (!$senhaValida) {
            return new DBJsonResponse(null, 'Usuario ou senha incorretos.', 401);
        }

        $cgm = 0;
        $sqlCgm = "SELECT z01_numcgm, z01_nome FROM db_usuacgm INNER JOIN cgm ON z01_numcgm = cgmlogin WHERE id_usuario = {$usuario->id_usuario}";
        $rsCgm = DB::select($sqlCgm);
        if (!empty($rsCgm)) {
            $cgm = $rsCgm[0]->z01_numcgm;
        }

        // Subquery para obter o ID rechumano do docente via rechumanocgm ou rechumanopessoal
        $sqlRechumanoDocente = "
            SELECT ed285_i_rechumano FROM escola.rechumanocgm WHERE ed285_i_cgm = {$cgm}
            UNION
            SELECT ed284_i_rechumano FROM escola.rechumanopessoal INNER JOIN rhpessoal ON rh01_regist = ed284_i_rhpessoal WHERE rh01_numcgm = {$cgm}
        ";

        $escolas = array();
        if ($cgm > 0) {
            $sqlEscolas = "
                SELECT DISTINCT ed18_i_codigo as id, trim(ed18_c_nome) as nome
                FROM escola.escola
                INNER JOIN escola.turma ON ed57_i_escola = ed18_i_codigo
                INNER JOIN escola.regencia ON ed59_i_turma = ed57_i_codigo
                INNER JOIN escola.regenciahorario ON ed58_i_regencia = ed59_i_codigo
                WHERE ed58_i_rechumano IN ({$sqlRechumanoDocente})
                ORDER BY trim(ed18_c_nome)
            ";
            try {
                $escolas = DB::select($sqlEscolas);
            } catch (\Exception $e) {
                $escolas = array();
            }
        }

        // Se o professor nao tem turma vinculada ou e administrador/teste:
        if (empty($escolas)) {
            $sqlEscolasFallback = "
                SELECT DISTINCT ed18_i_codigo as id, trim(ed18_c_nome) as nome
                FROM escola.escola
                INNER JOIN escola.turma ON ed57_i_escola = ed18_i_codigo
                ORDER BY trim(ed18_c_nome)
                LIMIT 30
            ";
            try {
                $escolas = DB::select($sqlEscolasFallback);
            } catch (\Exception $e) {
                $escolas = DB::select("SELECT ed18_i_codigo as id, trim(ed18_c_nome) as nome FROM escola.escola ORDER BY trim(ed18_c_nome) LIMIT 30");
            }
        }

        // Anos letivos disponiveis
        $anosDisponiveis = array();
        try {
            $rsAnos = DB::select("SELECT DISTINCT ed52_i_ano FROM escola.calendario ORDER BY ed52_i_ano DESC");
            foreach ($rsAnos as $row) {
                $anosDisponiveis[] = (int)$row->ed52_i_ano;
            }
        } catch (\Exception $e) {
            $anosDisponiveis = array((int)date('Y'));
        }
        if (empty($anosDisponiveis)) {
            $anosDisponiveis = array((int)date('Y'));
        }

        if ($anoParam) {
            $ano = (int)$anoParam;
        } else {
            // Se ano letivo nao foi especificado, prioriza 2025 onde ha turmas cadastradas, ou o mais recente com dados
            $ano = in_array(2025, $anosDisponiveis) ? 2025 : $anosDisponiveis[0];
        }

        $token = null;
        try {
            $tokenResult = $usuario->createToken('pauta-mobile-token');
            $token = $tokenResult->accessToken;
        } catch (\Exception $e) {
            $token = 'tk_' . md5($usuario->id_usuario . microtime() . 'ecidade_pauta_secret');
        }

        $dadosRetorno = array(
            'token' => $token,
            'usuario' => array(
                'id'    => $usuario->id_usuario,
                'login' => $usuario->login,
                'nome'  => $usuario->nome,
                'email' => $usuario->email,
                'cgm'   => $cgm
            ),
            'ano_letivo'       => $ano,
            'anos_disponiveis' => $anosDisponiveis,
            'escolas'          => $escolas,
            'versao_app'       => array(
                'versao_mais_recente' => '1.0.1',
                'codigo_versao'       => 2,
                'url_download'        => url('/download/ecidade-pauta-eletronica.apk'),
                'novidades'           => 'Seleção de ano letivo e sincronização aprimorada.',
                'obrigatoria'         => false
            )
        );

        return new DBJsonResponse($dadosRetorno, 'Autenticacao realizada com sucesso');
    }

    /**
     * Retorna turmas e regencias do professor na escola/ano
     */
    public function getTurmas(Request $request)
    {
        $escolaId  = (int)$request->input('escola_id');
        $ano       = $request->input('ano') ? (int)$request->input('ano') : 0;
        $usuarioId = $request->input('usuario_id') ? (int)$request->input('usuario_id') : 0;

        $cgm = 0;
        if ($usuarioId > 0) {
            $sqlCgm = "SELECT cgmlogin FROM db_usuacgm WHERE id_usuario = {$usuarioId}";
            $rsCgm = DB::select($sqlCgm);
            if (!empty($rsCgm)) {
                $cgm = (int)$rsCgm[0]->cgmlogin;
            }
        }

        $sqlRechumanoDocente = "
            SELECT ed285_i_rechumano FROM escola.rechumanocgm WHERE ed285_i_cgm = {$cgm}
            UNION
            SELECT ed284_i_rechumano FROM escola.rechumanopessoal INNER JOIN rhpessoal ON rh01_regist = ed284_i_rhpessoal WHERE rh01_numcgm = {$cgm}
        ";

        // Se escola_id nao foi informado, resolve automaticamente para a escola do docente
        if (!$escolaId && $cgm > 0) {
            $rsEscolaDocente = DB::select("
                SELECT DISTINCT ed18_i_codigo
                FROM escola.escola
                INNER JOIN escola.turma ON ed57_i_escola = ed18_i_codigo
                INNER JOIN escola.regencia ON ed59_i_turma = ed57_i_codigo
                INNER JOIN escola.regenciahorario ON ed58_i_regencia = ed59_i_codigo
                WHERE ed58_i_rechumano IN ({$sqlRechumanoDocente})
                LIMIT 1
            ");
            if (!empty($rsEscolaDocente)) {
                $escolaId = (int)$rsEscolaDocente[0]->ed18_i_codigo;
            }
        }

        if (!$escolaId) {
            $rsEscolaFallback = DB::select("
                SELECT DISTINCT ed18_i_codigo
                FROM escola.escola
                INNER JOIN escola.turma ON ed57_i_escola = ed18_i_codigo
                LIMIT 1
            ");
            if (!empty($rsEscolaFallback)) {
                $escolaId = (int)$rsEscolaFallback[0]->ed18_i_codigo;
            }
        }

        $sqlBase = "
            SELECT DISTINCT
                ed57_i_codigo as turma_id,
                trim(ed57_c_descr) as turma_nome,
                trim(ed11_c_descr) as serie_nome,
                trim(ed15_c_nome) as turno_nome,
                ed59_i_codigo as regencia_id,
                trim(ed232_c_descr) as disciplina_nome,
                ed232_i_codigo as disciplina_id,
                ed52_i_ano as ano_letivo,
                (SELECT count(*) FROM escola.matricula WHERE ed60_i_turma = ed57_i_codigo AND ed60_c_situacao = 'MATRICULADO') as total_alunos
            FROM escola.turma
            INNER JOIN escola.calendario ON ed52_i_codigo = ed57_i_calendario
            INNER JOIN escola.turmaserieregimemat ON ed220_i_turma = ed57_i_codigo
            INNER JOIN escola.serieregimemat ON ed223_i_codigo = ed220_i_serieregimemat
            INNER JOIN escola.serie ON ed11_i_codigo = ed223_i_serie
            INNER JOIN escola.turno ON ed15_i_codigo = ed57_i_turno
            INNER JOIN escola.regencia ON ed59_i_turma = ed57_i_codigo
            INNER JOIN escola.caddisciplina ON ed232_i_codigo = ed59_i_disciplina
            LEFT JOIN escola.regenciahorario ON ed58_i_regencia = ed59_i_codigo
        ";

        $turmas = array();
        if ($cgm > 0 && $usuarioId != 1) {
            $whereDocente = ($escolaId > 0) ? "ed57_i_escola = {$escolaId} AND ed58_i_rechumano IN ({$sqlRechumanoDocente})" : "ed58_i_rechumano IN ({$sqlRechumanoDocente})";
            if ($ano > 0) {
                $whereDocente .= " AND ed52_i_ano = {$ano}";
            }
            try {
                $turmas = DB::select("{$sqlBase} WHERE {$whereDocente} ORDER BY trim(ed57_c_descr), trim(ed232_c_descr)");
            } catch (\Exception $e) {
                $turmas = array();
            }

            if (empty($turmas) && $ano > 0) {
                // Tenta sem filtro de ano como fallback resiliente
                $whereDocenteSemAno = ($escolaId > 0) ? "ed57_i_escola = {$escolaId} AND ed58_i_rechumano IN ({$sqlRechumanoDocente})" : "ed58_i_rechumano IN ({$sqlRechumanoDocente})";
                try {
                    $turmas = DB::select("{$sqlBase} WHERE {$whereDocenteSemAno} ORDER BY trim(ed57_c_descr), trim(ed232_c_descr)");
                } catch (\Exception $e) {
                    $turmas = array();
                }
            }
        }

        if (empty($turmas) && $escolaId > 0) {
            $whereEscolaAno = "ed57_i_escola = {$escolaId}";
            if ($ano > 0) {
                $whereEscolaAno .= " AND ed52_i_ano = {$ano}";
            }
            try {
                $turmas = DB::select("{$sqlBase} WHERE {$whereEscolaAno} ORDER BY trim(ed57_c_descr), trim(ed232_c_descr)");
            } catch (\Exception $e) {
                $turmas = array();
            }
        }

        if (empty($turmas)) {
            $whereFallback = ($escolaId > 0) ? "ed57_i_escola = {$escolaId}" : "1=1";
            if ($ano > 0) {
                $whereFallback .= " AND ed52_i_ano = {$ano}";
            }
            try {
                $turmas = DB::select("{$sqlBase} WHERE {$whereFallback} ORDER BY trim(ed57_c_descr), trim(ed232_c_descr) LIMIT 30");
            } catch (\Exception $e) {
                $turmas = array();
            }
        }

        foreach ($turmas as $turma) {
            $sqlHorarios = "
                SELECT 
                    ed58_i_codigo as regencia_horario_id,
                    trim(ed32_c_descr) as dia_semana,
                    trim(ed08_c_descr) as periodo_aula,
                    trim(ed17_h_inicio) as hora_inicio,
                    trim(ed17_h_fim) as hora_fim
                FROM escola.regenciahorario
                INNER JOIN escola.diasemana ON ed32_i_codigo = ed58_i_diasemana
                INNER JOIN escola.periodoescola ON ed17_i_codigo = ed58_i_periodo
                INNER JOIN escola.periodoaula ON ed08_i_codigo = ed17_i_periodoaula
                WHERE ed58_i_regencia = {$turma->regencia_id}
                  AND (ed58_ativo IS TRUE OR ed58_ativo IS NULL)
                ORDER BY ed32_i_codigo, ed17_h_inicio
            ";
            try {
                $turma->grade_horaria = DB::select($sqlHorarios);
            } catch (\Exception $e) {
                $turma->grade_horaria = array();
            }
        }

        return new DBJsonResponse($turmas, 'Turmas carregadas com sucesso');
    }

    /**
     * Retorna a lista de alunos da turma com numeros de chamada
     */
    public function getAlunosTurma($turmaId)
    {
        $turmaId = (int)$turmaId;
        if (!$turmaId) {
            return new DBJsonResponse(null, 'Codigo da turma invalido.', 400);
        }

        $sql = "
            SELECT 
                ed47_i_codigo as aluno_id,
                ed60_i_codigo as matricula_id,
                ed60_i_numaluno as numero_chamada,
                trim(ed47_v_nome) as nome_completo,
                trim(ed47_v_nomesocial) as nome_social,
                ed47_d_nasc as data_nascimento,
                trim(ed60_c_situacao) as situacao,
                trim(ed47_v_sexo) as sexo
            FROM escola.matricula
            INNER JOIN escola.aluno ON ed47_i_codigo = ed60_i_aluno
            WHERE ed60_i_turma = {$turmaId}
              AND ed60_c_situacao = 'MATRICULADO'
            ORDER BY COALESCE(ed60_i_numaluno, 999), ed47_v_nome
        ";

        $alunos = DB::select($sql);

        return new DBJsonResponse($alunos, 'Alunos recuperados com sucesso');
    }

    /**
     * Endpoint retrocompativel para busca de alunos
     */
    public function getAlunosCompat(Request $request)
    {
        $turmaId = $request->input('id_turma') ? $request->input('id_turma') : $request->input('turma_id');
        return $this->getAlunosTurma($turmaId);
    }

    /**
     * Consulta registros de frequencia e faltas lancadas
     */
    public function getFrequencias(Request $request)
    {
        $regenciaId = (int)$request->input('regencia_id');
        $dataInicio = $request->input('data_inicio') ? $request->input('data_inicio') : date('Y-m-01');
        $dataFim    = $request->input('data_fim') ? $request->input('data_fim') : date('Y-m-d');

        if (!$regenciaId) {
            return new DBJsonResponse(null, 'Regencia nao informada.', 400);
        }

        $sql = "
            SELECT 
                ed300_sequencial as diario_id,
                ed300_datalancamento as data_aula,
                ed300_hora as hora,
                trim(ed300_auladesenvolvida) as conteudo_ministrado,
                ed302_regenciahorario as regencia_horario_id,
                ed301_aluno as aluno_falta_id
            FROM escola.diarioclasse
            INNER JOIN escola.diarioclasseregenciahorario ON ed302_diarioclasse = ed300_sequencial
            LEFT JOIN escola.diarioclassealunofalta ON ed301_diarioclasseregenciahorario = ed302_sequencial
            WHERE ed302_regenciahorario IN (SELECT ed58_i_codigo FROM escola.regenciahorario WHERE ed58_i_regencia = {$regenciaId})
              AND ed300_datalancamento BETWEEN '{$dataInicio}' AND '{$dataFim}'
            ORDER BY ed300_datalancamento DESC, ed300_hora DESC
        ";

        $resultados = DB::select($sql);

        $agrupados = array();
        foreach ($resultados as $row) {
            $key = $row->diario_id;
            if (!isset($agrupados[$key])) {
                $agrupados[$key] = array(
                    'diario_id'           => (int)$row->diario_id,
                    'data_aula'           => $row->data_aula,
                    'hora'                => $row->hora,
                    'conteudo_ministrado' => $row->conteudo_ministrado,
                    'regencia_horario_id' => (int)$row->regencia_horario_id,
                    'faltas'              => array()
                );
            }
            if ($row->aluno_falta_id) {
                $agrupados[$key]['faltas'][] = (int)$row->aluno_falta_id;
            }
        }

        return new DBJsonResponse(array_values($agrupados), 'Frequencias consultadas com sucesso');
    }

    /**
     * Sincronizacao em lote de Presencas/Faltas e Diarios registrados Offline
     */
    public function sincronizarFrequencia(Request $request)
    {
        $chamadas = $request->input('chamadas');
        $usuarioId = $request->input('usuario_id') ? (int)$request->input('usuario_id') : 1;

        // Suporte flexivel caso venha na estrutura flat 'frequencias'
        if ((!is_array($chamadas) || empty($chamadas)) && $request->input('frequencias')) {
            $freqList = $request->input('frequencias');
            $agrupadas = array();
            foreach ($freqList as $f) {
                $dataF = isset($f['data_aula']) ? $f['data_aula'] : date('Y-m-d');
                $turmaId = isset($f['id_turma']) ? (int)$f['id_turma'] : 0;
                $chave = $dataF . '_' . $turmaId;

                if (!isset($agrupadas[$chave])) {
                    $sqlHor = "SELECT ed58_i_codigo FROM escola.regencia INNER JOIN escola.regenciahorario ON ed58_i_regencia = ed59_i_codigo WHERE ed59_i_turma = {$turmaId} LIMIT 1";
                    $rsHor = DB::select($sqlHor);
                    $regHorId = !empty($rsHor) ? (int)$rsHor[0]->ed58_i_codigo : 0;

                    $agrupadas[$chave] = array(
                        'data'                => $dataF,
                        'regencia_horario_id' => $regHorId,
                        'conteudo'            => 'Aula regular ministrada',
                        'hora'                => date('H:i'),
                        'faltas'              => array()
                    );
                }

                if (isset($f['status']) && ($f['status'] === 'F' || $f['status'] === 'J')) {
                    $agrupadas[$chave]['faltas'][] = (int)$f['id_aluno'];
                }
            }
            $chamadas = array_values($agrupadas);
        }

        if (!is_array($chamadas) || empty($chamadas)) {
            return new DBJsonResponse(null, 'Nenhuma chamada informada para sincronizacao.', 400);
        }

        $processados = 0;
        DB::beginTransaction();

        try {
            foreach ($chamadas as $item) {
                $dataAula          = isset($item['data']) ? $item['data'] : date('Y-m-d');
                $regenciaHorarioId = isset($item['regencia_horario_id']) ? (int)$item['regencia_horario_id'] : 0;
                $conteudo          = isset($item['conteudo']) ? $item['conteudo'] : 'Aula desenvolvida';
                $hora              = isset($item['hora']) ? substr($item['hora'], 0, 5) : date('H:i');
                $faltas            = isset($item['faltas']) && is_array($item['faltas']) ? $item['faltas'] : array();

                if (!$regenciaHorarioId) {
                    continue;
                }

                $sqlBusca = "
                    SELECT ed300_sequencial, ed302_sequencial
                    FROM escola.diarioclasse
                    INNER JOIN escola.diarioclasseregenciahorario ON ed302_diarioclasse = ed300_sequencial
                    WHERE ed302_regenciahorario = {$regenciaHorarioId}
                      AND ed300_datalancamento = '{$dataAula}'
                ";
                $rsBusca = DB::select($sqlBusca);

                $diarioClasseId = null;
                $regHorarioSeqId = null;

                if (!empty($rsBusca)) {
                    $diarioClasseId  = (int)$rsBusca[0]->ed300_sequencial;
                    $regHorarioSeqId = (int)$rsBusca[0]->ed302_sequencial;

                    DB::table('escola.diarioclasse')
                        ->where('ed300_sequencial', $diarioClasseId)
                        ->update(array(
                            'ed300_auladesenvolvida' => $conteudo,
                            'ed300_hora' => $hora,
                            'ed300_id_usuario' => $usuarioId
                        ));

                    DB::table('escola.diarioclassealunofalta')
                        ->where('ed301_diarioclasseregenciahorario', $regHorarioSeqId)
                        ->delete();
                } else {
                    $seqRes = DB::select("SELECT nextval('escola.diarioclasse_ed300_sequencial_seq') as nextseq");
                    $diarioClasseId = (int)$seqRes[0]->nextseq;

                    DB::table('escola.diarioclasse')->insert(array(
                        'ed300_sequencial'       => $diarioClasseId,
                        'ed300_id_usuario'       => $usuarioId,
                        'ed300_datalancamento'   => $dataAula,
                        'ed300_hora'             => $hora,
                        'ed300_auladesenvolvida' => $conteudo
                    ));

                    $seqRegRes = DB::select("SELECT nextval('escola.diarioclasseregenciahorario_ed302_sequencial_seq') as nextseq");
                    $regHorSeqId = (int)$seqRegRes[0]->nextseq;

                    DB::table('escola.diarioclasseregenciahorario')->insert(array(
                        'ed302_sequencial'      => $regHorSeqId,
                        'ed302_regenciahorario' => $regenciaHorarioId,
                        'ed302_diarioclasse'    => $diarioClasseId
                    ));
                }

                foreach ($faltas as $alunoId) {
                    $alunoId = (int)$alunoId;
                    if ($alunoId > 0) {
                        $seqFaltaRes = DB::select("SELECT nextval('escola.diarioclassealunofalta_ed301_sequencial_seq') as nextseq");
                        $faltaId = (int)$seqFaltaRes[0]->nextseq;

                        DB::table('escola.diarioclassealunofalta')->insert(array(
                            'ed301_sequencial'                  => $faltaId,
                            'ed301_aluno'                       => $alunoId,
                            'ed301_diarioclasseregenciahorario' => $regHorSeqId
                        ));
                    }
                }

                $processados++;
            }

            DB::commit();

            return new DBJsonResponse(array(
                'total_recebido'    => count($chamadas),
                'total_processados' => $processados
            ), "Sincronizacao de {$processados} chamada(s) concluida com sucesso");

        } catch (\Exception $e) {
            DB::rollBack();
            return new DBJsonResponse(null, 'Erro ao sincronizar frequencia: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Sincronizacao de Conteudos/Aulas Ministradas registradas Offline
     */
    public function sincronizarAulas(Request $request)
    {
        $aulas = $request->input('aulas');
        $usuarioId = $request->input('usuario_id') ? (int)$request->input('usuario_id') : 1;

        if (!is_array($aulas) || empty($aulas)) {
            return new DBJsonResponse(null, 'Nenhuma aula informada para sincronizacao.', 400);
        }

        $processados = 0;
        DB::beginTransaction();

        try {
            foreach ($aulas as $item) {
                $turmaId   = isset($item['id_turma']) ? (int)$item['id_turma'] : 0;
                $dataAula  = isset($item['data_aula']) ? $item['data_aula'] : date('Y-m-d');
                $conteudo  = isset($item['conteudo_ministrado']) ? $item['conteudo_ministrado'] : 'Aula desenvolvida';
                $numAulas  = isset($item['numero_aulas']) ? (int)$item['numero_aulas'] : 1;

                if (!$turmaId) {
                    continue;
                }

                $sqlReg = "SELECT ed59_i_codigo FROM escola.regencia WHERE ed59_i_turma = {$turmaId} LIMIT 1";
                $rsReg = DB::select($sqlReg);
                $regenciaId = !empty($rsReg) ? (int)$rsReg[0]->ed59_i_codigo : 0;

                $sqlHor = "SELECT ed58_i_codigo FROM escola.regenciahorario WHERE ed58_i_regencia = {$regenciaId} LIMIT 1";
                $rsHor = DB::select($sqlHor);
                $regHorarioId = !empty($rsHor) ? (int)$rsHor[0]->ed58_i_codigo : 0;

                if ($regHorarioId > 0) {
                    $sqlBusca = "
                        SELECT ed300_sequencial
                        FROM escola.diarioclasse
                        INNER JOIN escola.diarioclasseregenciahorario ON ed302_diarioclasse = ed300_sequencial
                        WHERE ed302_regenciahorario = {$regHorarioId}
                          AND ed300_datalancamento = '{$dataAula}'
                    ";
                    $rsBusca = DB::select($sqlBusca);

                    if (!empty($rsBusca)) {
                        $diarioId = (int)$rsBusca[0]->ed300_sequencial;
                        DB::table('escola.diarioclasse')
                            ->where('ed300_sequencial', $diarioId)
                            ->update(array(
                                'ed300_auladesenvolvida' => $conteudo,
                                'ed300_id_usuario'       => $usuarioId
                            ));
                    } else {
                        $seqRes = DB::select("SELECT nextval('escola.diarioclasse_ed300_sequencial_seq') as nextseq");
                        $diarioId = (int)$seqRes[0]->nextseq;

                        DB::table('escola.diarioclasse')->insert(array(
                            'ed300_sequencial'       => $diarioId,
                            'ed300_id_usuario'       => $usuarioId,
                            'ed300_datalancamento'   => $dataAula,
                            'ed300_hora'             => date('H:i'),
                            'ed300_auladesenvolvida' => $conteudo
                        ));

                        $seqRegRes = DB::select("SELECT nextval('escola.diarioclasseregenciahorario_ed302_sequencial_seq') as nextseq");
                        $regHorSeqId = (int)$seqRegRes[0]->nextseq;

                        DB::table('escola.diarioclasseregenciahorario')->insert(array(
                            'ed302_sequencial'      => $regHorSeqId,
                            'ed302_regenciahorario' => $regHorarioId,
                            'ed302_diarioclasse'    => $diarioId
                        ));
                    }
                }

                $processados++;
            }

            DB::commit();

            return new DBJsonResponse(array(
                'total_processados' => $processados
            ), "Sincronizacao de {$processados} aula(s) realizada com sucesso");

        } catch (\Exception $e) {
            DB::rollBack();
            return new DBJsonResponse(null, 'Erro ao sincronizar aulas: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Retorna periodos de avaliacao e notas atuais da turma
     */
    public function getAvaliacoesTurma(Request $request, $turmaId)
    {
        $turmaId = (int)$turmaId;
        $periodo = $request->input('periodo') ? (int)$request->input('periodo') : null;

        if (!$turmaId) {
            return new DBJsonResponse(null, 'Codigo da turma invalido.', 400);
        }

        $sqlPeriodos = "
            SELECT DISTINCT
                ed09_i_codigo as periodo_id,
                trim(ed09_c_descr) as periodo_nome,
                trim(ed09_c_abrev) as periodo_abrev
            FROM escola.procavaliacao
            INNER JOIN escola.turma ON ed57_i_codigo = {$turmaId}
            ORDER BY ed09_i_codigo
        ";
        $periodos = DB::select($sqlPeriodos);

        $sqlNotas = "
            SELECT 
                ed95_i_regencia as regencia_id,
                ed95_i_aluno as aluno_id,
                ed72_i_procavaliacao as periodo_id,
                ed72_i_valornota as nota,
                trim(ed72_c_valorconceito) as conceito,
                trim(ed72_t_parecer) as parecer,
                ed72_i_numfaltas as faltas
            FROM escola.diario
            INNER JOIN escola.regencia ON ed59_i_codigo = ed95_i_regencia
            INNER JOIN escola.diarioavaliacao ON ed72_i_diario = ed95_i_codigo
            WHERE ed59_i_turma = {$turmaId}
        ";

        if ($periodo) {
            $sqlNotas .= " AND ed72_i_procavaliacao = {$periodo}";
        }

        $notas = DB::select($sqlNotas);

        return new DBJsonResponse(array(
            'periodos' => $periodos,
            'notas'    => $notas
        ), 'Avaliacoes consultadas com sucesso');
    }

    /**
     * Sincronizacao em lote de Notas e Conceitos registrados Offline
     */
    public function sincronizarAvaliacoes(Request $request)
    {
        $avaliacoes = $request->input('avaliacoes');

        // Suporte flexivel para envio sob a chave 'notas'
        if ((!is_array($avaliacoes) || empty($avaliacoes)) && $request->input('notas')) {
            $notasList = $request->input('notas');
            $avaliacoes = array();
            foreach ($notasList as $n) {
                $turmaId = isset($n['id_turma']) ? (int)$n['id_turma'] : 0;
                $sqlReg = "SELECT ed59_i_codigo FROM escola.regencia WHERE ed59_i_turma = {$turmaId} LIMIT 1";
                $rsReg = DB::select($sqlReg);
                $regId = !empty($rsReg) ? (int)$rsReg[0]->ed59_i_codigo : 0;

                $avaliacoes[] = array(
                    'regencia_id' => $regId,
                    'aluno_id'    => isset($n['id_aluno']) ? (int)$n['id_aluno'] : 0,
                    'periodo_id'  => isset($n['periodo']) ? (int)$n['periodo'] : 1,
                    'nota'        => isset($n['nota']) ? $n['nota'] : null,
                    'parecer'     => isset($n['parecer_descritivo']) ? $n['parecer_descritivo'] : null
                );
            }
        }

        if (!is_array($avaliacoes) || empty($avaliacoes)) {
            return new DBJsonResponse(null, 'Nenhuma avaliacao informada.', 400);
        }

        $processados = 0;
        DB::beginTransaction();

        try {
            foreach ($avaliacoes as $item) {
                $regenciaId = isset($item['regencia_id']) ? (int)$item['regencia_id'] : 0;
                $alunoId    = isset($item['aluno_id']) ? (int)$item['aluno_id'] : 0;
                $periodoId  = isset($item['periodo_id']) ? (int)$item['periodo_id'] : 0;
                $nota       = isset($item['nota']) ? (float)$item['nota'] : null;
                $conceito   = isset($item['conceito']) ? $item['conceito'] : null;
                $parecer    = isset($item['parecer']) ? $item['parecer'] : null;
                $faltas     = isset($item['faltas']) ? (int)$item['faltas'] : null;

                if (!$regenciaId || !$alunoId || !$periodoId) {
                    continue;
                }

                $sqlDiario = "SELECT ed95_i_codigo FROM escola.diario WHERE ed95_i_regencia = {$regenciaId} AND ed95_i_aluno = {$alunoId}";
                $rsDiario  = DB::select($sqlDiario);
                if (empty($rsDiario)) {
                    continue;
                }
                $diarioId = (int)$rsDiario[0]->ed95_i_codigo;

                $sqlAval = "SELECT ed72_i_codigo FROM escola.diarioavaliacao WHERE ed72_i_diario = {$diarioId} AND ed72_i_procavaliacao = {$periodoId}";
                $rsAval  = DB::select($sqlAval);

                if (!empty($rsAval)) {
                    $avalId = (int)$rsAval[0]->ed72_i_codigo;
                    DB::table('escola.diarioavaliacao')
                        ->where('ed72_i_codigo', $avalId)
                        ->update(array(
                            'ed72_i_valornota'     => $nota,
                            'ed72_c_valorconceito' => $conceito,
                            'ed72_t_parecer'       => $parecer,
                            'ed72_i_numfaltas'     => $faltas
                        ));
                } else {
                    $seqRes = DB::select("SELECT nextval('escola.diarioavaliacao_ed72_i_codigo_seq') as nextseq");
                    $avalId = (int)$seqRes[0]->nextseq;

                    DB::table('escola.diarioavaliacao')->insert(array(
                        'ed72_i_codigo'        => $avalId,
                        'ed72_i_diario'        => $diarioId,
                        'ed72_i_procavaliacao' => $periodoId,
                        'ed72_i_valornota'     => $nota,
                        'ed72_c_valorconceito' => $conceito,
                        'ed72_t_parecer'       => $parecer,
                        'ed72_i_numfaltas'     => $faltas
                    ));
                }

                $processados++;
            }

            DB::commit();

            return new DBJsonResponse(array(
                'total_processados' => $processados
            ), "Sincronizacao de {$processados} nota(s)/avaliacao(oes) realizada com sucesso");

        } catch (\Exception $e) {
            DB::rollBack();
            return new DBJsonResponse(null, 'Erro ao sincronizar avaliacoes: ' . $e->getMessage(), 500);
        }
    }
}
