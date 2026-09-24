<?php
/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2009  DBSeller Servicos de Informatica
 *                            www.dbseller.com.br
 *                         e-cidade@dbseller.com.br
 *
 *  Este programa e software livre; voce pode redistribui-lo e/ou
 *  modifica-lo sob os termos da Licenca Publica Geral GNU, conforme
 *  publicada pela Free Software Foundation; tanto a versao 2 da
 *  Licenca como (a seu criterio) qualquer versao mais nova.
 *
 *  Este programa e distribuido na expectativa de ser util, mas SEM
 *  QUALQUER GARANTIA; sem mesmo a garantia implicita de
 *  COMERCIALIZACAO ou de ADEQUACAO A QUALQUER PROPOSITO EM
 *  PARTICULAR. Consulte a Licenca Publica Geral GNU para obter mais
 *  detalhes.
 *
 *  Voce deve ter recebido uma copia da Licenca Publica Geral GNU
 *  junto com este programa; se nao, escreva para a Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
 *  02111-1307, USA.
 *
 *  Copia da licenca no diretorio licenca/licenca_en.txt
 *                                licenca/licenca_pt.txt
 */

use Illuminate\Support\Facades\Route;
use \Illuminate\Support\Facades\Request;

// php artisan route:list --path=web/educacao/transporte-escolar

Route::prefix('relatorios')->group(function (){
   Route::get('/emissao-carteira', function(Request $request) {
       return view('educacao.transporte-escolar.emissao-carteira-estudante');
   });
});

Route::prefix('procedimentos')->group(function (){
    Route::prefix('sete/exportar')->group(function (){
        Route::get('/exportacao-aluno', function(Request $request) {
            return view('educacao.transporte-escolar.sete.exportacao-aluno');
        });                      
    });
 });
