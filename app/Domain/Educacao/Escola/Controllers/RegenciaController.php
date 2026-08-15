<?php

namespace App\Domain\Educacao\Escola\Controllers;

use App\Domain\Core\Base\Http\Response\DBJsonResponse;
use App\Domain\Educacao\Escola\Models\AvaliacoesParciais\ProcedimentoResultadoParcial;
use App\Domain\Educacao\Escola\Models\Turma;
use App\Domain\Educacao\Escola\Models\TurmaEtapaRegimeMatricula;
use App\Domain\Educacao\Escola\Services\PermissaoDiarioService;
use App\Http\Controllers\Controller;
use db_utils;
use ECidade\Educacao\Escola\Repository\ProfissionalEscolaRepository;
use Exception;
use Illuminate\Http\Request;

class RegenciaController extends Controller
{
    /**
     * @param Request $request
     * @param $codigoTurmaSerieRegimeMat
     * @return DBJsonResponse
     * @throws Exception
     */
    public function regenciasProfissional(Request $request, $codigoTurmaSerieRegimeMat)
    {
        $codigoEscola = db_getsession('DB_coddepto');
        $daoTurmaSerieRegimeMat = new \cl_turmaserieregimemat();
        $sql = $daoTurmaSerieRegimeMat->sql_query(
            null,
            'ed220_i_turma, ed223_i_serie',
            null,
            "ed220_i_codigo = {$codigoTurmaSerieRegimeMat}"
        );

        $rsTurmaSerieRegimeMat = db_query($sql);
        
        if (!$rsTurmaSerieRegimeMat) {
            throw new Exception('Erro ao buscar Turma.');
        }
        
        $turmaSerieRegimeMat = db_utils::fieldsMemory($rsTurmaSerieRegimeMat, 0);
        
        $turma = \TurmaRepository::getTurmaByCodigo($turmaSerieRegimeMat->ed220_i_turma);
        $regencias = $turma->getDisciplinas();
        foreach ($regencias as $key => $regencia) {
            if ($regencia->getEtapa()->getCodigo() != $turmaSerieRegimeMat->ed223_i_serie) {
                unset($regencias[$key]);
            }
        }

        $codigousuarioTeste = db_getsession('DB_id_usuario');
        $usuario = new \UsuarioSistema($codigousuarioTeste);

        $permissaoDiario = 0;
        if (!$usuario->isAdministrador()) {
            $profissionalEscolaRepository = new ProfissionalEscolaRepository();
            $profissionalEscola = $profissionalEscolaRepository->scopeEscola($codigoEscola)
                ->scopeCgm($usuario->getCGM()->getCodigo())
                ->scopeAtivo()
                ->first();

            $permissaoDiarioService = new PermissaoDiarioService();
            $permissaoDiario = $permissaoDiarioService->getMaiorPermissaoProfissional($profissionalEscola);
        }

        $disciplinasTurma = [];
        foreach ($regencias as $regencia) {
            $temVinculo = false;
            foreach ($regencia->getDocentes() as $docente) {
                if ($docente->getCgm()->getCodigo() == $usuario->getCGM()->getCodigo()) {
                    $temVinculo = true;
                }
            }

            $regenciaSemProfessor = count($regencia->getDocentes()) == 0;
            if ($permissaoDiario == PermissaoDiarioService::PERMISSAO_TOTAL ||
                ($permissaoDiario == PermissaoDiarioService::PERMISSAO_PROFESSOR && $temVinculo
                    || $usuario->isAdministrador() == 1)) {
                if ($usuario->isAdministrador() == 1) {
                    $disciplinasTurma[] = $this->disciplinarResource($regencia);
                } else {
                    if ($regenciaSemProfessor == false) {
                        $disciplinasTurma[] = $this->disciplinarResource($regencia);
                    }
                }
            }
        }
        return new DBJsonResponse($disciplinasTurma);
    }

    public function porTurma(TurmaEtapaRegimeMatricula $turma)
    {
        $etapa = $turma->etapaRegimeMatricula;
        $turma = Turma::find($turma->ed220_i_turma);
        $disciplinasTurma = [];
        $regencias = $turma->regencias()->get();
        foreach ($regencias as $regencia) {
            if ($regencia->ed59_i_serie != $etapa->ed223_i_serie) {
                continue;
            }
            $regencia->disciplinaEnsino;
            $disciplinasTurma[] = $regencia;
        }

        return new DBJsonResponse($disciplinasTurma);
    }

    public function disciplinarResource(\Regencia $regencia)
    {
        $disciplina = (object)[
            'codigo_regencia' => $regencia->getCodigo(),
            'disciplina' => $regencia->getDisciplina()->getNomeDisciplina(),
            'encerrada' => $regencia->isEncerrada(),
            'professores' => [],
        ];

        $docentes = $regencia->getDocentes();
        foreach ($docentes as $docente) {
            if (in_array($docente->getCgm()->getNome(), $disciplina->professores)) {
                continue;
            }
            $disciplina->professores[] = $docente->getCgm()->getNome();
        }

        return $disciplina;
    }

    public function salvarFormaobtencao(Request $request)
    {
        $codigoResultadoParcial = $request->get('codigoResultadoParcial');
        $formaObtencao = $request->get('formaObtencao');
        $formaObtencaoRecuperacao = $request->get('formaObtencaoRecuperacao');
        $resultadoParcial = ProcedimentoResultadoParcial::find($codigoResultadoParcial);
        if (!empty($formaObtencao)) {
            $resultadoParcial->ed342_formaobtencao = $formaObtencao;
        }
        if (!empty($formaObtencaoRecuperacao)) {
            $resultadoParcial->ed342_formaobtencaofinal = $formaObtencaoRecuperacao;
        }
        $resultadoParcial->save();

        return new DBJsonResponse([], 'Forma de obtenção atualizada com sucesso!');
    }
}
