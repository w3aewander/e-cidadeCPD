<?php

$controller = '\App\Domain\Educacao\Escola\Controllers\PautaEletronicaMobileController@';

Route::get('/status', $controller . 'status');
Route::post('/login', $controller . 'login');
Route::get('/turmas', $controller . 'getTurmas');
Route::get('/turma/{turmaId}/alunos', $controller . 'getAlunosTurma');
Route::get('/frequencias', $controller . 'getFrequencias');
Route::post('/frequencia/sincronizar', $controller . 'sincronizarFrequencia');
Route::get('/turma/{turmaId}/avaliacoes', $controller . 'getAvaliacoesTurma');
Route::post('/avaliacoes/sincronizar', $controller . 'sincronizarAvaliacoes');
