<?php
/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2014  DBSeller Servicos de Informatica
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
require_once(modification("fpdf151/scpdf.php"));
require_once(modification("fpdf151/impcarne.php"));
require_once(modification("libs/db_utils.php"));
$iCodigoOrdem = (int)$_GET["codigo_ordem"];
if (empty($iCodigoOrdem)) {

  db_redireciona("db_erros.php?erro='Codigo da ordem não informado");
  exit;
}
$sqlpref  = "select db_config.*, cgm.z01_incest as inscricaoestadualinstituicao ";
$sqlpref .= "  from db_config                                                     ";
$sqlpref .= " inner join cgm on cgm.z01_numcgm = db_config.numcgm                 ";
$sqlpref .=	"	where codigo = ".db_getsession("DB_instit");

$resultpref        = db_query($sqlpref);
$oDadosInstituicao = db_utils::fieldsMemory($resultpref, 0);

$sqlUsu           = " select nome from db_usuarios where id_usuario = ".db_getsession('DB_id_usuario'); 
$resutlUsu        = db_query($sqlUsu);
$oDadosUsuario    = db_utils::fieldsMemory($resutlUsu, 0);

$oOrdem = new OrdemAuxiliarEmpenho($_GET["codigo_ordem"]);
$pdf    = new scpdf();
$pdf->Open();

$pdf1               = new db_impcarne($pdf, '101');
$pdf1->numordem     = $oOrdem->getCodigo();
$pdf1->prefeitura   = $oDadosInstituicao->nomeinst;
$pdf1->enderpref    = trim($oDadosInstituicao->ender).",".$oDadosInstituicao->numero;
$pdf1->municpref    = $oDadosInstituicao->munic;
$pdf1->uf           = $oDadosInstituicao->uf;
$pdf1->telefpref    = $oDadosInstituicao->telef;
$pdf1->logo		      = $oDadosInstituicao->logo;
$pdf1->emailpref    = $oDadosInstituicao->email;
$pdf1->inscricaoestadualinstituicao    = '';
$pdf1->elemento_despesa = '';
$pdf1->dataempenho  = $oOrdem->getEmpenho()->getDataEmissao();
$pdf1->datanota     = '';
$pdf1->departamento = '';
$pdf1->numeronota   = $oOrdem->getNumeroNotaFiscal();
$pdf1->usuario      = $oDadosUsuario->nome;
$sSqlConsultaEmpenho = " select o56_elemento from orcelemento where o56_codele = {$oOrdem->getEmpenho()->getDesdobramentoEmpenho()}";
$sSqlConsultaEmpenho .= " and o56_anousu = {$oOrdem->getEmpenho()->getAno()}";
$rsDesdobramento = db_query($sSqlConsultaEmpenho);

$sSqlBuscaProcessoAdmin  = "   select pc81_codproc,                                                                                                  "; 
$sSqlBuscaProcessoAdmin .= "          pc11_numero,                                                                                                 ";
$sSqlBuscaProcessoAdmin .= "          pc90_numeroprocesso                                                                                          ";
$sSqlBuscaProcessoAdmin .= "     from empautitem                                                                                                   ";	       
$sSqlBuscaProcessoAdmin .= "          inner join empautitempcprocitem on empautitempcprocitem.e73_autori    = empautitem.e55_autori                ";
$sSqlBuscaProcessoAdmin .= "                                         and empautitempcprocitem.e73_sequen    = empautitem.e55_sequen                ";	       
$sSqlBuscaProcessoAdmin .= "          inner join pcprocitem           on pcprocitem.pc81_codprocitem        = empautitempcprocitem.e73_pcprocitem  ";
$sSqlBuscaProcessoAdmin .= "          inner join solicitem            on solicitem.pc11_codigo              = pcprocitem.pc81_solicitem            ";
$sSqlBuscaProcessoAdmin .= "          left  join solicitaprotprocesso on solicitaprotprocesso.pc90_solicita = solicitem.pc11_numero            		 ";
$sSqlBuscaProcessoAdmin .= "          left  join empempaut            on empempaut.e61_autori               = empautitem.e55_autori                ";
$sSqlBuscaProcessoAdmin .= "          left  join empempenho           on empempenho.e60_numemp              = empempaut.e61_numemp                 ";
$sSqlBuscaProcessoAdmin .= "    where e60_numemp = {$oOrdem->getEmpenho()->getNumero()}                                                                                   ";
$sSqlBuscaProcessoAdmin .= " group by pc81_codproc, pc11_numero, pc90_numeroprocesso                                                                 ";
$rsBuscaProcessoAdmin    = db_query($sSqlBuscaProcessoAdmin);

if ($rsBuscaProcessoAdmin) {
  $pdf1->processo = db_utils::fieldsMemory($rsBuscaProcessoAdmin, 0)->pc81_codproc;
  $pdf1->solicita = db_utils::fieldsMemory($rsBuscaProcessoAdmin, 0)->pc11_numero;
}
$pdf1->fornecimento = ($oOrdem->getSaldo($oOrdem->getEmpenho()) == 0 ? 'TOTAL' : 'PARCIAL');
$pdf1->demonstrativo = $oOrdem->getDemonstrativo($oOrdem->getEmpenho());

if (!$rsDesdobramento || pg_num_rows($rsDesdobramento) == 0) {

  db_redireciona("db_erros.php?erro='Empenho sem desdobramento");
  exit;
}
$pdf1->elemento_despesa = db_formatar(db_utils::fieldsMemory($rsDesdobramento, 0)->o56_elemento, 'elemento');
if ($oOrdem->getDataNota() != '') {
  $pdf1->datanota = $oOrdem->getDataNota()->getDate(DBDate::DATA_PTBR);
}
if ($oOrdem->getDataEntrega() != '') {
  $pdf1->dataentrega = $oOrdem->getDataEntrega()->getDate(DBDate::DATA_PTBR);
}
if ($oOrdem->getDepartamento() != '') {
  $pdf1->departamento = $oOrdem->getDepartamento()->getNomeDepartamento();
}
$pdf1->empenho       = $oOrdem->getEmpenho()->getCodigo()."/".$oOrdem->getEmpenho()->getAnoUso();
$pdf1->valor_extenso = trim(db_formatar($oOrdem->getValor(), 'f'))." (".db_extenso($oOrdem->getValor(), true).")";

$pdf1->dataordem    = $oOrdem->getDataEmissao()->getDate(DBDate::DATA_PTBR);
$pdf1->numcgm       = $oOrdem->getEmpenho()->getCgm()->getCodigo();
$pdf1->nome         = $oOrdem->getEmpenho()->getCgm()->getNome();
$pdf1->email        = $oOrdem->getEmpenho()->getCgm()->getEmail();
$pdf1->cnpj         = '';
$pdf1->cgc          = $oDadosInstituicao->cgc;
$pdf1->url          = $oDadosInstituicao->url;
$pdf1->ender        = $oOrdem->getEmpenho()->getCgm()->getEnderecoPrimario();
$pdf1->munic        = $oOrdem->getEmpenho()->getCgm()->getMunicipio();
$pdf1->bairro       = $oOrdem->getEmpenho()->getCgm()->getBairro();
$pdf1->cep          = $oOrdem->getEmpenho()->getCgm()->getCep();
$pdf1->ufFornecedor = $oOrdem->getEmpenho()->getCgm()->getUf();
$pdf1->numero       = $oOrdem->getEmpenho()->getCgm()->getNumero();
$pdf1->compl        = $oOrdem->getEmpenho()->getCgm()->getComplemento();
$pdf1->contato      = $oOrdem->getEmpenho()->getCgm()->getTelefoneComercial();
$pdf1->telef_cont   = $oOrdem->getEmpenho()->getCgm()->getTelefone();
$pdf1->telef_fax		= $oOrdem->getEmpenho()->getCgm()->getFax();
$pdf1->itens        = $oOrdem->getItens();
$pdf1->emissao      = $oOrdem->getDataEmissao()->getDate(DBDate::DATA_PTBR);
// $pdf1->item	      = 'm52_sequen';
$pdf1->obs            = '';
$pdf1->sTipoCompra    = 'pc50_descr'; //campo do pg_result
$pdf1->empempenho      = 'e60_codemp';
$pdf1->anousuemp       = 'e60_anousu';
$pdf1->quantitem      = 'm52_quant';
$pdf1->condpag        = 'e54_conpag';
$pdf1->destino        = 'e54_destin';

$pdf1->imprime();
$pdf1->objpdf->Output();