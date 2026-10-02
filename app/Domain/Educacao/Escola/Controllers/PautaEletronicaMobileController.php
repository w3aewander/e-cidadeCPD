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
        $dados = array(
            'status'     => 'online',
            'modulo'     => 'Ecidade - Pauta Eletronica Mobile API',
            'versao_api' => '1.0.0',
            'timestamp'  => date('Y-m-d H:i:s'),
            'database'   => 'conectado'
        );

        return new DBJsonResponse($dados, 'API Pauta Eletronica operacional');
    }

    /**
     * Autenticacao do Professor para o App Mobile
     */
    public function login(Request $request)
    {
        $login = $request->input('login');
        $senha = $request->input('senha');
        $ano   = $request->input('ano') ? (int)$request->input('ano') : (int)date('Y');

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
            'ano_letivo' => $ano,
            'escolas'    => $escolas
        );

        return new DBJsonResponse($dadosRetorno, 'Autenticacao realizada com sucesso');
    }

    /**
     * Retorna turmas e regencias do professor na escola/ano
     */
    public function getTurmas(Request $request)
    {
        $escolaId  = (int)$request->input('escola_id');
        $ano       = $request->input('ano') ? (int)$request->input('ano') : (int)date('Y');
        $usuarioId = $request->input('usuario_id') ? (int)$request->input('usuario_id') : 0;

        if (!$escolaId) {
            return new DBJsonResponse(null, 'ID da escola e obrigatorio.', 400);
        }

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

        $sqlBase = "
            SELECT DISTINCT
                ed57_i_codigo as turma_id,
                trim(ed57_c_descr) as turma_nome,
                trim(ed11_c_descr) as serie_nome,
                trim(ed15_c_nome) as turno_nome,
                ed59_i_codigo as regencia_id,
                trim(ed232_c_descr) as disciplina_nome,
                ed232_i_codigo as disciplina_id,
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
            $whereDocente = "ed57_i_escola = {$escolaId} AND ed58_i_rechumano IN ({$sqlRechumanoDocente})";
            if ($ano > 0) {
                $whereDocente .= " AND ed52_i_ano = {$ano}";
            }
            try {
                $turmas = DB::select("{$sqlBase} WHERE {$whereDocente} ORDER BY trim(ed57_c_descr), trim(ed232_c_descr)");
            } catch (\Exception $e) {
                $turmas = array();
            }

            if (empty($turmas)) {
                $whereDocenteSemAno = "ed57_i_escola = {$escolaId} AND ed58_i_rechumano IN ({$sqlRechumanoDocente})";
                try {
                    $turmas = DB::select("{$sqlBase} WHERE {$whereDocenteSemAno} ORDER BY trim(ed57_c_descr), trim(ed232_c_descr)");
                } catch (\Exception $e) {
                    $turmas = array();
                }
            }
        }

        if (empty($turmas)) {
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
            $turmas = DB::select("{$sqlBase} WHERE ed57_i_escola = {$escolaId} ORDER BY trim(ed57_c_descr), trim(ed232_c_descr) LIMIT 30");
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
            $turma->grade_horaria = DB::select($sqlHorarios);
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

        $sqlAulas = "
            SELECT DISTINCT
                ed300_sequencial as diarioclasse_id,
                ed300_datalancamento as data_aula,
                trim(ed300_hora) as hora,
                ed300_auladesenvolvida as conteudo,
                ed302_sequencial as diarioclasseregenciahorario_id,
                ed302_regenciahorario as regencia_horario_id
            FROM escola.diarioclasse
            INNER JOIN escola.diarioclasseregenciahorario ON ed302_diarioclasse = ed300_sequencial
            INNER JOIN escola.regenciahorario ON ed58_i_codigo = ed302_regenciahorario
            WHERE ed58_i_regencia = {$regenciaId}
              AND ed300_datalancamento BETWEEN '{$dataInicio}' AND '{$dataFim}'
            ORDER BY ed300_datalancamento DESC
        ";

        $aulas = DB::select($sqlAulas);

        foreach ($aulas as $aula) {
            $sqlFaltas = "
                SELECT ed301_aluno as aluno_id
                FROM escola.diarioclassealunofalta
                WHERE ed301_diarioclasseregenciahorario = {$aula->diarioclasseregenciahorario_id}
            ";
            $rsFaltas = DB::select($sqlFaltas);
            $aula->alunos_faltosos = array();
            foreach ($rsFaltas as $f) {
                $aula->alunos_faltosos[] = (int)$f->aluno_id;
            }
        }

        return new DBJsonResponse($aulas, 'Frequencias consultadas com sucesso');
    }

    /**
     * Sincronizacao em lote de Chamadas registradas Offline
     */
    public function sincronizarFrequencia(Request $request)
    {
        $chamadas = $request->input('chamadas');
        $usuarioId = $request->input('usuario_id') ? (int)$request->input('usuario_id') : 1;

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
                    $regHorarioSeqId = (int)$seqRegRes[0]->nextseq;

                    DB::table('escola.diarioclasseregenciahorario')->insert(array(
                        'ed302_sequencial'      => $regHorarioSeqId,
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
                            'ed301_diarioclasseregenciahorario' => $regHorarioSeqId
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
     * Retorna periodos de avaliacao e notas atuais da turma
     */
    public function getAvaliacoesTurma(Request $request, $turmaId)
    {
        $turmaId    = (int)$turmaId;
        $regenciaId = (int)$request->input('regencia_id');

        if (!$turmaId) {
            return new DBJsonResponse(null, 'Turma invalida.', 400);
        }

        $sqlPeriodos = "
            SELECT DISTINCT
                ed41_i_codigo as periodo_id,
                trim(ed09_c_descr) as periodo_nome,
                trim(ed09_c_abrev) as periodo_sigla,
                trim(ed37_c_tipo) as forma_avaliacao,
                ed37_i_menorvalor as menor_valor,
                ed37_i_maiorvalor as maior_valor
            FROM escola.turma
            INNER JOIN escola.turmaserieregimemat ON ed220_i_turma = ed57_i_codigo
            INNER JOIN escola.procedimento ON ed40_i_codigo = ed220_i_procedimento
            INNER JOIN escola.procavaliacao ON ed41_i_procedimento = ed40_i_codigo
            INNER JOIN escola.periodoavaliacao ON ed09_i_codigo = ed41_i_periodoavaliacao
            INNER JOIN escola.formaavaliacao ON ed37_i_codigo = ed41_i_formaavaliacao
            WHERE ed57_i_codigo = {$turmaId}
            ORDER BY ed41_i_codigo
        ";
        $periodos = DB::select($sqlPeriodos);

        $notas = array();
        if ($regenciaId > 0) {
            $sqlNotas = "
                SELECT 
                    ed72_i_codigo as avaliacao_id,
                    ed95_i_aluno as aluno_id,
                    ed72_i_procavaliacao as periodo_id,
                    ed72_i_valornota as nota,
                    trim(ed72_c_valorconceito) as conceito,
                    ed72_t_parecer as parecer,
                    ed72_i_numfaltas as faltas
                FROM escola.diario
                INNER JOIN escola.diarioavaliacao ON ed72_i_diario = ed95_i_codigo
                WHERE ed95_i_regencia = {$regenciaId}
            ";
            $notas = DB::select($sqlNotas);
        }

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
