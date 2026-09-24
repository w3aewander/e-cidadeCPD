<?php

use Illuminate\Support\Facades\Route;

// Rotas de integracao com Apache Superset BI
Route::post('balancete-receita-recurso/guest-token', '\App\Domain\Financeiro\Contabilidade\Controllers\BiController@gerarGuestTokenBalanceteReceita');

