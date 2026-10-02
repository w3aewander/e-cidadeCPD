<?php

$controller = '\App\Domain\Educacao\Escola\Controllers\PautaEletronicaMobileController@';

Route::get('/status', $controller . 'status');
Route::get('/versao-app', $controller . 'getVersaoApp');
Route::get('/anos-letivos', $controller . 'getAnosLetivos');
Route::post('/login', $controller . 'login');
Route::get('/turmas', $controller . 'getTurmas');
Route::get('/turma/{turmaId}/alunos', $controller . 'getAlunosTurma');
Route::get('/alunos', $controller . 'getAlunosCompat');
Route::get('/frequencias', $controller . 'getFrequencias');
Route::post('/frequencia/sincronizar', $controller . 'sincronizarFrequencia');
Route::post('/sincronizar/frequencia', $controller . 'sincronizarFrequencia');
Route::post('/aulas/sincronizar', $controller . 'sincronizarAulas');
Route::post('/sincronizar/aulas', $controller . 'sincronizarAulas');
Route::get('/turma/{turmaId}/avaliacoes', $controller . 'getAvaliacoesTurma');
Route::post('/avaliacoes/sincronizar', $controller . 'sincronizarAvaliacoes');
Route::post('/sincronizar/notas', $controller . 'sincronizarAvaliacoes');
