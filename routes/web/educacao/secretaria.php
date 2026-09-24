<?php
use Illuminate\Support\Facades\Route;
use \Illuminate\Support\Facades\Request;

// php5.6 artisan route:list --path=web/educacao/secretaria
Route::prefix('relatorios')->group(function () {
    Route::get('alunos/alunos-estrangeiros', function (Request $request) {
        return view("educacao.secretaria.relatorios.alunos.alunos-estrangeiros");
    });
});


Route::prefix('cadastros')->group(function () {
    Route::get('cursos/crud', function (Request $request) {
        return view("educacao.secretaria.cadastros.cursos.crud-cursos");
    });
    Route::get('composicao-curricular/crud', function (Request $request) {
        return view("educacao.secretaria.cadastros.composicao-curricular.crud-unidades-curriculares");
    });
    Route::get('tabelas/recursos-utilizados-aee', function (Request $request) {
        return view("educacao.secretaria.cadastros.tabelas.recursos-utilizados-aee");
    });

    Route::get('tipos-instrumentos-avaliativos/crud', function (Request $request) {
        return view("educacao.secretaria.cadastros.tipos-instrumentos-avaliativos.crud-tipos-instrumentos-avaliativos");
    });
});

Route::get('procedimentos/acompanhamento-alunos-pcd', function (Request $request) {
    return view("educacao.escola.procedimentos.diario-classe.acompanhamento-alunos-pcd");
});

Route::get('cadastros/tabelas/recursos-utilizados-aee', function (Request $request) {
    return view("educacao.secretaria.cadastros.tabelas.recursos-utilizados-aee");
});
