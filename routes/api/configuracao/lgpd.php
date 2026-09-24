<?php

use Illuminate\Support\Facades\Route;

$controller = '\App\Domain\Configuracao\Lgpd\Controllers\LgpdController@';

Route::get('termos', $controller . 'obterTermos');
Route::post('consentimento', $controller . 'registrarConsentimento');
Route::get('consulta-titular/{cpf}', $controller . 'consultarTitular');

