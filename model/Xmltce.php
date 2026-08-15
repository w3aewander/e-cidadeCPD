<?php
//error_reporting(E_ALL); 
//ini_set('display_errors', '1');
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2009  DBselller Servicos de Informatica             
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



require_once ('model/PadArquivoEscritor.model.php');

class Xmltce extends PadArquivoEscritor {
  
  
  
  
  public function criarArquivo(iPadArquivoBase $oArquivo) {    
    $oXmlWriter = new XMLWriter();

    $oXmlWriter->openMemory();
    $oXmlWriter->setIndent(true);
    //$oXmlWriter->startDocument('1.0','ISO-8859-1');
    //$oXmlWriter->startDocument('1.0','UTF-8');
    $oXmlWriter->endDtd();
    //$oXmlWriter->startElement($oArquivo->getNomeArquivo());
    $oXmlWriter->startElement($oArquivo->nomexml());
    //$oXmlWriter->addAttribute("tipo", "teste");
    $oXmlWriter->writeAttribute("xsi:noNamespaceSchemaLocation", "schema.xsd");
    $oXmlWriter->writeAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");    
    $oXmlWriter->startElement($oArquivo->abretag());

    foreach ($oArquivo->getDados() as $oLinha) {      
      $oXmlWriter->startElement("{$oArquivo->segundatag()}");
      foreach ($oArquivo->getNomeElementos() as $sElemento) {
        $oXmlWriter->startElement($sElemento);        
        $oXmlWriter->text(utf8_encode($oLinha->$sElemento));        
        $oXmlWriter->endElement();
      }
      $oXmlWriter->endElement();
    }    
    $oXmlWriter->endElement();
    $oXmlWriter->endElement();
    //$sNomeArquivo = "tmp/{$oArquivo->getNomeArquivo()}.{$oArquivo->getOutput()}";
    //$sNomeArquivo = "tmp/{$oArquivo->nomexml()}.{$oArquivo->getOutput()}";
    $sNomeArquivo = "tmp/{$oArquivo->nomexml()}.xml";
    $rsArquivoXML = fopen($sNomeArquivo, "w");
    fputs($rsArquivoXML, $oXmlWriter->outputMemory());
    fclose($rsArquivoXML);
    unset($oXmlWriter);
    return $sNomeArquivo;
  }
  
}


/*
["codigolinha"]=> int(5008) 
["cd_SubPrograma"]=> string(4) "1111" 
["de_SubPrograma"]=> string(17) "SANEAMENTO BÝSICO" 
["cd_Unidade"]=> string(4) "0101" 
["dt_Ano"]=> string(4) "2022" 
["de_Objetivo"]=> string(565) "(fertilizante, biogás e outros)." 
["vl_SubPrograma"]=> string(10) "5509347.01" 


$oXmlWriter = new XMLWriter();
$oXmlWriter->openMemory();
$oXmlWriter->setIndent(true);
$oXmlWriter->startDocument('1.0', 'ISO-859-1');
$oXmlWriter->endDtd();
$oXmlWriter->startElement('despesas');
$oXmlWriter->startElement('despesa');
$oXmlWriter->writeAttribute("despesatce", $oParam->despesatce);
$oXmlWriter->writeAttribute("despesaecidade", $oParam->despesa);
$oXmlWriter->endElement();
$oXmlWriter->endElement();

$strBuffer = $oXmlWriter->outputMemory();
$rsXML     = fopen('config/sigfis/vinculodespesa.xml', 'w');

fputs($rsXML, $strBuffer);
fclose($rsXML);
*/

?>