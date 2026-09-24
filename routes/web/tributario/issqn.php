<?php

use Illuminate\Support\Facades\Route;

Route::get('procedimentos/simples-nacional/novos-estabelecimentos', function () {
    return view("tributario.issqn.procedimentos.simples-nacional.novos-estabelecimentos");
});

Route::get('procedimentos/empresas-optantes-do-simples/atualizacao-de-cadastros', function () {
    return view("tributario.issqn.procedimentos.empresas-optantes-do-simples.atualizacao-de-cadastros");
});

Route::get('procedimentos/simples-nacional/divida-ativa/importar_siples_divida', function () {
    return view("tributario.issqn.procedimentos.simples-nacional.divida-ativa.importar_siples_divida");
});

Route::get('procedimentos/simples-nacional/divida-ativa/processar_siples_divida
', function () {
    return view("tributario.issqn.procedimentos.simples-nacional.divida-ativa.processar_siples_divida
    ");
});

Route::get('procedimentos/simples-nacional/divida-ativa/consulta_siples_divida', function () {
    return view("tributario.issqn.procedimentos.simples-nacional.divida-ativa.consulta_siples_divida");
});

Route::get('massafalida', function () {
    return view("tributario.issqn.procedimentos.massafalida.index");
});

Route::get('procedimentos/cadastro-endereco/inclusao', function () {
    return view("tributario.issqn.procedimentos.cadastro-endereco.inclusao");
});

Route::get('procedimentos/cadastro-endereco/alteracao', function () {
    return view("tributario.issqn.procedimentos.cadastro-endereco.alteracao");
});

Route::get('procedimentos/cadastro-endereco/exclusao', function () {
    return view("tributario.issqn.procedimentos.cadastro-endereco.exclusao");
});

Route::get('cadastros/salao-parceiro/inclusao-salao-parceiro', function () {
    return view("tributario.issqn.cadastros.salao-parceiro.inclusao-salao-parceiro");
});

Route::get('cadastros/salao-parceiro/alterar-salao-parceiro', function () {
    return view("tributario.issqn.cadastros.salao-parceiro.alterar-salao-parceiro");
});

Route::get('cadastros/salao-parceiro/inclusao-deducao-salao', function () {
    return view("tributario.issqn.cadastros.salao-parceiro.inclusao-deducao-salao");
});