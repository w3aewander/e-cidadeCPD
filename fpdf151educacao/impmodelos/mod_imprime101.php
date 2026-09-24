<?php
global $resparag, $resparagpadrao, $db61_texto, $db02_texto;

$dist = 4;
$this->objpdf->SetAutoPageBreak(false);
$this->objpdf->AliasNbPages();
$this->objpdf->AddPage();
$this->objpdf->settopmargin(10);
$this->objpdf->setleftmargin(4);
$pagina = 1;
$xlin = 20;
$xcol = 4;

$this->objpdf->setfillcolor(245);
$this->objpdf->rect($xcol-2,$xlin-18,206,292,2,'DF','1234');
$this->objpdf->setfillcolor(255,255,255);
$this->objpdf->Setfont('Arial','B',6);
$this->objpdf->text(130,$xlin-15,'ORDEM DE COMPRA N'.CHR(176));
$this->objpdf->text(185,$xlin-15,db_formatar($this->numordem,'s','0',6,'e'));
$this->objpdf->text(130,$xlin-12,'DATA :');
$this->objpdf->text(185,$xlin-12,$this->dataordem);
$this->objpdf->text(130,$xlin-9,'DEPARTAMENTO :');
$this->objpdf->text(185,$xlin-9,$this->departamento);
$this->objpdf->text(130,$xlin-6,'DATA EMPENHO :');
$this->objpdf->text(185,$xlin-6,db_formatar($this->dataempenho,'d'));
$this->objpdf->text(130,$xlin-3,'NOTA FISCAL :');
$this->objpdf->text(185,$xlin-3, $this->numeronota);
$this->objpdf->text(130,$xlin,'DATA NOTA :');
$this->objpdf->text(185,$xlin, $this->datanota);
$this->objpdf->text(130,$xlin+3,'SOLICITAÇÃO :');
$this->objpdf->text(185,$xlin+3, $this->solicita);
$this->objpdf->text(130,$xlin+6,'DATA DE ENTREGA :');
$this->objpdf->text(185,$xlin+6, $this->dataentrega);


$this->objpdf->Setfont('Arial','B',9);
$this->objpdf->Image('imagens/files/'.$this->logo,15,$xlin-17,12);
$this->objpdf->Setfont('Arial','B',9);
$this->objpdf->text(40,$xlin-15,$this->prefeitura);
$this->objpdf->Setfont('Arial','',9);
$this->objpdf->text(40,$xlin-11,$this->enderpref);
$this->objpdf->text(40,$xlin- 7,"FONE: " . $this->telefpref);
$this->objpdf->text(40,$xlin- 3,$this->emailpref);
$this->objpdf->text(40,$xlin+1 ,$this->url . " - CNPJ:" . db_formatar($this->cgc,'cnpj'));

$xlin = $xlin + 5;
$this->objpdf->rect($xcol,$xlin+2,$xcol+198,20,2,'DF','1234');
$this->objpdf->Setfont('Arial','',6);
$this->objpdf->text($xcol+2,$xlin+4.5,'Dados do Fornecedor');
$this->objpdf->Setfont('Arial','B',8);
$this->objpdf->text($xcol+110,$xlin+5,'E-mail');
$this->objpdf->text($xcol+110,$xlin+8.5,'Numcgm');
$this->objpdf->text($xcol+150,$xlin+8.5,(strlen($this->cnpj) == 11?'CPF':'CNPJ'));
$this->objpdf->text($xcol+  2,$xlin+8.5,'Nome');
$this->objpdf->text($xcol+  2,$xlin+12.5,'Endereço');
$this->objpdf->text($xcol+110,$xlin+12.5,'Número');
$this->objpdf->text($xcol+150,$xlin+12.5,'Complemento');
$this->objpdf->text($xcol+  2,$xlin+16,'Município');
$this->objpdf->text($xcol+110,$xlin+16,'Bairro');
$this->objpdf->text($xcol+150,$xlin+16,'CEP');
$this->objpdf->text($xcol+  2,$xlin+20,'Contato');
$this->objpdf->text($xcol+110,$xlin+20,'Telefone');
$this->objpdf->text($xcol+150,$xlin+20,'FAX');
$this->objpdf->Setfont('Arial','',8);
$this->objpdf->text($xcol+122,$xlin+5,':  '.$this->email);
$this->objpdf->text($xcol+158,$xlin+8.5,':  '.$this->cnpj);
$this->objpdf->text($xcol+122,$xlin+8.5,':  '.$this->numcgm);
$this->objpdf->text($xcol+18,$xlin+ 8.5,':  '.$this->nome);
$this->objpdf->text($xcol+18,$xlin+ 12.5,':  '.$this->ender);
$this->objpdf->text($xcol+122,$xlin+12.5,':  '.$this->numero);
$this->objpdf->text($xcol+170,$xlin+12.5,':  '.$this->compl);
$this->objpdf->text($xcol+18,$xlin+ 16,':  '.$this->munic.'-'.$this->ufFornecedor);
$this->objpdf->text($xcol+122,$xlin+16,':  '.$this->bairro);
$this->objpdf->text($xcol+165,$xlin+16,':  '.$this->cep);
$this->objpdf->text($xcol+18,$xlin+ 20,':  '.$this->contato);
$this->objpdf->text($xcol+122,$xlin+20,':  '.$this->telef_cont);
$this->objpdf->text($xcol+158,$xlin+20,':  '.$this->telef_fax);


$this->objpdf->sety($xlin+ 24);
$xlin +=10;
$sTexto = "O material constante da nota fiscal anexa a este formulário e abaixo relacionada foi entregue ";
$sTexto .="e esta de acordo com o pedido da prefeitura, podendo ser providenciado o pagamento no valor de:";
$sTexto .= "R$ {$this->valor_extenso}";
$this->objpdf->multicell(202, 3, $sTexto, 0);
$this->objpdf->Setfont('Arial','B',8);
$this->objpdf->rect($xcol    ,$xlin+24,12,6,2,'DF','12');
$this->objpdf->rect($xcol+ 12,$xlin+24,17,6,2,'DF','12');
$this->objpdf->rect($xcol+ 29,$xlin+24,13,6,2,'DF','12');//$this->objpdf->rect($xcol+ 27,$xlin+24,11,6,2,'DF','12');
$this->objpdf->rect($xcol+ 42,$xlin+24,101,6,2,'DF','12');//$this->objpdf->rect($xcol+ 38,$xlin+24,104,6,2,'DF','12');
$this->objpdf->rect($xcol+143,$xlin+24,30,6,2,'DF','12');
$this->objpdf->rect($xcol+173,$xlin+24,30,6,2,'DF','12');

$this->objpdf->rect($xcol    ,$xlin+30,12,175  -$xlin ,2,'DF','34');
$this->objpdf->rect($xcol+ 12,$xlin+30,17,175  -$xlin ,2,'DF','34');
$this->objpdf->rect($xcol+ 29,$xlin+30,13,175  -$xlin ,2,'DF','34'); //$this->objpdf->rect($xcol+ 27,$xlin+30,11,205  -$xlin ,2,'DF','34');
$this->objpdf->rect($xcol+ 42,$xlin+30,101,175 -$xlin ,2,'DF','34'); //$this->objpdf->rect($xcol+ 38,$xlin+30,104,205 -$xlin ,2,'DF','34');
$this->objpdf->rect($xcol+143,$xlin+30,30,175  -$xlin ,2,'DF','');
$this->objpdf->rect($xcol+173,$xlin+30,30,175  -$xlin ,2,'DF','34');

$this->objpdf->sety($xlin+28);
$alt = 4;

$this->objpdf->text($xcol+   2,$xlin+28,'ITEM');
$this->objpdf->text($xcol+12.5,$xlin+28,'EMPENHO');
$this->objpdf->text($xcol+30.5,$xlin+28,'QUANT');  //$this->objpdf->text($xcol+27.5,$xlin+28,'QUANT');
$this->objpdf->text($xcol+  67,$xlin+28,'MATERIAL OU SERVIÇO'); //$this->objpdf->text($xcol+  70,$xlin+28,'MATERIAL OU SERVIÇO');
$this->objpdf->text($xcol+ 145,$xlin+28,'VALOR UNITÁRIO');
$this->objpdf->text($xcol+ 176,$xlin+28,'VALOR TOTAL');
$maiscol = 0;

$this->objpdf->setfillcolor(0,0,0);

$this->objpdf->Setfont('Arial','',5);
$this->objpdf->text($xcol,$xlin+243, $this->usuario);
$this->objpdf->text($xcol+26,$xlin+243, $this->fornecimento);
$this->objpdf->text($xcol+51,$xlin+243, $this->processo);

$this->objpdf->Setfont('Arial','',6);
$this->objpdf->line($xcol,$xlin+227, 205, $xlin+227);
$this->objpdf->line($xcol+102.5, $xlin+227,$xcol+102.5, $xlin+257);
$this->objpdf->text($xcol,$xlin+230, 'Observação');
$this->objpdf->text($xcol+132,$xlin+236, 'Carimbo e Assinatura');
$this->objpdf->line($xcol,$xlin+237, 205, $xlin+237);
$this->objpdf->text($xcol,$xlin+240, 'Recepção de Material');
$this->objpdf->line($xcol+25,$xlin+237, $xcol+25, $xlin+247);
$this->objpdf->text($xcol+26,$xlin+240, 'Fornecimento');
$this->objpdf->line($xcol+50,$xlin+237, $xcol+50, $xlin+247);
$this->objpdf->text($xcol+51,$xlin+240, 'Pc. Compra');
$this->objpdf->line($xcol+75,$xlin+237, $xcol+75, $xlin+247);
$this->objpdf->text($xcol+76,$xlin+240, 'Proc. Pagto');
$this->objpdf->text($xcol+103,$xlin+240, 'Elemento Despesa');
$this->objpdf->text($xcol+103,$xlin+243, $this->elemento_despesa);
$this->objpdf->line($xcol+130,$xlin+237, $xcol+130, $xlin+247);
$iAlturaAtual = $this->objpdf->getY();
$this->objpdf->SetXY($xcol+130, $xlin+238);
$this->objpdf->multicell(70, 2, "Somente será admitido a 1º Via da NF.\nAnexa a 1º via NEse fornecimento for total. PORT ARlA n°. 013176 - SP");
$this->objpdf->setY($iAlturaAtual);

$this->objpdf->line($xcol,$xlin+247, 205, $xlin+247);
$this->objpdf->line($xcol,$xlin+257, 205, $xlin+257);
$this->objpdf->line($xcol,$xlin+277, 205, $xlin+277);
$this->objpdf->text($xcol,$xlin+250, 'Funcional:');
$this->objpdf->text($xcol+103,$xlin+250, 'Categoria Econômica: '. $this->elemento_despesa);
//$this->objpdf->text($xcol+10,$xlin+217,strtoupper($this->municpref).', '.substr($this->emissao,8,2).' DE '. strtoupper(db_mes(substr($this->emissao,3,2))).' DE '.substr($this->emissao,6,4).'.');
//$this->objpdf->text($xcol+ 120,$xlin+217,'___________________________________________');

$this->objpdf->SetWidths(array(12,16,13,101,30,30));  //$this->objpdf->SetWidths(array(12,16,10,104,30,30));
$this->objpdf->SetAligns(array('C','C','R','L','R','R'));

$this->objpdf->setleftmargin(4);
$this->objpdf->sety($xlin+32);

$xtotal    = 0;
$item      = 1;
$iVoltaImp = 0;

foreach ($this->itens as $oItem) {


  $this->objpdf->Setfont('Arial','',7);

  $descricaoitem = $oItem->getItemAlmoxarifado()->getDescricao();


  $obsitem = "";

  //// troca de pagina
  if( ($this->objpdf->gety() > $this->objpdf->h - 90 && $pagina == 1 )
    || ( $this->objpdf->gety() > $this->objpdf->h - 50 && $pagina != 1 )) {

    $this->objpdf->Setfont('Arial','B',7);
    if ($this->objpdf->PageNo() == 1) {

      if ($this->obs!="") {

        $this->objpdf->text(90,268-$xlin,'Continua na Página '.($pagina+1));
      } else {
        $this->objpdf->text(90,$xlin+243,'Continua na Página '.($pagina+1));
      }
    } else {
      $this->objpdf->text(110,$xlin+320,'Continua na Página '.($pagina+1));
    }
    if ($pagina == 1) {

      $xlin = 20;
      $xcol = 4;
      $this->objpdf->rect($xcol,    $xlin+205,143, 10,2,'DF','34');
      $this->objpdf->rect($xcol+143,$xlin+205,30, 10,2,'DF','34');
      $this->objpdf->rect($xcol+173,$xlin+205,30, 10,2,'DF','34');
      $this->objpdf->text($xcol+100 ,$xlin+211,'T O T A L   D A   P Á G I N A');

      $this->objpdf->SetXY(173,$xlin+205);
      $this->objpdf->cell(30 ,10,db_formatar($xtotal,'f'),0,0,"R");

      $this->objpdf->SetXY(4,$xlin+217);

      if (isset($texto2) && trim($texto2) != ""){
        $this->objpdf->multicell(202,4,$texto2,1);
      }
    }
    $this->objpdf->addpage();
    $pagina += 1;

    $this->objpdf->settopmargin(1);
    $xlin = 20;
    $xcol = 4;

    $this->objpdf->setfillcolor(245);
    $this->objpdf->rect($xcol-2,$xlin-18,206,292,2,'DF','1234');
    $this->objpdf->setfillcolor(255,255,255);
    $this->objpdf->Setfont('Arial','B',9);
    $this->objpdf->text(130,$xlin-13,'ORDEM DE COMPRA N'.CHR(176));
    $this->objpdf->text(185,$xlin-13,db_formatar($this->numordem,'s','0',6,'e'));
    $this->objpdf->Image('imagens/files/'.$this->logo,15,$xlin-17,12);
    $this->objpdf->Setfont('Arial','B',9);
    $this->objpdf->text(40,$xlin-15,$this->prefeitura);
    $this->objpdf->Setfont('Arial','',9);
    $this->objpdf->text(40,$xlin-11,$this->enderpref);
    $this->objpdf->text(40,$xlin-8,$this->municpref);
    $this->objpdf->text(40,$xlin-5,$this->telefpref);
    $this->objpdf->text(40,$xlin-2,$this->emailpref);

    $xlin = -30;
    $this->objpdf->Setfont('Arial','B',8);

    $this->objpdf->rect($xcol    ,$xlin+54,12,6,2,'DF','12');
    $this->objpdf->rect($xcol+ 12,$xlin+54,17,6,2,'DF','12');
    $this->objpdf->rect($xcol+ 29,$xlin+54,13,6,2,'DF','12'); //$this->objpdf->rect($xcol+ 27,$xlin+54,11,6,2,'DF','12');
    $this->objpdf->rect($xcol+ 42,$xlin+54,101,6,2,'DF','12'); //$this->objpdf->rect($xcol+ 38,$xlin+54,104,6,2,'DF','12');
    $this->objpdf->rect($xcol+143,$xlin+54,30,6,2,'DF','12');
    $this->objpdf->rect($xcol+173,$xlin+54,30,6,2,'DF','12');

    $this->objpdf->rect($xcol,    $xlin+60,12,252,2,'DF','34');
    $this->objpdf->rect($xcol+ 12,$xlin+60,17,252,2,'DF','34');
    $this->objpdf->rect($xcol+ 29,$xlin+60,13,252,2,'DF','34'); //$this->objpdf->rect($xcol+ 27,$xlin+60,11,252,2,'DF','34');
    $this->objpdf->rect($xcol+ 42,$xlin+60,101,252,2,'DF','34'); //$this->objpdf->rect($xcol+ 38,$xlin+60,104,252,2,'DF','34');
    $this->objpdf->rect($xcol+143,$xlin+60,30,252,2,'DF','');
    $this->objpdf->rect($xcol+173,$xlin+60,30,252,2,'DF','34');

    $this->objpdf->sety($xlin+66);
    $alt = 4;

    $this->objpdf->text($xcol+   2,$xlin+59,'ITEM');
    $this->objpdf->text($xcol+12.5,$xlin+59,'EMPENHO');
    $this->objpdf->text($xcol+30.5,$xlin+59,'QUANT');
    $this->objpdf->text($xcol+  70,$xlin+59,'MATERIAL OU SERVIÇO');
    $this->objpdf->text($xcol+ 145,$xlin+59,'VALOR UNITÁRIO');
    $this->objpdf->text($xcol+ 176,$xlin+59,'VALOR TOTAL');
    $this->objpdf->text($xcol+  43,$xlin+63,'Continuação da Página '.($pagina-1));
    $this->objpdf->Setfont('Arial','',8);

    $maiscol = 0;
  }
  $controle = $item;
  $controle++;

  // Pega o ultimo item da pagina e testa se consegue imprimir tudo na mesma pagina
  if (($controle%7)==0) {

    if (strlen($obsitem) > 68) {
      $obsitem = substr($obsitem,0,68)." ...";
    }
  }

  if ($iVoltaImp == 0) {

    $this->objpdf->Row_multicell(array($oItem->getItemAlmoxarifado()->getCodigo(),
      $this->empenho,
      $oItem->getQuantidade(),
      $descricaoitem . "\n",
      db_formatar($oItem->getValorUnitario(),'v'," ",2),
      db_formatar($oItem->getValorTotal(),'f')),3,false,4,0,true);
    $xtotal += $oItem->getValorTotal();
  }else if ($iVoltaImp == 1){
    $sObsItem = $sTextoaImprimir; //resto do texto
  }

  if ((isset($sObsItem) && $obsitem != '' && $iVoltaImp == 0) || $iVoltaImp == 1){

    $this->objpdf->Setfont('Arial','',8);
    $iAlturaFinal = 80;
    if ($pagina != 1) {
      $iAlturaFinal = 30;
    }

    // Largura total do multicell
    $iWidthMulticell  = $this->objpdf->widths[4];

    // Consulta o total de linhas restantes
    $iLinhasRestantes = ((( $this->objpdf->h - 25 ) - $this->objpdf->GetY()) / $dist );

    // Consulta o total de linhas que será utilizado no multicelll
    $iLinhasMulticell = $this->objpdf->NbLines($iWidthMulticell,$sObsItem);

    // Verifica se o total de linhas utilizadas no multicell é maior que as linhas restantes
    if ( $iLinhasMulticell > $iLinhasRestantes ) {

      // Total de carateres necessários para a impressão até o fim da página
      $iTotalCaract = ( $iWidthMulticell * $iLinhasRestantes );
      $iLimitString = $iTotalCaract;

      // Percorre o resumo do limite de caraceters até um ponto que haja espaço em branco para não quebre alguma palavra
      for ($iInd = $iTotalCaract; $iInd < strlen($sObsItem); $iInd++) {
        if ( $sObsItem{$iInd} == ' ') {
          $iLimitString = $iInd;
          break;
        }
      }

      // Insere quebra no ponto informado
      $sObsItem = substr($sObsItem,0,$iLimitString)."\n".substr($sObsItem,$iLimitString,strlen($sObsItem));
    }

    $sObsItem = $this->objpdf->Row_multicell(array('','','',stripslashes($sObsItem),'',''),
      3,
      false,
      5,
      0,
      true,
      true,
      3,
      ($this->objpdf->h - $iAlturaFinal)
    );
    if ($sObsItem != "") {

      $iVoltaImp       = 1;
      $sTextoaImprimir = $sObsItem;
      $ii--;
    } else {
      $iVoltaImp = 0;
    }
  }

  $item++;

}

if($pagina == 1){

  $xlin = 20;
  $xcol = 4;
  $this->objpdf->rect($xcol,$xlin+175,143, 10,2,'DF','34');
  $this->objpdf->rect($xcol+143,$xlin+175,30, 10,2,'DF','34');
  $this->objpdf->rect($xcol+173,$xlin+175,30, 10,2,'DF','34');
  $this->objpdf->text($xcol+100,$xlin+181,'T O T A L   G E R A L');

  $this->objpdf->SetXY(173,$xlin+175);
  $this->objpdf->cell(30 ,10,db_formatar($xtotal,'f'),0,0,"R");

  $this->objpdf->setfillcolor(220);
  $this->objpdf->rect($xcol,$xlin+195,203, 45,'FD');
  $this->objpdf->text($xcol+87,$xlin+200,'D E M O N S T R A T I V O ');
  $this->objpdf->Setfont('Arial','',6);
  $this->objpdf->text($xcol+2,$xlin+205,'Tipo');
  $this->objpdf->text($xcol+20,$xlin+205,'N º.');
  $this->objpdf->text($xcol+40,$xlin+205,'Data Doc.');
  $this->objpdf->text($xcol+60,$xlin+205,'NRM.');
  $this->objpdf->text($xcol+90,$xlin+205,'Débito');
  $this->objpdf->text($xcol+130,$xlin+205,'Crédito');
  $this->objpdf->text($xcol+170,$xlin+205,'Saldo');

  $this->objpdf->SetXY($xcol,$xlin+206);
  $oTotalCredito = 0;
  $oTotalDebito  = 0;
  foreach ($this->demonstrativo as $oDemonstrativo) {

    $this->objpdf->SetX($xcol+1);
    $this->objpdf->cell(10, 4, $oDemonstrativo->tipo             , 0, 0, "L", 0);
    $this->objpdf->cell(20, 4, $oDemonstrativo->numero             , 0, 0, "R", 0);
    $this->objpdf->cell(20, 4, db_formatar($oDemonstrativo->data,"d"), 0, 0, "R", 0);
    $this->objpdf->cell(15, 4, $oDemonstrativo->nrm             , 0, 0, "R", 0);
    $this->objpdf->cell(40, 4, db_formatar($oDemonstrativo->debito,"f")             , 0, 0, "R", 0);
    $this->objpdf->cell(40, 4, db_formatar($oDemonstrativo->credito,"f")             , 0, 0, "R", 0);
    $this->objpdf->cell(50, 4, db_formatar($oDemonstrativo->saldo,"f")             , 0, 1, "R", 0);
    $oTotalDebito  += $oDemonstrativo->debito;
    $oTotalCredito += $oDemonstrativo->credito;
  }
    $this->objpdf->ln(3);
    $this->objpdf->SetX($xcol+1);
     $this->objpdf->Setfont('Arial','B',6);
    $this->objpdf->cell(10, 4, "", 'TB', 0, "L", 0);
    $this->objpdf->cell(20, 4, "", 'TB', 0, "R", 0);
    $this->objpdf->cell(20, 4, "", 'TB', 0, "R", 0);
    $this->objpdf->cell(15, 4, "Saldo", "TB", 0,"C", 0);
    $this->objpdf->cell(40, 4, db_formatar($oTotalDebito,"f")  , "TB", 0, "R", 0);
    $this->objpdf->cell(40, 4, db_formatar($oTotalCredito,"f") , "TB", 0, "R", 0);
    $this->objpdf->cell(50, 4, db_formatar($oDemonstrativo->saldo,"f") ,"TB", 1, "R", 0);
  $this->objpdf->SetXY(4,$xlin+217);

  if (isset($texto2) && trim($texto2) != ""){
    $this->objpdf->multicell(202,4,$texto2,1);
  }
}else{

  $this->objpdf->rect($xcol    ,$xlin+312,12,10,2,'DF','34');
  $this->objpdf->rect($xcol+ 12,$xlin+312,17,10,2,'DF','34');
  $this->objpdf->rect($xcol+ 29,$xlin+312,13,10,2,'DF','34');
  $this->objpdf->rect($xcol+ 42,$xlin+312,101,10,2,'DF','34'); //$this->objpdf->rect($xcol+ 38,$xlin+312,104,10,2,'DF','34');
  $this->objpdf->rect($xcol+143,$xlin+312,30,10,2,'DF','34');
  $this->objpdf->rect($xcol+173,$xlin+312,30,10,2,'DF','34');

  $this->objpdf->text($xcol+100 ,$xlin+319,'T O T A L   G E R A L');
  $this->objpdf->text($xcol+173 ,$xlin+319,db_formatar($xtotal,'f'));
}
$posicao_depois=$this->objpdf->gety();
//$xlin+=$posicao_depois-$posicao_atual+2;
