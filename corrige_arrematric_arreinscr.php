<?php

/**
 * Seta o tempo e memoria limite para execução
 */
set_time_limit(0);
ini_set("memory_limit", '-1');

$str_arquivo = $_SERVER['PHP_SELF'];
$str_hora    = date( "h:m:s");

/**
 * Define as variaveis ou via db_conn
 * APONTAR PARA ONTEM
 */
$DB_USUARIO      = "";
$DB_SENHA        = "";
$DB_SERVIDOR     = "";
$DB_PORTA        = "5432";
$DB_BASE         = "";

require("libs/db_conn.php");

require_once("integracao_externa/portal_transparencia/libs/db_libconversao.php");

$sArqLog = "tmp/acerto_arrematric_arreinscr-$DB_BASE.txt";

/**
 * Conecta na base informada no db_conn
 */
db_log("- BASE PARA AJUSTES: $DB_BASE - $DB_SERVIDOR", $sArqLog);
$sDataSourceDBPortal = "host=$DB_SERVIDOR dbname=$DB_BASE port=$DB_PORTA user=$DB_USUARIO password=$DB_SENHA";
if(!($conn = pg_connect($sDataSourceDBPortal))) {

  db_log("Erro ao conectar no DBPortal ($sDataSourceDBPortal)...", $sArqLog);
  die();
}

/**
 * Prosseguindo com os ajustes
 */
$isTeste = null;
if(isset($argv[1])){
  $isTeste = (strtoupper($argv[1])=="TESTE");
}

db_log("", $sArqLog);
db_log("*** INICIO Script ".basename(__FILE__)." ***", $sArqLog);
db_log("", $sArqLog);

db_log("Arquivo de Log: $sArqLog", $sArqLog);
db_log("    Script PHP: ".basename(__FILE__), $sArqLog);
db_log("", $sArqLog);

if ($isTeste) {

  db_log("Executando em modo TESTE", $sArqLog);
  db_log("", $sArqLog);
}

db_log("Conectando...", $sArqLog);

db_log("", $sArqLog);

pg_exec( $conn, "begin;") or die(db_log("ERRO SQL: begin;", $sArqLog));

$erro = false;

$sSql   = "drop table if exists tmparrematric;";
$result = pg_exec($conn, $sSql);

$sSql   = "drop table if exists tmparreinscr;";
$result = pg_exec($conn, $sSql);

$sSql   = "create table
            tmparrematric ( numpre integer,
                            matric integer,
                            perc   float8 );";
$result = pg_exec($conn, $sSql) or die(db_log("ERRO SQL: $sSql", $sArqLog));

$sSql   = "create index
             tmparrematric_matric_numpre_in on
             tmparrematric ( matric, numpre );";
$result = pg_exec($conn, $sSql) or die(db_log("ERRO SQL: $sSql", $sArqLog));

$sSql   = "create table
               tmparreinscr ( numpre integer,
                              inscr  integer,
                              perc   float8 );";
$result = pg_exec($conn, $sSql) or die(db_log("ERRO SQL: $sSql", $sArqLog));

$sSql   = "create index
               tmparreinscr_inscr_numpre_in on
               tmparreinscr ( inscr, numpre );";
$result = pg_exec($conn, $sSql) or die(db_log("ERRO SQL: $sSql", $sArqLog));

db_log("", $sArqLog);

pg_exec( $conn, "select fc_startsession();") or die(db_log("ERRO SQL: startsession", $sArqLog));

$str_sql  = " select distinct                                                                                                         ";
$str_sql .= "        v07_parcel as parcel,                                                                                            ";
$str_sql .= "        v07_numpre as numpreant,                                                                                         ";
$str_sql .= "        case                                                                                                             ";
$str_sql .= "          when termoini.parcel is not null then 'inicial'                                                                ";
$str_sql .= "          when termodiv.parcel is not null then 'divida'                                                                 ";
$str_sql .= "          when termoreparc.v08_parcel is not null then 'reparcelamento'                                                  ";
$str_sql .= "        end as tipo,                                                                                                     ";
$str_sql .= "        case                                                                                                             ";
$str_sql .= "          when k00_inscr is not null then 'inscr'                                                                        ";
$str_sql .= "          when k00_matric is not null then 'matric'                                                                      ";
$str_sql .= "        end as origem                                                                                                    ";
$str_sql .= "   from termo                                                                                                            ";
$str_sql .= "        left join (select distinct parcel from termodiv) termodiv on termodiv.parcel = termo.v07_parcel                  ";
$str_sql .= "        left join (select distinct parcel from termoini) termoini on termoini.parcel = termo.v07_parcel                  ";
$str_sql .= "        left join (select distinct v08_parcel from termoreparc) termoreparc on termoreparc.v08_parcel = termo.v07_parcel ";
$str_sql .= "        left join arrematric on v07_numpre = arrematric.k00_numpre                                                       ";
$str_sql .= "        left join arreinscr  on v07_numpre = arreinscr.k00_numpre                                                        ";
$str_sql .= "   where ( arrematric.k00_numpre is not null or arreinscr.k00_numpre is not null )                                       ";
$str_sql .= "     and ( termodiv.parcel is not null or termoini.parcel is not null )                                                  ";

/**
 * Cria tabela com os registros que serao ajustados
 */
$sSqlBackup = "create table bkp_acerto_arrematric_arreinscr as " . $str_sql;
pg_query( $conn, $sSqlBackup ) or die(db_log("ERRO SQL: $sSqlBackup", $sArqLog));

$res_select = pg_query( $conn, $str_sql ) or die(db_log("ERRO SQL: $str_sql", $sArqLog));
$int_linhas = pg_num_rows( $res_select );

$numpre_ant = "";
$valorzero  = 0;

for( $i=0; $i < $int_linhas; $i++ ){

  db_fieldsmemory( $res_select, $i );
  db_log(" ".round( ( ($i / $int_linhas) * 100 ) ,0)."%  CONCLUIDO... $i/$int_linhas \r", $sArqLog);

  if ($tipo ==  'inicial') {

    $sqlTotal = " select sum(k00_valor) as total
                    from termoini
                         inner join inicialnumpre on inicial    = v59_inicial
                         inner join arreold       on v59_numpre = k00_numpre
                         left join arrematric     on v59_numpre = arrematric.k00_numpre
                         left join arreinscr      on v59_numpre = arreinscr.k00_numpre
                   where parcel = $parcel
                     and ( arrematric.k00_numpre is not null or arreinscr.k00_numpre is not null ) ";

    $sqlRegistros = " select arreold.k00_numpre as numpre,
                             arreold.k00_numpar as numpar,
                             arreold.k00_valor  as valor
                        from termoini
                             inner join inicialnumpre on inicial    = v59_inicial
                             inner join arreold       on v59_numpre = k00_numpre
                             left join arrematric     on v59_numpre = arrematric.k00_numpre
                             left join arreinscr      on v59_numpre = arreinscr.k00_numpre
                       where parcel = $parcel
                         and ( arrematric.k00_numpre is not null or arreinscr.k00_numpre is not null ) ";

  } else if ($tipo ==  'divida') {

    $sqlTotal = " select sum(v01_valor) as total
                    from termodiv
                         inner join divida  on v01_coddiv = coddiv
                         left join arrematric     on v01_numpre = arrematric.k00_numpre
                         left join arreinscr      on v01_numpre = arreinscr.k00_numpre
                   where parcel = $parcel
                         and ( arrematric.k00_numpre is not null or arreinscr.k00_numpre is not null ) ";

    $sqlRegistros = " select divida.v01_numpre as numpre,
                             divida.v01_numpar as numpar,
                             divida.v01_valor  as valor
                        from termodiv
                             inner join divida    on v01_coddiv = coddiv
                             left join arrematric on v01_numpre = arrematric.k00_numpre
                             left join arreinscr  on v01_numpre = arreinscr.k00_numpre
                       where parcel = $parcel
                         and ( arrematric.k00_numpre is not null or arreinscr.k00_numpre is not null ) ";

  }

  $rsTotal = pg_query($sqlTotal) or die (db_log("ERRO SQL: $sqlTotal", $sArqLog));
  db_fieldsmemory( $rsTotal, 0 );

  $rsRegistros         = pg_query($sqlRegistros) or die (db_log("ERRO SQL: $sqlRegistros", $sArqLog));
  $intNumrowsRegistros = pg_num_rows($rsRegistros);

  $PercCalc = 0;

  for ( $iRegistros = 0 ; $iRegistros < $intNumrowsRegistros ; $iRegistros ++ ){

    db_fieldsmemory( $rsRegistros, $iRegistros );

//        echo " numpreant - $numpreant numpar - $numpar valor - $valor \n ";

    if ($valor == 0) {

      $valorzero++;
      continue;
    }

    $PercCalc = (( $valor / $total ) * 100 );

    $sqlPercentuais = " select k00_matric as k00_origem,
                               k00_perc,
                               1 as tipo
                          from arrematric
                         where k00_numpre = $numpre
                       union
                        select k00_inscr as k00_origem,
                               k00_perc,
                               2 as tipo
                          from arreinscr
                         where k00_numpre = $numpre ";

    $rsPercentuais = pg_query($sqlPercentuais) or die (db_log("ERRO SQL: $sqlPercentuais", $sArqLog));
    $intNumrowsPercentuais = pg_num_rows($rsPercentuais);

    for ( $iPercentuais = 0; $iPercentuais < $intNumrowsPercentuais ; $iPercentuais++ ){

      db_fieldsmemory( $rsPercentuais, $iPercentuais );
      if ($tipo == 1) {

        /* select para verificar se ja existe */
        $sqlPercMatric = " select matric, numpre from tmparrematric where matric = $k00_origem and numpre = $numpreant ";
        $rsPercMatric  = pg_query($sqlPercMatric) or die (db_log("ERRO SQL: $sqlPercMatric", $sArqLog));
        $intAchou = pg_num_rows($rsPercMatric);
        if ( $intAchou > 0 ){

          $sqlx = " update tmparrematric
                       set perc = ( perc + ( ".( $PercCalc).") )
                     where matric = $k00_origem and numpre = $numpreant ";
          pg_exec($sqlx) or die(db_log("ERRO SQL: $sqlx", $sArqLog));
        } else {

          $sqlx = " insert into tmparrematric (matric,perc,numpre)
                         values ( $k00_origem, ( ".( $PercCalc  )." ) ,$numpreant ) ";
          pg_exec($sqlx) or die(db_log("ERRO SQL: $sqlx", $sArqLog));
        }
      }else if ($tipo == 2) {

        /* select para verificar se ja existe */
        $sqlPercInscr = " select inscr, numpre from tmparreinscr where inscr = $k00_origem and numpre = $numpreant ";
        $rsPercInscr  = pg_query($sqlPercInscr) or die(db_log("ERRO SQL: $sqlPercInscr", $sArqLog));
        $intAchou     = pg_num_rows($rsPercInscr);
        if ( $intAchou > 0 ){
          $sqlx = " update tmparreinscr
                       set perc = ( perc + ( ".( $PercCalc )." ) )
                     where inscr = $k00_origem and numpre = $numpreant ";
          pg_exec($sqlx) or die(db_log("ERRO SQL: $sqlx", $sArqLog));
        } else {

          $sqlx = " insert into tmparreinscr (inscr,perc,numpre)
                         values ( $k00_origem, (".( $PercCalc )." ) ,$numpreant ) ";
          pg_exec($sqlx) or die(db_log("ERRO SQL: $sqlx", $sArqLog));
        }
      }
    }//3
  }//2
}//1

db_log("", $sArqLog);
db_log(" Executando update para arredondamento dos valores... ", $sArqLog);

$sqlx = "update tmparreinscr set perc = round(perc,2);";
pg_exec($sqlx) or die(db_log("ERRO SQL: $sqlx", $sArqLog));

$sqlx = "update tmparrematric set perc = round(perc,2);";
pg_exec($sqlx) or die(db_log("ERRO SQL: $sqlx", $sArqLog));

db_log("", $sArqLog);
db_log(" Processando diferenças... ", $sArqLog);

$sSqlAcertaDiferenca  = " select numpre,                                   ";
$sSqlAcertaDiferenca .= "       sum(perc) as perc,                         ";
$sSqlAcertaDiferenca .= "       ( 100 - sum(perc) )                        ";
$sSqlAcertaDiferenca .= "  from ( select numpre,                           ";
$sSqlAcertaDiferenca .= "                perc                              ";
$sSqlAcertaDiferenca .= "           from tmparrematric                     ";
$sSqlAcertaDiferenca .= "          union all                               ";
$sSqlAcertaDiferenca .= "         select numpre,                           ";
$sSqlAcertaDiferenca .= "                perc                              ";
$sSqlAcertaDiferenca .= "           from tmparreinscr) as x                ";
$sSqlAcertaDiferenca .= " group by numpre                                  ";
$sSqlAcertaDiferenca .= "having round(sum(perc),2) <> 100.00               ";
$sSqlAcertaDiferenca .= "   and ( 100 - sum(perc) ) between -1.00 and 1.00 ";

$rsAcertaDiferenca    = pg_query($sSqlAcertaDiferenca);
$iNumRowsDiferenca    = pg_num_rows($rsAcertaDiferenca);

for ($i = 0;$i < $iNumRowsDiferenca; $i++) {

  db_fieldsmemory($rsAcertaDiferenca,$i);

  $nDif = round((100.00 - $perc),2);

  /**
   * Caso a diferença seja positiva(a maior) subtrai do menor valor
   * Caso a diferença seja negativa(a menor) subtrai do maior valor
   */
  if ($nDif < 0) {
    $sOrder = " desc";
  } else {
    $sOrder = " asc";
  }

  db_log("", $sArqLog);
  db_log(" Encontrada diferença para o numpre: $numpre. Valor da Diferença: $nDif ", $sArqLog);

  $sSqlCampos = "select *
                   from (select 'matric'      as xcampo,
                                'tmparrematric'  as xtabela,
                                matric        as xvalor,
                                sum(perc)     as perc_min
                           from tmparrematric
                          where numpre = $numpre
                          group by matric
                          union all
                         select 'inscr'      as xcampo,
                                'tmparreinscr'  as xtabela,
                                inscr        as xvalor,
                                sum(perc)    as perc_min
                           from tmparreinscr
                          where numpre = $numpre
                          group by inscr) as x
                  order by perc_min {$sOrder} limit 1";
  $rsCampos = pg_query($sSqlCampos);
  db_fieldsmemory($rsCampos,0);

  /*
   * Caso a diferença seja negativa e igual ou maior que o percentual já existente para o débito não altera o registro
   * Pois poderia causar inconsistencia nos percentuais ficando registros negativos
   */
  if ( ($nDif < 0) && (($perc_min-abs($nDif)) < 0) ) {
    db_log(" Diferença não pode ser corrigida pois é maior que o percentual atual do débito. Percentual Atual: {$perc_min} Valor da Diferença: {$nDif} ", $sArqLog);
    continue;
  }

  $sUpdate = "update {$xtabela} set perc = perc + {$nDif} where numpre = {$numpre} and $xcampo = $xvalor";
  pg_query($sUpdate);
}

if ($isTeste != "TESTE") {

  db_log("", $sArqLog);
  db_log(" Executando delete e insert em arrematric e arreinscr ... ", $sArqLog);

  pg_query("delete from arrematric
             using tmparrematric
             where tmparrematric.matric = arrematric.k00_matric
               and tmparrematric.numpre = arrematric.k00_numpre ");
  pg_query("insert into arrematric (k00_numpre,k00_matric,k00_perc) select numpre,matric,perc from tmparrematric");

  pg_query("delete from arreinscr
             using tmparreinscr
             where tmparreinscr.inscr = arreinscr.k00_inscr
               and tmparreinscr.numpre = arreinscr.k00_numpre ");
  pg_query("insert into arreinscr (k00_numpre,k00_inscr,k00_perc) select numpre,inscr,perc from tmparreinscr");
} else {
  db_log("Modo TESTE... Gerada as tabelas tmparreinscr e tmparrematric", $sArqLog);
}

if ($erro == false) {

  pg_exec($conn, "commit;") or die(db_log("ERRO SQL: commit;", $sArqLog));
  db_log("", $sArqLog);
  db_log(" Processamento concluido... ", $sArqLog);
} else {

  pg_exec($conn, "rollback;") or die(db_log("ERRO SQL: rollback;", $sArqLog));
  db_log("", $sArqLog);
  db_log("Erro durante o processamento... {$erromsg}", $sArqLog);
  exit;
}

db_log("", $sArqLog);
db_log(" valorzero: $valorzero", $sArqLog);

db_log("");
db_log("Inicio: $str_hora", $sArqLog);
db_log("Final.: " . date( "h:m:s"), $sArqLog);

db_log("", $sArqLog);
db_log("*** FINAL Script ".basename(__FILE__)." ***", $sArqLog);
db_log("", $sArqLog);

function db_fieldsmemory($recordset,$indice,$formatar="",$mostravar=false){
  $fm_numfields = pg_numfields($recordset);
  for ($i = 0;$i < $fm_numfields;$i++){
    $matriz[$i] = pg_fieldname($recordset,$i);
    global $$matriz[$i];
  $aux = trim(pg_result($recordset,$indice,$matriz[$i]));
  if(!empty($formatar)) {
      switch(pg_fieldtype($recordset,$i)) {
      case "float8":
      case "float4":
      case "float":
          $$matriz[$i] = number_format($aux,2,".","");
          if($mostravar==true) echo $matriz[$i]."->".$$matriz[$i]."<br>";
      break;
    case "date":
          if($aux!=""){
        $data = split("-",$aux);
        $$matriz[$i] = $data[2]."/".$data[1]."/".$data[0];
      }else{
        $$matriz[$i] = "";
      }
          if($mostravar==true) echo $matriz[$i]."->".$$matriz[$i]."<br>";
      break;
    default:
          $$matriz[$i] = $aux;
          if($mostravar==true) echo $matriz[$i]."->".$$matriz[$i]."<br>";
      break;
    }
  } else
      switch(pg_fieldtype($recordset,$i)) {
    case "date":
      $datav = split("-",$aux);
          $split_data = $matriz[$i]."_dia";
          global $$split_data;
          $$split_data =  @$datav[2];
          if($mostravar==true) echo $split_data."->".$$split_data."<br";
          $split_data = $matriz[$i]."_mes";
          global $$split_data;
          $$split_data =  @$datav[1];
          if($mostravar==true) echo $split_data."->".$$split_data."<br>";
          $split_data = $matriz[$i]."_ano";
          global $$split_data;
          $$split_data =  @$datav[0];
          if($mostravar==true) echo $split_data."->".$$split_data."<br>";
          $$matriz[$i] = $aux;
          if($mostravar==true) echo $matriz[$i]."->".$$matriz[$i]."<br>";
      break;
    default:
          $$matriz[$i] = $aux;
          if($mostravar==true) echo $matriz[$i]."->".$$matriz[$i]."<br>";
      break;
    }
  }
}