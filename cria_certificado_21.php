<?php
//Na função "conecta" abaixo, substituir XXX pelos dados do banco de dados de produção
//Respectivamente: Nome do banco de dados; host; usuário; senha; porta

function conecta(){  
    try {      
      $pdo = new PDO("pgsql:dbname='voltaredonda'; host='10.1.0.51'; user='ecidade'; password='db#vltrdnd12'; port='5432'"); 
      $pdo->exec( "select fc_startsession();" );
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      return $pdo;
    } catch (PDOException $e){
      echo "Erro: " . $e->getMessage();
    }
}

$pdo = conecta();
if(!$pdo) die ("Não foi possível conectar ao banco. Tente novamente.");


function buscaTudo($pdo){
    try {      
      $stmt = $pdo->prepare("SELECT distinct empempenho.e60_numemp from empempenho inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo inner join concarpeculiar on concarpeculiar.c58_sequencial = empempenho.e60_concarpeculiar inner join db_config as a on a.codigo = orcdotacao.o58_instit inner join orctiporec on orctiporec.o15_codigo = orcdotacao.o58_codigo inner join orcfuncao on orcfuncao.o52_funcao = orcdotacao.o58_funcao inner join orcsubfuncao on orcsubfuncao.o53_subfuncao = orcdotacao.o58_subfuncao inner join orcprograma on orcprograma.o54_anousu = orcdotacao.o58_anousu and orcprograma.o54_programa = orcdotacao.o58_programa inner join orcelemento on orcelemento.o56_codele = orcdotacao.o58_codele and orcelemento.o56_anousu = orcdotacao.o58_anousu inner join orcprojativ on orcprojativ.o55_anousu = orcdotacao.o58_anousu and orcprojativ.o55_projativ = orcdotacao.o58_projativ inner join orcorgao on orcorgao.o40_anousu = orcdotacao.o58_anousu and orcorgao.o40_orgao = orcdotacao.o58_orgao inner join orcunidade on orcunidade.o41_anousu = orcdotacao.o58_anousu and orcunidade.o41_orgao = orcdotacao.o58_orgao and orcunidade.o41_unidade = orcdotacao.o58_unidade where e60_instit = 1 AND e60_numemp NOT IN ( SELECT DISTINCT e60_numemp FROM rhpessoalmov INNER JOIN rhpessoal ON rh01_regist = rh02_regist INNER JOIN cgm ON z01_numcgm = rh01_numcgm INNER JOIN empempenho ON z01_numcgm = e60_numcgm INNER JOIN rhempenhofolharubrica ON rh73_seqpes = rh02_seqpes INNER JOIN rhempenhofolharhemprubrica ON rh81_rhempenhofolharubrica = rh73_sequencial INNER JOIN rhempenhofolha ON rh72_sequencial = rh81_rhempenhofolha INNER JOIN rhempenhofolhaempenho ON rh76_rhempenhofolha = rh72_sequencial WHERE rh02_anousu = 2021 AND e60_numemp = rh76_numemp ) and e60_anousu = 2021 order by e60_numemp");
      $stmt->execute();
      $resultado = $stmt->fetchAll(PDO::FETCH_OBJ);
      return $resultado ? $resultado : false;
    } catch (PDOException $e){
      echo $e->getMessage();
    }
}


function buscaNaopagas($pdo, $sequencial){
    try {      
      $stmt = $pdo->prepare("SELECT c71_coddoc, e50_codord, e50_numemp, c71_codlan, e50_data FROM pagordem INNER JOIN conlancamord ON c80_codord = e50_codord INNER JOIN conlancam ON c80_codlan = c70_codlan INNER JOIN conlancamdoc ON c71_codlan = c70_codlan WHERE e50_numemp = :sequencial AND c71_coddoc in(3, 23) AND e50_codord not in (SELECT e50_codord FROM pagordem INNER JOIN conlancamord ON c80_codord = e50_codord INNER JOIN conlancam ON c80_codlan = c70_codlan INNER JOIN conlancamdoc ON c71_codlan = c70_codlan WHERE e50_numemp = :sequencial AND c71_coddoc = 5) AND c70_anousu = 2021 ORDER BY e50_codord");
      $stmt->execute(array("sequencial" => $sequencial));
      $resultado = $stmt->fetchAll(PDO::FETCH_OBJ);
      return $resultado ? $resultado : false;
    } catch (PDOException $e){
      echo $e->getMessage();
    }
}

;
function buscaAnuladas($pdo, $op){
    try {      
      $stmt = $pdo->prepare("SELECT c71_coddoc FROM pagordem INNER JOIN conlancamord ON c80_codord = e50_codord INNER JOIN conlancam ON c80_codlan = c70_codlan INNER JOIN conlancamdoc ON c71_codlan = c70_codlan WHERE e50_codord = :op AND c71_coddoc in(4,24)");
      $stmt->execute(array("op" => $op));
      $resultado = $stmt->fetchAll(PDO::FETCH_OBJ);
      return $resultado ? $resultado : false;
    } catch (PDOException $e){
      echo $e->getMessage();
    }
}

function consultaCertificados($pdo, $op){
    try {      
      $stmt = $pdo->prepare("SELECT id from certificacaoconformidade where e50_codord = :op");
      $stmt->execute(array("op" => $op));
      $resultado = $stmt->fetchAll(PDO::FETCH_OBJ);
      return $resultado ? $resultado : false;
    } catch (PDOException $e){
      echo $e->getMessage();
    }
}


function buscaDados($pdo, $seq){
    try {      
      $stmt = $pdo->prepare("SELECT *, (select rh76_rhempenhofolha from rhempenhofolhaempenho where rh76_numemp = 922020) as empenho_folha from empempenho inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo inner join concarpeculiar on concarpeculiar.c58_sequencial = empempenho.e60_concarpeculiar inner join db_config as a on a.codigo = orcdotacao.o58_instit inner join orctiporec on orctiporec.o15_codigo = orcdotacao.o58_codigo inner join orcfuncao on orcfuncao.o52_funcao = orcdotacao.o58_funcao inner join orcsubfuncao on orcsubfuncao.o53_subfuncao = orcdotacao.o58_subfuncao inner join orcprograma on orcprograma.o54_anousu = orcdotacao.o58_anousu and orcprograma.o54_programa = orcdotacao.o58_programa inner join orcelemento on orcelemento.o56_codele = orcdotacao.o58_codele and orcelemento.o56_anousu = orcdotacao.o58_anousu inner join orcprojativ on orcprojativ.o55_anousu = orcdotacao.o58_anousu and orcprojativ.o55_projativ = orcdotacao.o58_projativ inner join orcorgao on orcorgao.o40_anousu = orcdotacao.o58_anousu and orcorgao.o40_orgao = orcdotacao.o58_orgao inner join orcunidade on orcunidade.o41_anousu = orcdotacao.o58_anousu and orcunidade.o41_orgao = orcdotacao.o58_orgao and orcunidade.o41_unidade = orcdotacao.o58_unidade where empempenho.e60_numemp = :seq");
      $stmt->execute(array("seq" => $seq));
      $resultado = $stmt->fetchAll(PDO::FETCH_OBJ);
      return $resultado ? $resultado[0] : false;
    } catch (PDOException $e){
      echo $e->getMessage();
    }
}


function verificaSeq1($pdo, $sequencial){
    try {      
      $stmt = $pdo->prepare("SELECT max(cseq1) as seq1 FROM certificacaoconformidade WHERE e60_numemp = :sequencial");
      $stmt->execute(array("sequencial" => $sequencial));
      $resultado = $stmt->fetchAll(PDO::FETCH_OBJ);
      return $resultado ? $resultado[0]->seq1 : false;
    } catch (PDOException $e){
      echo $e->getMessage();
    }
}


function buscaPA($pdo, $sequencial){
    try {      
      $stmt = $pdo->prepare("SELECT e150_numeroprocesso FROM empautorizaprocesso INNER JOIN empempaut ON e150_empautoriza = e61_autori WHERE e61_numemp = :sequencial");
      $stmt->execute(array("sequencial" => $sequencial));
      $resultado = $stmt->fetchAll(PDO::FETCH_OBJ);
      return $resultado ? $resultado[0]->e150_numeroprocesso : false;
    } catch (PDOException $e){
      echo $e->getMessage();
    }
}



function buscaNF($pdo, $lancamento){
    try {      
      $stmt = $pdo->prepare("SELECT e69_numero, c70_valor from conlancamemp inner join conlancam on c70_codlan = c75_codlan left join conlancamordem on conlancamordem.c03_codlan = conlancam.c70_codlan left outer join conlancampag on c82_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc left join conlancamcompl on c72_codlan =c70_codlan left join conlancamnota on c66_codlan =c70_codlan left join conlancamord on c80_codlan =c70_codlan left join empnota on c66_codnota = e69_codnota left join conplanoreduz on c61_reduz = conlancampag.c82_reduz and c61_anousu=c70_anousu left join conplano on c60_codcon = conplanoreduz.c61_codcon and c60_anousu=c61_anousu left join pagordem on e50_codord = c80_codord where c70_codlan = :lancamento order by c75_data, c03_ordem, c75_codlan");
      $stmt->execute(array("lancamento" => $lancamento));
      $resultado = $stmt->fetchAll(PDO::FETCH_OBJ);
      return $resultado ? $resultado[0] : false;
    } catch (PDOException $e){
      echo $e->getMessage();
    }
}

function verificaDesdobramento($pdo, $sequencial){
    try {      
      $stmt = $pdo->prepare("SELECT o56_elemento from empelemento inner join empempenho on empempenho.e60_numemp = empelemento.e64_numemp inner join orcelemento on orcelemento.o56_codele = empelemento.e64_codele and orcelemento.o56_anousu = empempenho.e60_anousu inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo where empelemento.e64_numemp = :sequencial order by e64_codele");
      $stmt->execute(array("sequencial" => $sequencial));
      $resultado = $stmt->fetchAll(PDO::FETCH_OBJ);

      $resultado = substr($resultado[0]->o56_elemento, 0, 9);
      if(substr($resultado, 0, 4) == 3319 || substr($resultado, 0, 4) == 3333 || substr($resultado, 0, 4) == 3334|| substr($resultado, 0, 4) == 3337|| substr($resultado, 0, 4) == 3329 || substr($resultado, 0, 7) == 3339091 || substr($resultado, 0, 7) == 3449091 || substr($resultado, 0, 7) == 3469091 || substr($resultado, 0, 7) == 3459091 || substr($resultado, 0, 7) == 3339067 || substr($resultado, 0, 7) == 3469071 || substr($resultado, 0, 7) == 3339047 || substr($resultado, 0, 7) == 3339018 || substr($resultado, 0, 7) == 3339046 || substr($resultado, 0, 7) == 3339093 || $resultado == 333903644 || $resultado == 333903645 || $resultado == 333903696 || $resultado == 333909399){
        return false;
      }

      return $resultado;
    } catch (PDOException $e){
      echo $e->getMessage();
    }
}

function verificaCoddoc($pdo, $sequencial){
  try {      
      $stmt = $pdo->prepare("SELECT c53_coddoc from conlancamemp inner join conlancam on c70_codlan = c75_codlan left join conlancamordem on conlancamordem.c03_codlan = conlancam.c70_codlan left outer join conlancampag on c82_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc left join conlancamcompl on c72_codlan =c70_codlan left join conlancamnota on c66_codlan =c70_codlan left join conlancamord on c80_codlan =c70_codlan left join empnota on c66_codnota = e69_codnota left join conplanoreduz on c61_reduz = conlancampag.c82_reduz and c61_anousu=c70_anousu left join conplano on c60_codcon = conplanoreduz.c61_codcon and c60_anousu=c61_anousu left join pagordem on e50_codord = c80_codord where c75_numemp = :sequencial");
      $stmt->execute(array("sequencial" => $sequencial));
      $resultado = $stmt->fetchAll(PDO::FETCH_OBJ);

      if($resultado[0]->c53_coddoc == 410){
        return false;
      }

      return $resultado;
    } catch (PDOException $e){
      echo $e->getMessage();
    }    
}

function verificaCgm($pdo, $sequencial){
    try {      
      $stmt = $pdo->prepare("SELECT z01_numcgm from empelemento inner join empempenho on empempenho.e60_numemp = empelemento.e64_numemp inner join orcelemento on orcelemento.o56_codele = empelemento.e64_codele and orcelemento.o56_anousu = empempenho.e60_anousu inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo where empelemento.e64_numemp = :sequencial order by e64_codele");
      $stmt->execute(array("sequencial" => $sequencial));
      $resultado = $stmt->fetchAll(PDO::FETCH_OBJ);

      if($resultado[0]->z01_numcgm == 217 || $resultado[0]->z01_numcgm == 112873 || $resultado[0]->z01_numcgm == 103446 || $resultado[0]->z01_numcgm == 2686 || $resultado[0]->z01_numcgm == 117185 || $resultado[0]->z01_numcgm == 367 || $resultado[0]->z01_numcgm == 256 || $resultado[0]->z01_numcgm == 13428 || $resultado[0]->z01_numcgm == 65963 || $resultado[0]->z01_numcgm == 11006 || $resultado[0]->z01_numcgm == 60626 || $resultado[0]->z01_numcgm == 56459 || $resultado[0]->z01_numcgm == 77171 || $resultado[0]->z01_numcgm == 556 || $resultado[0]->z01_numcgm == 118712 || $resultado[0]->z01_numcgm == 67387 || $resultado[0]->z01_numcgm == 3331 || $resultado[0]->z01_numcgm == 60099|| $resultado[0]->z01_numcgm == 1272 || $resultado[0]->z01_numcgm == 3154 || $resultado[0]->z01_numcgm == 3660 || $resultado[0]->z01_numcgm == 7350 || $resultado[0]->z01_numcgm == 8957 || $resultado[0]->z01_numcgm == 8958 || $resultado[0]->z01_numcgm == 9078 || $resultado[0]->z01_numcgm == 9155 || $resultado[0]->z01_numcgm == 10315 || $resultado[0]->z01_numcgm == 11101 || $resultado[0]->z01_numcgm == 11145 || $resultado[0]->z01_numcgm == 11306 || $resultado[0]->z01_numcgm == 11340 || $resultado[0]->z01_numcgm == 13989 || $resultado[0]->z01_numcgm == 32808 || $resultado[0]->z01_numcgm == 56467 || $resultado[0]->z01_numcgm == 56484 || $resultado[0]->z01_numcgm == 56849 || $resultado[0]->z01_numcgm == 60190 || $resultado[0]->z01_numcgm == 108852 || $resultado[0]->z01_numcgm == 110585 || $resultado[0]->z01_numcgm == 112322 || $resultado[0]->z01_numcgm == 112733 || $resultado[0]->z01_numcgm == 114315 || $resultado[0]->z01_numcgm == 115347 || $resultado[0]->z01_numcgm == 115964 || $resultado[0]->z01_numcgm == 116032 || $resultado[0]->z01_numcgm == 118505 || $resultado[0]->z01_numcgm == 118506 || $resultado[0]->z01_numcgm == 120370){
        return false;
      }

      return $resultado;
    } catch (PDOException $e){
      echo $e->getMessage();
    }
}


function insereCertificado($pdo, $dados){  
  try {
    $stmt = $pdo->prepare("INSERT INTO certificacaoconformidade(e60_numemp, e60_codemp, e60_anousu, e60_instit, e50_codord, corgao, cempenho, cseq1, cseq2,cano, nocertificado, noprocesso, datageracao, ordenadordadespesa, instrumentojuridico, noempenho, nonotafiscal, valornotafiscal, z01_numcgm, z01_nome, z01_cgccpf, g11, g11f, g12, g12f, g13, g13f, g14, g14f, g15, g15f, g16, g16f, g17, g17f, g18, g18f, g19, g19f, g110, g110f, g111, g111f, g112, g112f, g113, g113f, g114, g114f, g115, g115f, g116, g116f, g117, g117f, viascript) VALUES(:e60_numemp, :e60_codemp, :e60_anousu, :e60_instit, :e50_codord, :o58_orgao, :cempenho, :cseq1, :cseq2, :cano, :nocertificado, :noprocesso, :datageracao, :ordenadordadespesa, :instrumentojuridico, :noempenho, :nonotafiscal, :valornotafiscal, :z01_numcgm, :fornecedor, :cpffornecedor, :g11, :g11f, :g12, :g12f, :g13, :g13f, :g14, :g14f, :g15, :g15f, :g16, :g16f, :g17, :g17f, :g18, :g18f, :g19, :g19f, :g110, :g110f, :g111, :g111f, :g112, :g112f, :g113, :g113f, :g114, :g114f, :g115, :g115f, :g116, :g116f, :g117, :g117f, :viascript)");
    $stmt->execute(array(
      "e60_numemp" => $dados["e60_numemp"],
      "e60_codemp" => $dados["e60_codemp"],
      "e60_anousu" => $dados["e60_anousu"],
      "e60_instit" => $dados["e60_instit"],
      "e50_codord" => $dados["e50_codord"],
      "o58_orgao" => $dados["o58_orgao"],
      "cempenho" => $dados["cempenho"],
      "cseq1" => $dados["cseq1"],
      "cseq2" => $dados["cseq2"],
      "cano" => $dados["cano"],
      "nocertificado" => $dados["nocertificado"],
      "noprocesso" => $dados["noprocesso"],
      "datageracao" => $dados["datageracao"],
      "ordenadordadespesa" => $dados["ordenadordadespesa"],
      "instrumentojuridico" => $dados["instrumentojuridico"],
      "noempenho" => $dados["noempenho"],
      "nonotafiscal" => $dados["nonotafiscal"],
      "valornotafiscal" => $dados["valornotafiscal"],
      "z01_numcgm" => $dados["z01_numcgm"],
      "fornecedor" => $dados["fornecedor"],
      "cpffornecedor" => $dados["cpffornecedor"],
      "g11" => $dados["g11"],
      "g11f" => $dados["g11f"],
      "g12" => $dados["g12"],
      "g12f" => $dados["g12f"],
      "g13" => $dados["g13"],
      "g13f" => $dados["g13f"],
      "g14" => $dados["g14"],
      "g14f" => $dados["g14f"],
      "g15" => $dados["g15"],
      "g15f" => $dados["g15f"],
      "g16" => $dados["g16"],
      "g16f" => $dados["g16f"],
      "g17" => $dados["g17"],
      "g17f" => $dados["g17f"],
      "g18" => $dados["g18"],
      "g18f" => $dados["g18f"],
      "g19" => $dados["g19"],
      "g19f" => $dados["g19f"],
      "g110" => $dados["g110"],
      "g110f" => $dados["g110f"],
      "g111" => $dados["g111"],
      "g111f" => $dados["g111f"],
      "g112" => $dados["g112"],
      "g112f" => $dados["g112f"],
      "g113" => $dados["g113"],
      "g113f" => $dados["g113f"],
      "g114" => $dados["g114"],
      "g114f" => $dados["g114f"],
      "g115" => $dados["g115"],
      "g115f" => $dados["g115f"],
      "g116" => $dados["g116"],
      "g116f" => $dados["g116f"],
      "g117" => $dados["g117"],
      "g117f" => $dados["g117f"],
      "viascript" => "sim"
    ));
    return ($stmt->rowCount() > 0) ? $stmt : false;
  } catch (PDOException $e){
    echo $e->getMessage();
  }
}


$empenhos = buscaTudo($pdo);

foreach ($empenhos as $linha){
  $naopago = buscaNaopagas($pdo, $linha->e60_numemp);
  $vecgm = verificaCgm($pdo, $linha->e60_numemp);
  $vedesdobra = verificaDesdobramento($pdo, $linha->e60_numemp);
  $vecoddoc = verificaCoddoc($pdo, $linha->e60_numemp);  
  
  if(!$naopago || !$vecgm || !$vedesdobra || !$vecoddoc){
    continue;
  }  

  foreach ($naopago as $noordem){
    $anulada = buscaAnuladas($pdo, $noordem->e50_codord);
    if($anulada){
      continue;
    }

    $insere = array();
    $temcertificado = consultaCertificados($pdo, $noordem->e50_codord);
    if(!$temcertificado){      
      $dados = buscaDados($pdo, $noordem->e50_numemp);
      $notafiscal = buscaNF($pdo, $noordem->c71_codlan);


            
      $dnoempenho = $dados->e60_codemp."/".$dados->e60_anousu;
      $dordenador = $dados->o40_descr;
      $dfornecedor = $dados->z01_nome;
      $dorgao = $dados->o58_orgao;
      $vseq1 = verificaSeq1($pdo, $dados->e60_numemp);

      if(strlen($dorgao) == 1){
        $corgao = "0".$dorgao;
      }else{
        $corgao = $dorgao;
      }

      if(strlen($dados->e60_codemp) == 1){
        $cemp = "0000" . $dados->e60_codemp;
      } elseif(strlen($dados->e60_codemp) == 2){
        $cemp = "000" . $dados->e60_codemp;
      } elseif(strlen($dados->e60_codemp) == 3){  
        $cemp = "00" . $dados->e60_codemp;
      } elseif(strlen($dados->e60_codemp) == 4){
        $cemp = "0" . $dados->e60_codemp;
      } elseif(strlen($dados->e60_codemp) == 5){
        $cemp = $dados->e60_codemp;
      }

      if(isset($vseq1)){  
        $vseq1 = $vseq1 + 1;
        $vseq1 = (string)$vseq1;
        if(strlen($vseq1) == 1){
          $vseq1 = "0".$vseq1;
        }
      } else {
        $vseq1 = "01";
      }

      $vseq2 = "00";
      $certificado = $corgao . '.' . $cemp . '.' . $vseq1 . '.' . $vseq2 . '/' . $dados->e60_anousu;

      $cgc = preg_replace("/[^0-9]/", "", $dados->z01_cgccpf);
      $qtd = strlen($cgc);

      if($qtd == 11 ) { 
        $dcpf = substr($cgc, 0, 3) . '.' .substr($cgc, 3, 3) . '.' . substr($cgc, 6, 3) . '.' . substr($cgc, 9, 2);
      } elseif($qtd == 14) {
        $dcpf = substr($cgc, 0, 2) . '.' . substr($cgc, 2, 3) . '.' . substr($cgc, 5, 3) . '/' . substr($cgc, 8, 4) . '-' . substr($cgc, -2);
      } else {
        $cpf = "";
      }

      $e60_numemp = $dados->e60_numemp;
      $e60_codemp = $dados->e60_codemp;
      $e60_anousu = $dados->e60_anousu;
      $e60_instit = $dados->e60_instit;
      $o58_orgao = $dados->o58_orgao;
      $cano = $dados->e60_anousu;
      $z01_numcgm = $dados->z01_numcgm;

      
      $insere["e60_numemp"] = $e60_numemp;
      $insere["e60_codemp"] = $e60_codemp;
      $insere["e60_anousu"] = $e60_anousu;
      $insere["e60_instit"] = $e60_instit;
      $insere["e50_codord"] = $noordem->e50_codord;
      $insere["o58_orgao"] = $o58_orgao;
      $insere["cempenho"] = $cemp;
      $insere["cseq1"] = $vseq1;
      $insere["cseq2"] = $vseq2;
      $insere["cano"] = $cano;
      $insere["nocertificado"] = $certificado;
      $insere["noprocesso"] = buscaPA($pdo, $e60_numemp);
      $insere["datageracao"] = $noordem->e50_data;
      $insere["ordenadordadespesa"] = $dordenador;
      $insere["instrumentojuridico"] = "Conforme processo";
      $insere["noempenho"] = $dnoempenho;
      $insere["nonotafiscal"] = $notafiscal->e69_numero;

      $insere["valornotafiscal"] = $notafiscal->c70_valor;            

      $insere["z01_numcgm"] = $z01_numcgm;
      $insere["fornecedor"] = $dfornecedor;
      $insere["cpffornecedor"] = $dcpf;
      $insere["g11"] = 3;
      $insere["g11f"] = "";
      $insere["g12"] = 3;
      $insere["g12f"] = "";
      $insere["g13"] = 3;
      $insere["g13f"] = "";
      $insere["g14"] = 3;
      $insere["g14f"] = "";
      $insere["g15"] = 3;
      $insere["g15f"] = "";
      $insere["g16"] = 3;
      $insere["g16f"] = "";
      $insere["g17"] = 3;
      $insere["g17f"] = "";
      $insere["g18"] = 3;
      $insere["g18f"] = "";
      $insere["g19"] = 3;
      $insere["g19f"] = "";
      $insere["g110"] = 3;
      $insere["g110f"] = "";
      $insere["g111"] = 3;
      $insere["g111f"] = "";
      $insere["g112"] = 3;
      $insere["g112f"] = "";
      $insere["g113"] = 3;
      $insere["g113f"] = "";
      $insere["g114"] = 3;
      $insere["g114f"] = "";
      $insere["g115"] = 3;
      $insere["g115f"] = "";
      $insere["g116"] = 3;
      $insere["g116f"] = "";
      $insere["g117"] = 3;
      $insere["g117f"] = "";
      
      
      insereCertificado($pdo, $insere);
      
    }//Fim do if para inserir o certificado
  }//fim do foreach com os não pagos

  
  
  
}//fim do foreach de empenhos


echo "Finalizado";
