<?php
  
  $this->objpdf->AliasNbPages();
  $this->objpdf->settopmargin(1);

  $ylin  = 20;
  $this->objpdf->AddPage();
       

  $xcol  = 4;
  $cinza = 225;
    $this->objpdf->setfillcolor(225);
    $this->objpdf->roundedrect($xcol-2,$ylin-18,206,168,2,'DF','1234');
    $this->objpdf->setfillcolor(255,255,255);

    $this->objpdf->Setfont('Arial','B',11);
    $this->objpdf->text(130,$ylin-13,'RECIBO DE PAGAMENTO');
    $this->objpdf->text(130,$ylin-8,'REF. AO MÊS '.db_formatar($this->mes,'s','0',2,'e',0).'/'.$this->ano);
    $this->objpdf->text(130,$ylin-3,$this->qualarquivo);
    
    $this->objpdf->Image('imagens/files/'.$this->logo,15,$ylin-17,12); //.$this->logo
    $this->objpdf->Setfont('Arial','B',9);
    $this->objpdf->text(30,$ylin-15,$this->prefeitura);
    $this->objpdf->Setfont('Arial','',7);
    $this->objpdf->text(30,$ylin-12,$this->enderpref);
    $this->objpdf->text(30,$ylin- 9,$this->municpref);
    $this->objpdf->text(30,$ylin- 6,$this->telefpref);
    $this->objpdf->text(30,$ylin- 3,db_formatar($this->cgcpref,'cnpj'));
  
    ///retangulo da assinatura
    $this->objpdf->Roundedrect($xcol+178,$ylin+14,$xcol+20,133,2,'DF','1234');

    //retangulo onde fica no nome do funcionario
    $this->objpdf->Roundedrect($xcol,$ylin,$xcol+198,12,2,'DF','1234');

    $this->objpdf->Roundedrect($xcol,$ylin+14,$xcol+172,62,2,'DF','1234');
    $this->objpdf->Roundedrect($xcol,$ylin+77,$xcol+172,42,2,'DF','1234'); // base
    $this->objpdf->Roundedrect($xcol,$ylin+119,$xcol+172,28,2,'DF','1234');
    $this->objpdf->line($xcol,$ylin+22,$xcol+176,$ylin+22);
    $this->objpdf->line($xcol,$ylin+138,$xcol+176,$ylin+138);
    $this->objpdf->line($xcol+130,$ylin+128,$xcol+176,$ylin+128);
    
    $this->objpdf->line($xcol+153,$ylin+14,$xcol+153,$ylin+76);
    $this->objpdf->line($xcol+130,$ylin+14,$xcol+130,$ylin+76);
    $this->objpdf->line($xcol+115,$ylin+14,$xcol+115,$ylin+76);
    $this->objpdf->line($xcol+15,$ylin+14,$xcol+15,$ylin+76);

    
    $this->objpdf->line($xcol,$ylin+85,$xcol+176,$ylin+85);
    $this->objpdf->line($xcol+153, $ylin+77, $xcol+153, $ylin+138);  
    $this->objpdf->line($xcol+130, $ylin+77, $xcol+130, $ylin+138);  
    $this->objpdf->line($xcol+115, $ylin+77, $xcol+115, $ylin+119);  
    $this->objpdf->line($xcol+15,  $ylin+77, $xcol+15,  $ylin+119); 

    $this->objpdf->Setfont('Arial','',6);
    $this->objpdf->text($xcol+2,$ylin+3,'Matrícula:');
    $this->objpdf->Setfont('Arial','B',7);
    $this->objpdf->text($xcol+12,$ylin+3,$this->registro);

    $this->objpdf->Setfont('Arial','',6);
    $this->objpdf->text($xcol+25,$ylin+3,'Nome:');
    $this->objpdf->Setfont('Arial','B',7);
    $this->objpdf->text($xcol+33,$ylin+3,$this->nome);

    $this->objpdf->Setfont('Arial','',6);
    $this->objpdf->text($xcol+100,$ylin+3,'Função:');
    $this->objpdf->Setfont('Arial','B',7);
    $this->objpdf->text($xcol+110,$ylin+3,$this->descr_funcao);

    $this->objpdf->Setfont('Arial','',6);
    $this->objpdf->text($xcol+170,$ylin+3,'Padrão:');
    $this->objpdf->Setfont('Arial','B',7);
    $this->objpdf->text($xcol+180,$ylin+3,$this->padrao);

    $this->objpdf->Setfont('Arial','',6);
    $this->objpdf->text($xcol+2,$ylin+7,'Lotação:');
    $this->objpdf->Setfont('Arial','B',7);
    $this->objpdf->text($xcol+12,$ylin+7,$this->descr_lota);
    
    $this->objpdf->Setfont('Arial','',6);
    $this->objpdf->text($xcol+100,$ylin+7,'Bco/Ag/Cta:');
    $this->objpdf->Setfont('Arial','B',7);
    $this->objpdf->text($xcol+112,$ylin+7,$this->banco.' / '.$this->agencia.' / '.$this->conta);

    $this->objpdf->Setfont('Arial','',6);
    $this->objpdf->text($xcol+170,$ylin+7,'Admissão:');
    $this->objpdf->Setfont('Arial','B',7);
    $this->objpdf->text($xcol+180,$ylin+7,$this->admissao);
        
    $this->objpdf->Setfont('Arial','',8);
    $this->objpdf->text($xcol+ 5 ,$ylin+18,'Cód.');
    $this->objpdf->text($xcol+ 55,$ylin+18,'Descrição');
    $this->objpdf->text($xcol+116,$ylin+18,'Referência');
    $this->objpdf->text($xcol+135,$ylin+18,'Proventos');
    $this->objpdf->text($xcol+157,$ylin+18,'Descontos');

    $this->objpdf->text($xcol+ 5 ,$ylin+81,'Cód.');
    $this->objpdf->text($xcol+ 55,$ylin+81,'Descrição');
    $this->objpdf->text($xcol+116,$ylin+81,'Referência');
    $this->objpdf->text($xcol+139,$ylin+81,'Valor');
    $this->objpdf->text($xcol+157,$ylin+81,'Tipo Base');



    $this->objpdf->Setfont('Arial','',6);
    $this->objpdf->text($xcol+155,$ylin+121,'Total dos Descontos');
    $this->objpdf->text($xcol+131,$ylin+121,'Total dos Vencimentos');
    $this->objpdf->text($xcol+133,$ylin+133,'Líquido a Receber');
    $this->objpdf->setfillcolor(225);
    $this->objpdf->rect($xcol+153,$ylin+128,23,10,'DF');
    $this->objpdf->setfillcolor(255,255,255);

    $this->objpdf->text($xcol+9  ,$ylin+140,'Sal. Base');
    $this->objpdf->text($xcol+30 ,$ylin+140,'Base Previdência');
    $this->objpdf->text($xcol+62 ,$ylin+140,'Base FGTS');
    $this->objpdf->text($xcol+89,$ylin+140,'FGTS do Mês');
    $this->objpdf->text($xcol+117,$ylin+140,'Base IRRF');
    $this->objpdf->text($xcol+139,$ylin+140,'Base Informativa');


    
      $this->objpdf->sety($ylin+24);
                $maiscol = 0;
                $yy = $this->objpdf->gety();
                $provento = 0;
                $margem_deduz  = 0;
                $margem_consignada = 0;
    $desconto     = 0;
    $baseprev     = 0;
    $basefgts     = 0;
    $baseirrf     = 0;
    $valor_margem = 0;

    $this->objpdf->Setfont('Arial','',7);
    for($ii = 0;$ii < $this->linhasenvelope ;$ii++) {
      
               if ( pg_fetch_result($this->recordenvelope,$ii,$this->tipo)  == 'P'){
                  $this->objpdf->cell(5,3,trim(pg_fetch_result($this->recordenvelope,$ii,$this->rubrica)),0,0,"R",0);
                  $this->objpdf->cell(5,3,"",0,0,"L",0);
                  $this->objpdf->cell(93,3,pg_fetch_result($this->recordenvelope,$ii,$this->descr_rub),0,0,"L",0);
                  $this->objpdf->cell(20,3,db_formatar(pg_fetch_result($this->recordenvelope,$ii,$this->quantidade),'f'),0,0,"R",0);
                  $this->objpdf->cell(22,3,db_formatar(pg_fetch_result($this->recordenvelope,$ii,$this->valor),'f'),0,0,"R",0);
                  $this->objpdf->cell(22,3,'',0,1,"R",0);
                  $provento += pg_fetch_result($this->recordenvelope,$ii,$this->valor);
                  $rubrica = trim(pg_fetch_result($this->recordenvelope,$ii,$this->rubrica));
                  if(db_getsession("DB_instit") == 1 && strtoupper($this->municpref == 'GUAIBA') && ($rubrica == '0102' || $rubrica == '0109' || $rubrica == '0111' || $rubrica == '0195'  || $rubrica == '0196' || $rubrica == '0197' || $rubrica == '0198' )){
                    $margem_consignada += pg_fetch_result($this->recordenvelope,$ii,$this->valor);
                  }elseif(db_getsession("DB_instit") == 1 && strtoupper($this->municpref) == 'ARAPIRACA' && 
                          ($rubrica == '0005' || $rubrica == '0006' || $rubrica == '0007' || $rubrica == '0008' || 
                           $rubrica == '0011' || $rubrica == '0014' || $rubrica == '0017' || $rubrica == '0018' || 
                           $rubrica == '0020' || $rubrica == '0021' || $rubrica == '0023' || $rubrica == '0055' || 
                           $rubrica == '0060' || $rubrica == '0061' || $rubrica == '0062' || $rubrica == '0063' || 
                           $rubrica == '0064' || $rubrica == '0065' || $rubrica == '0098' || $rubrica == '0099' || 
                           $rubrica == '0101' || $rubrica == '0104' || $rubrica == '0105' || $rubrica == '0107' || 
                           $rubrica == '0108' || $rubrica == '0112' || $rubrica == '0116' || $rubrica == '0117' || 
                           $rubrica == '0118' || $rubrica == '0121' || $rubrica == '0122' || $rubrica == '0126' || 
                           $rubrica == '0129' || $rubrica == '0131' || $rubrica == '0132' || $rubrica == '0133' || 
                           $rubrica == '0134' || $rubrica == '0135' || $rubrica == '0136' || $rubrica == '0137' || 
                           $rubrica == '0138' || $rubrica == '0150' || $rubrica == '0151' || $rubrica == '0160' || 
                           $rubrica == '0170' || $rubrica == '0190' 
                           )){
                    $margem_consignada += pg_fetch_result($this->recordenvelope,$ii,$this->valor);
                  }
               }elseif( pg_fetch_result($this->recordenvelope,$ii,$this->tipo ) == 'D'){ 
                 $this->objpdf->cell(5,3,trim(pg_fetch_result($this->recordenvelope,$ii,$this->rubrica)),0,0,"R",0);
                 $this->objpdf->cell(5,3,"",0,0,"L",0);
                 $this->objpdf->cell(93,3,pg_fetch_result($this->recordenvelope,$ii,$this->descr_rub),0,0,"L",0);
                 $this->objpdf->cell(20,3,db_formatar(pg_fetch_result($this->recordenvelope,$ii,$this->quantidade),'f'),0,0,"R",0);
                 $this->objpdf->cell(22,3,'',0,0,"R",0);
                 $this->objpdf->cell(22,3,db_formatar(pg_fetch_result($this->recordenvelope,$ii,$this->valor),'f'),0,1,"R",0);
                 $desconto += pg_fetch_result($this->recordenvelope,$ii,$this->valor);
                 $rubrica = trim(pg_fetch_result($this->recordenvelope,$ii,$this->rubrica));
                 if(db_getsession("DB_instit") == 1 && strtoupper($this->municpref) == 'ARAPIRACA' ){
                   if($rubrica == 'R901' || $rubrica == 'R904' || $rubrica == 'R913' || $rubrica == '0333' ){
                     $margem_consignada -= pg_fetch_result($this->recordenvelope,$ii,$this->valor);
                   }elseif($rubrica == '0330' || 
                           $rubrica == '0334' || 
                           $rubrica == '0335' || 
                           $rubrica == '0336' || 
                           $rubrica == '0337' || 
                           $rubrica == '0338' || 
                           $rubrica == '0340' || 
                           $rubrica == '0341' || 
                           $rubrica == '0342' || 
                           $rubrica == '0343' || 
                           $rubrica == '0344' || 
                           $rubrica == '0345' 
                          ){
                     $margem_deduz += pg_fetch_result($this->recordenvelope,$ii,$this->valor);
                   }
                 }
             }else{


         if(pg_fetch_result($this->recordenvelope,$ii,$this->rubrica) == 'R981' ||
            pg_fetch_result($this->recordenvelope,$ii,$this->rubrica) == 'R982' ){
            $baseirrf += pg_fetch_result($this->recordenvelope,$ii,$this->valor);
         }elseif(pg_fetch_result($this->recordenvelope,$ii,$this->rubrica) == 'R992'){
            $baseprev += pg_fetch_result($this->recordenvelope,$ii,$this->valor);
         }elseif(pg_fetch_result($this->recordenvelope,$ii,$this->rubrica) == 'R991'){
            $basefgts += pg_fetch_result($this->recordenvelope,$ii,$this->valor);
         }elseif(pg_fetch_result($this->recordenvelope,$ii,$this->rubrica) == 'R803'){
            $valor_margem += pg_fetch_result($this->recordenvelope,$ii,$this->valor);
         }
          continue;
       }
    }
    
    $this->objpdf->SetXY(10, 108);
    for($ii = 0;$ii < $this->linhasenvelope ;$ii++) {

      switch (pg_fetch_result($this->recordenvelope,$ii,$this->tipo_rubrica)){
        case 4 :
          $this->objpdf->cell(5,3,trim(pg_fetch_result($this->recordenvelope,$ii,$this->rubrica)),0,0,"R",0);
          $this->objpdf->cell(5,3,"",0,0,"L",0);
          $this->objpdf->cell(93,3,pg_fetch_result($this->recordenvelope,$ii,$this->descr_rub),0,0,"L",0);
          $this->objpdf->cell(20,3,db_formatar(pg_fetch_result($this->recordenvelope,$ii,$this->quantidade),'f'),0,0,"R",0);
          $this->objpdf->cell(22,3,db_formatar(pg_fetch_result($this->recordenvelope,$ii,$this->valor),'f'),0,0,"R",0);
          $this->objpdf->cell(22,3,'INFORMATIVA',0,1,"R",0);
          $baseInfo += pg_fetch_result($this->recordenvelope,$ii,$this->valor);
        break;
      }

    }

    $this->objpdf->text($xcol+134,$ylin+125,db_formatar($provento,'f'));
    $this->objpdf->text($xcol+157,$ylin+125,db_formatar($desconto,'f'));
    $this->objpdf->Setfont('Arial','B',9);
    $this->objpdf->text($xcol+152,$ylin+134,db_formatar(( $provento - $desconto ),'f'));
    $this->objpdf->Setfont('Arial','',8);

    
    $this->objpdf->SetY($ylin+142);
    $this->objpdf->SetX($xcol-1);
    $this->objpdf->cell(16, 3, db_formatar($this->f010,'f'), 0, 0, 'R');
    $this->objpdf->SetX($xcol+31);
    $this->objpdf->cell(16, 3, db_formatar($baseprev,'f'), 0, 0, 'R');
    $this->objpdf->SetX($xcol+58);
    $this->objpdf->cell(16, 3, db_formatar($basefgts,'f'), 0, 0, 'R');
    $this->objpdf->SetX($xcol+87);
    $this->objpdf->cell(16, 3, db_formatar(($basefgts*8/100),'f'), 0, 0, 'R');
    $this->objpdf->SetX($xcol+112);
    $this->objpdf->cell(16, 3, db_formatar($baseirrf,'f'), 0, 0, 'R');
    $this->objpdf->SetX($xcol+135);
    $this->objpdf->cell(16, 3, db_formatar($baseInfo,'f'), 0, 0, 'R');
    
    $this->objpdf->SetY($ylin+120);
    $this->objpdf->SetX($xcol+3);
    $this->objpdf->multicell(125,4,'MENSAGEM :   '.$this->mensagem,0,"J");
    $this->objpdf->SetX($xcol+3);
    $this->objpdf->multicell(0,4,$this->histparcel);
    $this->objpdf->Setfont('Arial','',6);
    $this->objpdf->setx(15);
    $this->objpdf->setfillcolor(0);
    $this->objpdf->Setfont('Arial','',5);
    $this->objpdf->TextWithDirection(185,$ylin+116,'DECLARO TER RECEBIDO A IMPORTÂNCIA LÍQUIDA DISCRIMIDA NESTE RECIBO.','U'); // texto no canhoto do carne
    $this->objpdf->line($xcol+193,$ylin+35,$xcol+193,$ylin+80);
    $this->objpdf->line($xcol+193,$ylin+85,$xcol+193,$ylin+125);
    $this->objpdf->TextWithDirection(200,$ylin+107,'DATA','U'); // texto no canhoto do carne
    $this->objpdf->TextWithDirection(200,$ylin+70,'ASSINATURA DO FUNCIONÁRIO','U'); // texto no canhoto do carne
    $this->objpdf->TextWithDirection(209.7,$ylin,$this->total.' / '.$this->numero,'U'); // numero do contra-cheque
    $this->objpdf->TextWithDirection(205,$ylin+130,"Para Verificar Autenticidade Acesse: ".$this->url,'U');
    $this->objpdf->TextWithDirection(205,$ylin+70,"Código da Autenticação: ",'U');
    $this->objpdf->Setfont('Arial','B',5); 
    $this->objpdf->TextWithDirection(205,$ylin+50,$this->codautent,'U');
?>
