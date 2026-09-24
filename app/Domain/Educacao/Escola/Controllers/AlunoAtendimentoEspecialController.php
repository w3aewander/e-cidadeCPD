<?php

namespace App\Domain\Educacao\Escola\Controllers;

use App\Domain\Educacao\Escola\Repositories\AlunoAtendimentoEspecialRepository;
use App\Domain\Educacao\Secretaria\Repositories\RecursosAtendimentosRepository;
use App\Domain\Educacao\Secretaria\Repositories\RecursosUtilizadosRepository;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use db_utils;
use App\Domain\Core\Base\Http\Response\DBJsonResponse;
use App\Domain\Educacao\Escola\Resources\AtendimentoEspecialResource;

class AlunoAtendimentoEspecialController extends Controller
{
    public function getAtendimentosByAluno($aluno, AlunoAtendimentoEspecialRepository $repository)
    {
        $dados = [];

        $dados = $repository->getAtendimentosEspecial($aluno)->map(function ($atendimento) {
            return AtendimentoEspecialResource::toResponse($atendimento);
        });

        return new DBJsonResponse($dados);
    }

    public function getRecursosByAluno($aluno, RecursosAtendimentosRepository $repositoryRecursos)
    {
        $dadosRecursos = [];

        $dadosRecursos = $repositoryRecursos->getRecursosAtendimentos($aluno);
        
        return new DBJsonResponse($dadosRecursos);
    }

    public function deleteAtendimentosByCodigo(
        $codigo,
        AlunoAtendimentoEspecialRepository $repository,
        RecursosAtendimentosRepository $repositoryRecursos
    ) {

        if (!empty($codigo)) {
            $msgSucesso = "Atendimento Especial excluido com sucesso!";
            $repositoryRecursos->deleteRecursoAtendimentos($codigo);
            return new DBJsonResponse($repository->deleteAtendimentoEspecial($codigo), $msgSucesso, 200, false);
        }

        throw new \Exception("Não foi possível excluir!");
    }

    public function updateAtendimentos(
        Request $request,
        AlunoAtendimentoEspecialRepository $repository,
        RecursosAtendimentosRepository $repositoryRecursos
    ) {

        $atendimentos = $request->all();
        $repository->update($atendimentos);
        $repositoryRecursos->update($atendimentos);
        return new DBJsonResponse([], 'Atualização feita feita');
    }

    public function persistAtendimentos(
        Request $request,
        AlunoAtendimentoEspecialRepository $repository,
        RecursosAtendimentosRepository $repositoryRecursos
    ) {
        
        $atendimentos = $request->all();
        $atendimento = $repository->persist($atendimentos);
        $repositoryRecursos->persist($atendimento->ed197_codigo, $atendimentos);
        return new DBJsonResponse([], 'Inclusão feita');
    }



    public function getRecursos(RecursosUtilizadosRepository $repository)
    {
        $dados = [];

        $dados = $repository->getRecursosUtilizados($repository);
        
        return new DBJsonResponse($dados);
    }

    public function deleteRecursoByCodigo($codigo, RecursosUtilizadosRepository $repository)
    {

        if (!empty($codigo)) {
            $msgSucesso = "Atendimento Especial excluido com sucesso!";
    
            return new DBJsonResponse($repository->deleteRecursoUtilizado($codigo), $msgSucesso, 200, false);
        }

        throw new \Exception("Não foi possível excluir!");
    }

    public function persistRecurso(Request $request, RecursosUtilizadosRepository $repository)
    {

        $recursos = $request->all();

        return new DBJsonResponse($repository->persist($recursos));
    }

    public function updateRecurso(Request $request, RecursosUtilizadosRepository $repository)
    {
        
        $recursos = $request->all();

        return new DBJsonResponse($repository->update($recursos));
    }
}
