<?php
/**
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (c) 2018  DBSeller Servicos de Informatica
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

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once modification('libs/db_usuariosonline.php');
require_once modification('libs/JSON.php');

$retorno = new stdClass();
$retorno->erro = false;
$retorno->mensagem = '';

$parametros = JSON::requestParameters();

db_inicio_transacao();
try {
    switch ($parametros->codigo) {
        
        case null:

            $issbaseendereco = preencheCampos($parametros);
            $retorno->mensagem = $issbaseendereco->incluir(null);

        break;
        
        default:
        
            $issbaseendereco = preencheCampos($parametros);
            $retorno->mensagem = $issbaseendereco->alterar($parametros->codigo);
            
        break;
    }
} catch (Exception $exception) {
    $retorno->erro = true;
    $retorno->mensagem = $exception->getMessage();
}

db_fim_transacao($retorno->erro);

function preencheCampos($parametros) {
    $issbaseendereco = new cl_issbaseendereco;
    $issbaseendereco->q205_inscr = $parametros->inscricao;
    $issbaseendereco->q205_cep = $parametros->cep;
    $issbaseendereco->q205_num = $parametros->numero;
    $issbaseendereco->q205_compl = $parametros->complemento;
    $issbaseendereco->q205_dest = $parametros->destinatario;
    $issbaseendereco->q205_municipal = $parametros->municipal;
    $issbaseendereco->q205_usuario = $parametros->usuario;
    $issbaseendereco->q205_codigo = $parametros->codigo == null? null : $parametros->codigo;

    if ($parametros->municipal == '1') {
        $issbaseendereco->q205_bairro  = $parametros->bairro;
        $issbaseendereco->q205_rua = $parametros->rua;
    } else {
        $issbaseendereco->q205_bairronome  = $parametros->bairro;
        $issbaseendereco->q205_ruanome = $parametros->rua;
    }

    return $issbaseendereco;
}

echo JSON::create()->stringify($retorno);
