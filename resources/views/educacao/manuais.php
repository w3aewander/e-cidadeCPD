<?php

use Illuminate\Support\Facades\Route;

$controller = '\App\Domain\Educacao\Manuais\Controllers\ManuaisController@';

Route::get('/', $controller . 'index')->name('educacao.manuais.index')->middleware('legacyAuthenticated');
Route::get('/listar', $controller . 'listar')->name('educacao.manuais.listar')->middleware('legacyAuthenticated');
Route::post('/salvar', $controller . 'salvar')->name('educacao.manuais.salvar')->middleware('legacyAuthenticated');
Route::delete('/excluir/{id}', $controller . 'excluir')->name('educacao.manuais.excluir')->middleware('legacyAuthenticated');
Route::get('/visualizar/{id}', $controller . 'visualizar')->name('educacao.manuais.visualizar')->middleware('legacyAuthenticated');
Route::get('/download/{id}', $controller . 'download')->name('educacao.manuais.download')->middleware('legacyAuthenticated');
