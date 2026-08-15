<?php
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2013  DBselller Servicos de Informatica             
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

require modification("libs/db_stdlib.php");
require modification("libs/db_utils.php");
require modification("libs/db_conecta.php");
include modification("libs/db_sessoes.php");
include modification("libs/db_usuariosonline.php");
include modification("dbforms/db_funcoes.php");

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

function buscaContaDespesa($codele, $empenho){
  //$sql = pg_query("SELECT DISTINCT conplano.c60_estrut from conplanoconplanoorcamento inner join conplano on conplano.c60_codcon = conplanoconplanoorcamento.c72_conplano and conplano.c60_anousu = conplanoconplanoorcamento.c72_anousu left join conplanoreduz on conplano.c60_codcon = conplanoreduz.c61_codcon and conplano.c60_anousu = conplanoreduz.c61_anousu inner join conplanoorcamento on conplanoorcamento.c60_codcon = conplanoconplanoorcamento.c72_conplanoorcamento and conplanoorcamento.c60_anousu = conplanoconplanoorcamento.c72_anousu left join conplanoorcamentoanalitica on conplanoorcamento.c60_codcon = conplanoorcamentoanalitica.c61_codcon and conplanoorcamento.c60_anousu = conplanoorcamentoanalitica.c61_anousu where c72_conplanoorcamento = {$codele} and c72_anousu = 2024");
  $sql = pg_query("SELECT e64_codele, o56_elemento, o56_descr from empelemento inner join empempenho on empempenho.e60_numemp = empelemento.e64_numemp inner join orcelemento on orcelemento.o56_codele = empelemento.e64_codele and orcelemento.o56_anousu = empempenho.e60_anousu inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo where e64_codele = {$codele} AND empelemento.e64_numemp = {$empenho} order by e64_codele");

  $resultado = pg_fetch_all($sql);

  //var_dump(substr($resultado[0]["o56_elemento"], 1, 8));
  //die("Confere");

  //return $resultado[0]["o56_elemento"];
  return substr($resultado[0]["o56_elemento"], 1, 8);
}

function inciso1(){
  $instit = db_getsession("DB_instit");
  $sql = pg_query("SELECT x.*, substr(saldos,1,1)::int as tipo, coalesce(substr(saldos,2,13)::float8, 0) as anterior, coalesce(substr(saldos,15,13)::float8, 0) as debitado , coalesce(substr(saldos,28,13)::float8, 0) as creditado, coalesce(substr(saldos,41,13)::float8, 0) as atual from ( select k13_conta, k13_descr, c60_estrut, gestao, o15_recurso, descricao, o15_complemento, fc_saltessaldo(k13_conta,'2024-12-31','2024-12-31',null, 1) as saldos, o15_codigo from saltes join conplanoexe on c62_anousu = 2024 and c62_reduz = k13_conta join conplanoreduz on c61_anousu = c62_anousu and c61_reduz = c62_reduz and c61_instit = {$instit} join conplano on c61_codcon = c60_codcon and c61_anousu = c60_anousu join orctiporec on o15_codigo = c61_codigo join fonterecurso on orctiporec_id = o15_codigo and exercicio = c62_anousu where c60_codsis in (5,6) and (k13_limite is null or k13_limite >= '2024-12-31') ) as x order by o15_recurso, gestao, o15_complemento, k13_descr");

  $resultado = pg_fetch_all($sql);
  return $resultado;
}

function inciso5(){
  $instit = db_getsession("DB_instit");
  $sql = pg_query("SELECT e91_numemp, o15_codigo, o15_loaespecificacao, e91_vlremp, e91_vlranu, e91_vlrliq, e91_vlrpag, e91_recurso, gestao, e91_anousu, o15_descr, o15_recurso, vlranu, vlrliq, vlrpag, vlrpagnproc, e91_codtipo, e90_descr, z01_numcgm, z01_nome, z01_cgccpf, e60_numemp, e60_codemp, e60_emiss, e60_anousu, o58_orgao, o58_unidade, o58_codigo, o58_funcao, o58_subfuncao, o56_elemento, o58_programa, o58_projativ, o52_descr, o54_descr, o55_descr, o56_descr, o40_descr, o41_descr, o53_descr, vlranuliq, vlranuliqnaoproc, c70_anousu, e64_codele, db21_tipoinstit, e60_instit, nomeinst from ( select e91_numemp, o15_codigo, o15_loaespecificacao, e91_anousu, e91_codtipo, e90_descr, o15_descr, o15_recurso, e91_recurso, fonterecurso.gestao, c70_anousu, o15_complemento, coalesce(e91_vlremp,0) as e91_vlremp, coalesce(e91_vlranu,0) as e91_vlranu, coalesce(e91_vlrliq,0) as e91_vlrliq, coalesce(e91_vlrpag,0) as e91_vlrpag, coalesce(vlranu,0) as vlranu, coalesce(vlranuliq,0) as vlranuliq, coalesce(vlranuliqnaoproc,0) as vlranuliqnaoproc, coalesce(vlrliq,0) as vlrliq, coalesce(vlrpag,0) as vlrpag, coalesce(vlrpagnproc,0) as vlrpagnproc from empresto inner join emprestotipo on e91_codtipo = e90_codigo inner join orctiporec on e91_recurso = o15_codigo inner join fonterecurso on fonterecurso.orctiporec_id = orctiporec.o15_codigo and fonterecurso.exercicio = 2024 left outer join ( select c75_numemp, c70_anousu, sum( round( case when c53_tipo = 11 then c70_valor else 0 end,2) ) as vlranu, sum( round(case when c71_coddoc = 31 then c70_valor else 0 end,2) ) as vlranuliq, sum( round(case when c71_coddoc = 32 then c70_valor else 0 end,2) ) as vlranuliqnaoproc, sum( round(case when c53_tipo = 20 then c70_valor else ( case when c53_tipo = 21 then c70_valor*-1 else 0 end) end,2) ) as vlrliq, sum( round(case when c71_coddoc in (35, 6008) then c70_valor else ( case when c71_coddoc in (36, 6009) then c70_valor*-1 else 0 end) end,2) ) as vlrpag, sum( round( case when c71_coddoc in (37, 6010) then c70_valor else ( case when c71_coddoc in (38, 6011) then c70_valor*-1 else 0 end) end ,2) ) as vlrpagnproc from conlancamemp inner join conlancamdoc on c71_codlan = c75_codlan inner join conhistdoc on c53_coddoc = c71_coddoc inner join conlancam on c70_codlan = c75_codlan inner join empempenho on e60_numemp = c75_numemp where e60_anousu < 2024 and c75_data between '2024-12-01' and '2024-12-31' and e60_instit = {$instit} and c70_anousu = 2024 group by c75_numemp,c70_anousu ) as x on x.c75_numemp = e91_numemp where e91_anousu = 2024 ) as x inner join empempenho on e60_numemp = e91_numemp and e60_instit = {$instit} inner join db_config on db_config.codigo = empempenho.e60_instit inner join db_tipoinstit on db_tipoinstit.db21_codtipo = db_config.db21_tipoinstit inner join empelemento on e64_numemp = e60_numemp inner join cgm on z01_numcgm = e60_numcgm inner join orcdotacao on o58_coddot = e60_coddot and o58_anousu = e60_anousu and o58_instit = e60_instit inner join orcorgao on o40_orgao = o58_orgao and o40_anousu = o58_anousu inner join orcunidade on o41_anousu = o58_anousu and o41_orgao = o58_orgao and o41_unidade = o58_unidade inner join orcfuncao on o52_funcao = orcdotacao.o58_funcao inner join orcsubfuncao on o53_subfuncao = orcdotacao.o58_subfuncao inner join orcprograma on o54_programa = o58_programa and o54_anousu = orcdotacao.o58_anousu inner join orcprojativ on o55_projativ = o58_projativ and o55_anousu = orcdotacao.o58_anousu inner join orcelemento on e64_codele = o56_codele and o58_anousu = o56_anousu left join origemcomplementorecurso on o15_codigo = o206_recurso and o206_numero = e60_numemp and o206_origem = 10 where 1 = 1 and 1=1 order by e60_anousu, e91_recurso, e60_codemp::integer");
  $resultado = pg_fetch_all($sql);
  return $resultado;
  
}


function inciso6(){
  $instit = db_getsession("DB_instit");
  
  $sql = pg_query("SELECT empempenho.e60_numemp, e60_resumo, o58_codele, empelemento.e64_codele, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, yyy.e60_vlremp, yyy.e60_vlranu, yyy.e60_vlrliq, e63_codhist, e40_descr, yyy.e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, fc_estruturaldotacao(e60_anousu,e60_coddot) as dl_estrutural, e60_codcom, pc50_descr, e60_concarpeculiar from ( select e60_numemp, sum(case when c53_tipo = 10 then c70_valor else 0 end) as e60_vlremp, sum(case when c53_tipo = 11 then c70_valor else 0 end) as e60_vlranu, sum(case when c53_tipo = 20 then c70_valor else 0 end) - sum(case when c53_tipo = 21 then c70_valor else 0 end) as e60_vlrliq, sum(case when c53_tipo = 30 then c70_valor else 0 end) - sum(case when c53_tipo = 31 then c70_valor else 0 end) as e60_vlrpag from ( select e60_numemp, c53_tipo, sum(c70_valor) as c70_valor from ( select e60_numemp, e60_anousu, e60_coddot from empempenho where e60_instit = {$instit} and e60_emiss between '2024-01-01' and '2024-12-31' ) as xxx inner join orcdotacao on orcdotacao.o58_anousu = xxx.e60_anousu and orcdotacao.o58_coddot = xxx.e60_coddot inner join orcelemento on orcelemento.o56_codele = orcdotacao.o58_codele and orcelemento.o56_anousu = orcdotacao.o58_anousu inner join conlancamemp on c75_numemp = xxx.e60_numemp inner join conlancam on c70_codlan = c75_codlan and c70_data <= '2024-12-31' inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc and c53_tipo in (10,11,20,21,30,31) inner join conlancamdot on c73_codlan = c75_codlan group by e60_numemp, c53_tipo ) as xxx group by e60_numemp ) as yyy inner join empempenho on empempenho.e60_numemp = yyy.e60_numemp inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo inner join db_config as a on a.codigo = orcdotacao.o58_instit inner join origemcomplementorecurso on origemcomplementorecurso.o206_numero = empempenho.e60_numemp and origemcomplementorecurso.o206_origem = 1 inner join orctiporec on orctiporec.o15_codigo = origemcomplementorecurso.o206_recurso inner join orcfuncao on orcfuncao.o52_funcao = orcdotacao.o58_funcao inner join orcsubfuncao on orcsubfuncao.o53_subfuncao = orcdotacao.o58_subfuncao inner join orcprograma on orcprograma.o54_anousu = orcdotacao.o58_anousu and orcprograma.o54_programa = orcdotacao.o58_programa inner join orcelemento on orcelemento.o56_codele = orcdotacao.o58_codele and orcelemento.o56_anousu = orcdotacao.o58_anousu inner join orcprojativ on orcprojativ.o55_anousu = orcdotacao.o58_anousu and orcprojativ.o55_projativ = orcdotacao.o58_projativ inner join orcorgao on orcorgao.o40_anousu = orcdotacao.o58_anousu and orcorgao.o40_orgao = orcdotacao.o58_orgao inner join orcunidade on orcunidade.o41_anousu = orcdotacao.o58_anousu and orcunidade.o41_orgao = orcdotacao.o58_orgao and orcunidade.o41_unidade = orcdotacao.o58_unidade left join empemphist on empemphist.e63_numemp = empempenho.e60_numemp left join emphist on emphist.e40_codhist = empemphist.e63_codhist inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom INNER JOIN empelemento ON e64_numemp = empempenho.e60_numemp where e60_instit = {$instit} and e60_emiss between '2024-01-01' and '2024-12-31' and 1=1 order by to_number(e60_codemp::text,'9999999999') , e60_numemp, e60_emiss, z01_nome, e60_anousu");


  $resultado = pg_fetch_all($sql);
  return $resultado;
}

function inciso7(){
  $instit = db_getsession("DB_instit");

  $sql = pg_query("SELECT * from ( select x.e60_resumo, x.e60_numemp, x.e60_codemp, x.e60_emiss, x.e60_numcgm, x.z01_nome, x.z01_cgccpf, x.z01_munic, x.e63_codhist, x.e40_descr, x.e60_anousu, x.e60_coddot, x.o58_coddot, x.o58_orgao, x.o40_orgao, x.o40_descr, x.o58_unidade, x.o41_descr, x.o15_codigo, x.o15_descr, x.dl_estrutural, x.e60_codcom, x.pc50_descr, empelemento.e64_codele, orcelemento.o56_descr, x.e60_vlremp, x.e60_vlranu, x.e60_vlrliq, x.e60_vlrpag, empelemento.e64_vlremp, empelemento.e64_vlrliq, empelemento.e64_vlranu, empelemento.e64_vlrpag, x.e60_concarpeculiar from (select distinct e60_numemp, to_number(e60_codemp::text,'9999999999') as e60_codemp, e60_resumo, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, descricao as o15_descr, fc_estruturaldotacao(e60_anousu,e60_coddot) as dl_estrutural, e60_codcom, pc50_descr,e60_concarpeculiar from empempenho join cgm on cgm.z01_numcgm = empempenho.e60_numcgm join db_config on db_config.codigo = empempenho.e60_instit join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo join db_config as a on a.codigo = orcdotacao.o58_instit join origemcomplementorecurso on origemcomplementorecurso.o206_numero = empempenho.e60_numemp and origemcomplementorecurso.o206_origem = 1 join orctiporec on orctiporec.o15_codigo = origemcomplementorecurso.o206_recurso join fonterecurso on fonterecurso.orctiporec_id = orctiporec.o15_codigo and fonterecurso.exercicio = orcdotacao.o58_anousu join orcfuncao on orcfuncao.o52_funcao = orcdotacao.o58_funcao join orcsubfuncao on orcsubfuncao.o53_subfuncao = orcdotacao.o58_subfuncao join orcprograma on orcprograma.o54_anousu = orcdotacao.o58_anousu and orcprograma.o54_programa = orcdotacao.o58_programa join orcelemento on orcelemento.o56_codele = orcdotacao.o58_codele and orcelemento.o56_anousu = orcdotacao.o58_anousu join orcprojativ on orcprojativ.o55_anousu = orcdotacao.o58_anousu and orcprojativ.o55_projativ = orcdotacao.o58_projativ join orcorgao on orcorgao.o40_anousu = orcdotacao.o58_anousu and orcorgao.o40_orgao = orcdotacao.o58_orgao join orcunidade on orcunidade.o41_anousu = orcdotacao.o58_anousu and orcunidade.o41_orgao = orcdotacao.o58_orgao and orcunidade.o41_unidade = orcdotacao.o58_unidade left join empemphist on empemphist.e63_numemp = empempenho.e60_numemp left join emphist on emphist.e40_codhist = empemphist.e63_codhist join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom join empempitem on e62_numemp = e60_numemp where e60_instit = {$instit} and e60_emiss between '2024-01-01' and '2024-12-31' AND e60_vlranu > 0 and 1=1 order by to_number(e60_codemp::text,'9999999999') , e60_numemp, e60_emiss, z01_nome, e60_anousu) as x join empelemento on x.e60_numemp = e64_numemp join orcelemento on o56_codele = e64_codele and o56_anousu = x.e60_anousu group by x.e60_resumo, x.e60_numemp, x.e60_codemp, x.e60_emiss, x.e60_numcgm, x.z01_nome, x.z01_cgccpf, x.z01_munic, x.e63_codhist, x.e40_descr, x.e60_anousu, x.e60_coddot, x.o58_coddot, x.o58_orgao, x.o40_orgao, x.o40_descr, x.o58_unidade, x.o41_descr, x.o15_codigo, x.o15_descr, x.dl_estrutural, x.e60_codcom, x.pc50_descr, empelemento.e64_codele, orcelemento.o56_descr, x.e60_vlremp, x.e60_vlranu, x.e60_vlrliq, x.e60_vlrpag, empelemento.e64_vlremp, empelemento.e64_vlrliq, empelemento.e64_vlranu, empelemento.e64_vlrpag, x.e60_concarpeculiar) as x order by to_number(e60_codemp::text,'9999999999') , e60_numemp, e60_emiss, z01_nome, e60_anousu");

  $resultado = pg_fetch_all($sql);
  return $resultado;
}

function buscaMotivoAnulacao($seqempenho){
  $sql = pg_query("SELECT e94_motivo FROM empanulado WHERE e94_numemp = {$seqempenho}");
  $resultado = pg_fetch_all($sql);
  $motivo = trim($resultado[0]["e94_motivo"]);
  $motivo = substr($motivo, 0, 250);
  return $motivo;
}

function buscaDePara($codigo){
  $sql = pg_query("SELECT o15_codigo, fonterecurso.codigo_siconfi, fonterecurso.gestao, o15_recurso, fonterecurso.descricao, o15_complemento, o200_descricao from orctiporec join fonterecurso on orctiporec_id = o15_codigo join complementofonterecurso on o200_sequencial = o15_complemento where exercicio = 2024 AND o15_codigo = {$codigo}");
  $resultado = pg_fetch_all($sql);
  return trim($resultado[0]["codigo_siconfi"]);
}

$fontessubelemento = array(            
            31900805 => 33900805,
            31900901 => 33900856,
            33901899 => 33901801,
            33904022 => 33904014,
            33901414 => 33901401,
            33903006 => 33903007,
            33903696 => 33903699,
            44905212 => 44905208,
            44905206 => 44905208,
            33903981 => 33903999,            
            33909218 => 33901801,
            33903937 => 33903936,
            33901499 => 33901401,
            33904713 => 33904705,
            46907301 => 46907399,
            46907101 => 46907101,
            32902104 => 32902101,
            32902106 => 32902101,
            46907106 => 46907101,
            46907104 => 46907101,
            33903004 => 33903001,
            33903096 => 33903094,
            33904002 => 33904001,
            33903058 => 33903024,
            44905235 => 44905299,
            33909600 => 33909601,
            33904004 => 33904001,
            33903054 => 33903024,
            33904011 => 33904012,
            33904003 => 33904012,
            33903022 => 33903021,
            33903992 => 33903990,
            33903057 => 33903024,
            44905234 => 44905299,
            33904801 => 33904899,
            33903966 => 33903906,
            33909299 => 33909240,
            33903003 => 33903001,
            44904005 => 44904099,
            33904501 => 33904599,
            33904603 => 33904601,
            33904602 => 33904601,
            33903055 => 33903024,
            33903056 => 33903024,
            44905237 => 44905299,
            33904013 => 33904014,
            33903499 => 33903400,
            33904007 => 33904099,
            13220011 => 13220101,
            13999901 => 19999921,
            13999901 => 19999921,
            19909912 => 19999922,
            19909912 => 19999922,
            19909913 => 19999923,
            19909913 => 19999923,
            46907105 => 46907101,
            33904715 => 33904705,
            46907110 => 46907101,
            46907111 => 46907101,
            46907112 => 46907101,
            33903401 => 33903400,
            46907108 => 46907101,
            46907109 => 46907101,
            13900011 => 13999901,
            33903400 => 33903401,
            44904005 => 44904099,
            33904501 => 33904599,
            33903054 => 33903099,
            33903003 => 33903001,
            33903058 => 33903024,
            33903004 => 33903001,
            44905210 => 44905208,
            33903914 => 33903913,
            44905204 => 44905208,
            33903906 => 33903906,
            33903401 => 33903499,
            33903946 => 33903906,
            33903499 => 33903401,
            44905238 => 44905299,
            44905238 => 44905299,
            44905238 => 44905299,
            33904017 => 33904099,
            33903400 => 33903401,
            33904023 => 33904099,
            33903400 => 33903401,
            33904017 => 33904099,
            33904007 => 33904099,
            33904013 => 33904014,
            33904017 => 33904099,
            33904013 => 33904014,
            33904021 => 33904099,
            44905239 => 44905299,        
            44905204 => 44905299,
            44905226 => 44905299,
            45906109 => 45906199,
            44905233 => 44905208,
            45906109 => 45909261,
            44906100 => 44906101,
            31909106 => 31909126,
            33903659 => 33903649,
            44905230 => 44905299,
            33504103 => 33504199,
            33904600 => 33904601,
            31903400 => 31900499,
            44905251 => 44905242,
            44905230 => 44905299,            
            33903050 => 33903099,
            44905241 => 44905299,
            44905228 => 44905299,
            44906107 => 44906103,
            44717000 => 44717001,
            33717000 => 33717001,
            44905192 => 44905103,
            17235040 => 17235001,
            33727000 => 33729901,
            33904800 => 33904899,
            33723900 => 33723901,
            32902105 => 32902101,
            33903996 => 33903999
        );




if($_POST){
  if(!isset($_POST["inciso"])){
    echo "<script>alert('Escolha pelo menos uma opção.');</script>";
  }else{    

  
  require_once 'model/contabilidade/arquivos/etcerj/vendor/PHPExcel.php';



//Até aqui funciona


//header('Content-Type: text/html; charset=utf-8');
//header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
//header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
//header('Content-Length: ' . filesize($fileName));
//ob_clean();
//flush();
//readfile($fileName);
//unlink($fileName);
//exit;



  $incisos = $_POST["inciso"];
  
  foreach ($incisos as $inciso) {

    if($inciso == "inciso1"){
      $objPHPExcel = new PHPExcel();
      $sheet = $objPHPExcel->getActiveSheet();
      

      $sheet->setCellValue('A1', utf8_encode('Fonte de Recurso'));
      $sheet->setCellValue('B1', utf8_encode('Tipo'));
      $sheet->setCellValue('C1', utf8_encode('Saldo'));

      $dados = inciso1();
      $dadosinciso1 = array();
      
      $indice = 0;


      foreach ($dados as $linha){

        if($linha["o15_codigo"] == 211){
          $fonterecurso = 1700;
        }elseif($linha["o15_codigo"] == 220){
          if(db_getsession("DB_instit") == 1){
            $fonterecurso = 1500;
          }else{
            $fonterecurso = 1501;
          }
        }elseif($linha["o15_codigo"] == 224){
          $fonterecurso = 1799;
        }elseif($linha["o15_codigo"] == 8){
          $fonterecurso = 1704;
        }elseif($linha["o15_codigo"] == 92){
          $fonterecurso = 1700;
        }elseif($linha["o15_codigo"] == 170 || $linha["o15_codigo"] == 171){
          $fonterecurso = 1700;
        }elseif($linha["o15_codigo"] == 173){
          $fonterecurso = 1751;
        }elseif($linha["o15_codigo"] == 178){
          $fonterecurso = 1703;
        }elseif($linha["o15_codigo"] == 1){
  $fonterecurso = 1500;
}
elseif($linha["o15_codigo"] == 2){
  $fonterecurso = 1700;
}
elseif($linha["o15_codigo"] == 3){
  $fonterecurso = 1500;
}
elseif($linha["o15_codigo"] == 4){
  $fonterecurso = 1500;
}
elseif($linha["o15_codigo"] == 5){
  $fonterecurso = 1569;
}
elseif($linha["o15_codigo"] == 6){
  $fonterecurso = 1751;
}
elseif($linha["o15_codigo"] == 7){
  $fonterecurso = 1500;
}
elseif($linha["o15_codigo"] == 9){
  $fonterecurso = 1500;
}
elseif($linha["o15_codigo"] == 10){
  $fonterecurso = 1700;
}
elseif($linha["o15_codigo"] == 11){
  $fonterecurso = 1551;
}
elseif($linha["o15_codigo"] == 12){
  $fonterecurso = 1552;
}
elseif($linha["o15_codigo"] == 13){
  $fonterecurso = 1500;
}
elseif($linha["o15_codigo"] == 14){
  $fonterecurso = 1700;
}
elseif($linha["o15_codigo"] == 15){
  $fonterecurso = 1500;
}
elseif($linha["o15_codigo"] == 16){
  $fonterecurso = 1569;
}
elseif($linha["o15_codigo"] == 17){
  $fonterecurso = 1569;
}
elseif($linha["o15_codigo"] == 18){
  $fonterecurso = 1700;
}
elseif($linha["o15_codigo"] == 19){
  $fonterecurso = 1700;
}
elseif($linha["o15_codigo"] == 20){
  $fonterecurso = 1601;
}
elseif($linha["o15_codigo"] == 21){
  $fonterecurso = 1701;
}
elseif($linha["o15_codigo"] == 22){
  $fonterecurso = 1700;
}
elseif($linha["o15_codigo"] == 23){
  $fonterecurso = 1540;
}
elseif($linha["o15_codigo"] == 24){
  $fonterecurso = 1704;
}
elseif($linha["o15_codigo"] == 25){
  $fonterecurso = 1700;
}
elseif($linha["o15_codigo"] == 26){
  $fonterecurso = 1700;
}
elseif($linha["o15_codigo"] == 27){
  $fonterecurso = 1550;
}
elseif($linha["o15_codigo"] == 28){
  $fonterecurso = 1701;
}
elseif($linha["o15_codigo"] == 29){
  $fonterecurso = 1701;
}
elseif($linha["o15_codigo"] == 30){
  $fonterecurso = 1700 ;
}
elseif($linha["o15_codigo"] == 32){
  $fonterecurso = 1569;
}
elseif($linha["o15_codigo"] == 33){
  $fonterecurso = 1569;
}
elseif($linha["o15_codigo"] == 45){
  $fonterecurso = 1553;
}
elseif($linha["o15_codigo"] == 56){
  $fonterecurso = 1601;
}
elseif($linha["o15_codigo"] == 57){
  $fonterecurso = 1601 ;
}
elseif($linha["o15_codigo"] == 58){
  $fonterecurso = 1601;
}
elseif($linha["o15_codigo"] == 61){
  $fonterecurso = 1601;
}
elseif($linha["o15_codigo"] == 79){
  $fonterecurso = 1601;
}
elseif($linha["o15_codigo"] == 81){
  $fonterecurso = 1601;
}
elseif($linha["o15_codigo"] == 83){
  $fonterecurso = 1601;
}
elseif($linha["o15_codigo"] == 90){
  $fonterecurso = 1551;
}
elseif($linha["o15_codigo"] == 93){
  $fonterecurso = 1701;
}
elseif($linha["o15_codigo"] == 96){
  $fonterecurso = 1569;
}
elseif($linha["o15_codigo"] == 105){
  $fonterecurso = 1601;
}
elseif($linha["o15_codigo"] == 123){
  $fonterecurso = 1601;
}
elseif($linha["o15_codigo"] == 167){
  $fonterecurso = 1569;
}
elseif($linha["o15_codigo"] == 193){
  $fonterecurso = 1621;
}
elseif($linha["o15_codigo"] == 400){
  $fonterecurso = 1501;
}
elseif($linha["o15_codigo"] == 8001){
  $fonterecurso = 1500;
}else{
          $fonterecurso = buscaDePara($linha["o15_codigo"]);
        }
        if($fonterecurso == "0"){$fonterecurso = 1700;}

        $dadosinciso1[$indice]["tipo"] = $linha["c60_estrut"];
        $dadosinciso1[$indice]["fontederecurso"] = $fonterecurso; //$linha["o15_recurso"];
        $dadosinciso1[$indice]["saldo"] = $linha["atual"];
        $indice++;
      }

      $agrupado = array();
      foreach ($dadosinciso1 as $item) {
        if(substr($item['tipo'], 0, 7) == 1111101){
          $tipo = 1;
        }elseif(substr($item['tipo'], 0, 7) == 1111150){
          $tipo = 3;
        }else{
          $tipo = 2;
        }
        $item['tipo'] = $tipo;

        $chave = $item['tipo'] . '_' . $item['fontederecurso'];
        
        if (isset($agrupado[$chave])) {
          $agrupado[$chave]['saldo'] += $item['saldo'];
        } else {
          //var_dump($item["fontederecurso"]); echo "<br>";
          $agrupado[$chave]["tipo"] = $tipo;
          $agrupado[$chave]["fontederecurso"] = $item["fontederecurso"];
          $agrupado[$chave]["saldo"] = $item["saldo"];
        }
      }
      
      $celula = 2;
      foreach ($agrupado as $dados) {
        $saldo = number_format($dados["saldo"], 2, ",", "");
        
        $sheet->setCellValue('A'.$celula.'', utf8_encode($dados["fontederecurso"]));
        $sheet->setCellValue('B'.$celula.'', utf8_encode($dados["tipo"]));
        $sheet->setCellValue('C'.$celula.'', utf8_encode($saldo));
        $celula++;
      }
      $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
      $objWriter->save('IncisoI.xlsx');    
    }//FIM INCISO 1


    if($inciso == "inciso5"){
      $objPHPExcel = new PHPExcel();
      $sheet = $objPHPExcel->getActiveSheet();
      

      $sheet->setCellValue('A1', utf8_encode('Exercício'));
      $sheet->setCellValue('B1', utf8_encode('Fonte Recurso'));
      $sheet->setCellValue('C1', utf8_encode('RP Processado'));
      $sheet->setCellValue('D1', utf8_encode('RP Não Processado'));

      $dados = inciso5();
      //testa($dados); die("Confere");
      $dadosinciso5 = array();
      $indice = 0;
      
      foreach ($dados as $linha) {
        if($linha["o15_codigo"] == 211){
          $fonterecurso = 1700;
        }elseif($linha["o15_codigo"] == 220){
          if(db_getsession("DB_instit") == 1){
            $fonterecurso = 1500;
          }else{
            $fonterecurso = 1501;
          }
        }elseif($linha["o15_codigo"] == 224){
          $fonterecurso = 1799;
        }elseif($linha["o15_codigo"] == 8){
          $fonterecurso = 1704;
        }elseif($linha["o15_codigo"] == 92){
          $fonterecurso = 1700;
        }elseif($linha["o15_codigo"] == 170 || $linha["o15_codigo"] == 171){
          $fonterecurso = 1700;
        }elseif($linha["o15_codigo"] == 173){
          $fonterecurso = 1751;
        }elseif($linha["o15_codigo"] == 178){
          $fonterecurso = 1703;
        }elseif($linha["o15_codigo"] == 1){
  $fonterecurso = 1500;
}
elseif($linha["o15_codigo"] == 2){
  $fonterecurso = 1700;
}
elseif($linha["o15_codigo"] == 3){
  $fonterecurso = 1500;
}
elseif($linha["o15_codigo"] == 4){
  $fonterecurso = 1500;
}
elseif($linha["o15_codigo"] == 5){
  $fonterecurso = 1569;
}
elseif($linha["o15_codigo"] == 6){
  $fonterecurso = 1751;
}
elseif($linha["o15_codigo"] == 7){
  $fonterecurso = 1500;
}
elseif($linha["o15_codigo"] == 9){
  $fonterecurso = 1500;
}
elseif($linha["o15_codigo"] == 10){
  $fonterecurso = 1700;
}
elseif($linha["o15_codigo"] == 11){
  $fonterecurso = 1551;
}
elseif($linha["o15_codigo"] == 12){
  $fonterecurso = 1552;
}
elseif($linha["o15_codigo"] == 13){
  $fonterecurso = 1500;
}
elseif($linha["o15_codigo"] == 14){
  $fonterecurso = 1700;
}
elseif($linha["o15_codigo"] == 15){
  $fonterecurso = 1500;
}
elseif($linha["o15_codigo"] == 16){
  $fonterecurso = 1569;
}
elseif($linha["o15_codigo"] == 17){
  $fonterecurso = 1569;
}
elseif($linha["o15_codigo"] == 18){
  $fonterecurso = 1700;
}
elseif($linha["o15_codigo"] == 19){
  $fonterecurso = 1700;
}
elseif($linha["o15_codigo"] == 20){
  $fonterecurso = 1601;
}
elseif($linha["o15_codigo"] == 21){
  $fonterecurso = 1701;
}
elseif($linha["o15_codigo"] == 22){
  $fonterecurso = 1700;
}
elseif($linha["o15_codigo"] == 23){
  $fonterecurso = 1540;
}
elseif($linha["o15_codigo"] == 24){
  $fonterecurso = 1704;
}
elseif($linha["o15_codigo"] == 25){
  $fonterecurso = 1700;
}
elseif($linha["o15_codigo"] == 26){
  $fonterecurso = 1700;
}
elseif($linha["o15_codigo"] == 27){
  $fonterecurso = 1550;
}
elseif($linha["o15_codigo"] == 28){
  $fonterecurso = 1701;
}
elseif($linha["o15_codigo"] == 29){
  $fonterecurso = 1701;
}
elseif($linha["o15_codigo"] == 30){
  $fonterecurso = 1700 ;
}
elseif($linha["o15_codigo"] == 32){
  $fonterecurso = 1569;
}
elseif($linha["o15_codigo"] == 33){
  $fonterecurso = 1569;
}
elseif($linha["o15_codigo"] == 45){
  $fonterecurso = 1553;
}
elseif($linha["o15_codigo"] == 56){
  $fonterecurso = 1601;
}
elseif($linha["o15_codigo"] == 57){
  $fonterecurso = 1601 ;
}
elseif($linha["o15_codigo"] == 58){
  $fonterecurso = 1601;
}
elseif($linha["o15_codigo"] == 61){
  $fonterecurso = 1601;
}
elseif($linha["o15_codigo"] == 79){
  $fonterecurso = 1601;
}
elseif($linha["o15_codigo"] == 81){
  $fonterecurso = 1601;
}
elseif($linha["o15_codigo"] == 83){
  $fonterecurso = 1601;
}
elseif($linha["o15_codigo"] == 90){
  $fonterecurso = 1551;
}
elseif($linha["o15_codigo"] == 93){
  $fonterecurso = 1701;
}
elseif($linha["o15_codigo"] == 96){
  $fonterecurso = 1569;
}
elseif($linha["o15_codigo"] == 105){
  $fonterecurso = 1601;
}
elseif($linha["o15_codigo"] == 123){
  $fonterecurso = 1601;
}
elseif($linha["o15_codigo"] == 167){
  $fonterecurso = 1569;
}
elseif($linha["o15_codigo"] == 193){
  $fonterecurso = 1621;
}
elseif($linha["o15_codigo"] == 400){
  $fonterecurso = 1501;
}
elseif($linha["o15_codigo"] == 8001){
  $fonterecurso = 1500;
}else{
          $fonterecurso = buscaDePara($linha["o15_codigo"]);
        }
        if($fonterecurso == "0"){$fonterecurso = 1700;}
                
        $vprocessado = ($linha["e91_vlrliq"] - $linha["e91_vlrpag"]);
        $vnaoprocessado = ($linha["e91_vlremp"] - $linha["e91_vlranu"] - $linha["e91_vlrliq"]);

        $vprocessado = number_format($vprocessado, 2, ",", "");
        $vnaoprocessado = number_format($vnaoprocessado, 2, ",", "");
        
        $dadosinciso5[$indice]["exercicio"] = $linha["e60_anousu"];
        $dadosinciso5[$indice]["fontederecurso"] = $fonterecurso;
        $dadosinciso5[$indice]["rpprocessado"] = abs($vprocessado);
        $dadosinciso5[$indice]["rpnaoprocessado"] = abs($vnaoprocessado);
        $indice++;
      }

      $agrupado = array();
      foreach ($dadosinciso5 as $item) {
        $chave = $item['exercicio'] . '_' . $item['fontederecurso'];        
        if (isset($agrupado[$chave])) {
          $agrupado[$chave]['rpprocessado'] += $item['rpprocessado'];
          $agrupado[$chave]['rpnaoprocessado'] += $item['rpnaoprocessado'];
        } else {
          //var_dump($item["fontederecurso"]); echo "<br>";
          $agrupado[$chave]["exercicio"] = $item["exercicio"];
          $agrupado[$chave]["fontederecurso"] = $item["fontederecurso"];
          $agrupado[$chave]['rpprocessado'] = $item['rpprocessado'];
          $agrupado[$chave]['rpnaoprocessado'] = $item['rpnaoprocessado'];
        }
      }
      
      
      $celula = 2;
      foreach ($agrupado as $dados) {                
        $sheet->setCellValue('A'.$celula.'', utf8_encode($dados["exercicio"]));
        $sheet->setCellValue('B'.$celula.'', utf8_encode($dados["fontederecurso"]));
        $sheet->setCellValue('C'.$celula.'', utf8_encode($dados["rpprocessado"]));
        $sheet->setCellValue('D'.$celula.'', utf8_encode($dados["rpnaoprocessado"]));
        $celula++;
      }
      $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
      $objWriter->save('IncisoV.xlsx');
    
  }//FIM INCISO 5
  
    
    if($inciso == "inciso6"){
      $objPHPExcel = new PHPExcel();
      $sheet = $objPHPExcel->getActiveSheet();
      

      $sheet->setCellValue('A1', utf8_encode('Número'));
      $sheet->setCellValue('B1', utf8_encode('Orgão'));
      $sheet->setCellValue('C1', utf8_encode('Unidade Orçamentária'));
      $sheet->setCellValue('D1', utf8_encode('Fonte Recurso'));
      $sheet->setCellValue('E1', utf8_encode('Conta Despesa'));
      $sheet->setCellValue('F1', utf8_encode('CPF/CNPJ'));
      $sheet->setCellValue('G1', utf8_encode('Nome/Razão Social'));
      $sheet->setCellValue('H1', utf8_encode('Data do Empenho'));
      $sheet->setCellValue('I1', utf8_encode('Histórico'));
      $sheet->setCellValue('J1', utf8_encode('Empenhado'));
      $sheet->setCellValue('K1', utf8_encode('Liquidado'));
      $sheet->setCellValue('L1', utf8_encode('Pago'));
      $sheet->setCellValue('M1', utf8_encode('Essencial, Contínua e Preexistente'));
      $sheet->setCellValue('N1', utf8_encode('Justificativa'));

      $dados = inciso6();
      $dadosinciso6 = array();
      $indice = 0;
      
      foreach ($dados as $linha) {
        $elemento = buscaContaDespesa($linha["e64_codele"], $linha["e60_numemp"]);
        if($fontessubelemento[$elemento]){
          $elemento = $fontessubelemento[$elemento];
        }
        $valorempenhado = $linha["e60_vlremp"] - $linha["e60_vlranu"];
        $fonterecurso = buscaDePara($linha["o15_codigo"]);

        $dadosinciso6[$indice]["numero"] = $linha["e60_codemp"];
        $dadosinciso6[$indice]["orgao"] = $linha["o40_orgao"];
        $dadosinciso6[$indice]["unidadeorcamentaria"] = $linha["o58_unidade"];
        $dadosinciso6[$indice]["fontederecurso"] = $fonterecurso;
        //$dadosinciso6[$indice]["contadespesa"] = buscaContaDespesaInciso6($linha["e64_codele"], $linha["e60_numemp"]);
        $dadosinciso6[$indice]["contadespesa"] = $elemento;
        $dadosinciso6[$indice]["cpfcnpj"] = $linha["z01_cgccpf"];
        $dadosinciso6[$indice]["nome"] = $linha["z01_nome"];
        $dadosinciso6[$indice]["dataempenho"] = implode("/", array_reverse(explode("-", $linha["e60_emiss"])));
        $dadosinciso6[$indice]["historico"] = substr(trim($linha["e60_resumo"]), 0, 250);
        //$dadosinciso6[$indice]["empenhado"] = number_format($linha["e60_vlremp"], 2, ",", "");
        $dadosinciso6[$indice]["empenhado"] = number_format($valorempenhado, 2, ",", "");
        $dadosinciso6[$indice]["liquidado"] = number_format($linha["e60_vlrliq"], 2, ",", "");
        $dadosinciso6[$indice]["pago"] = number_format($linha["e60_vlrpag"], 2, ",", "");
        //$dadosinciso6[$indice]["essencial"] = ($linha["e60_emiss"] <= "2024-04-30") ? 2 : 1;
        //$dadosinciso6[$indice]["justificativa"] = ($dadosinciso6[$indice]["essencial"] == 1) ? "Contínua" : "";
        $dadosinciso6[$indice]["essencial"] = ($linha["e60_emiss"] <= "2024-04-30") ? "" : 1;
        $dadosinciso6[$indice]["justificativa"] = ($dadosinciso6[$indice]["essencial"] == 1) ? "Contínua" : "";
        $indice++;
      }
      
      $celula = 2;
      foreach ($dadosinciso6 as $dados) {                
        $sheet->setCellValue('A'.$celula.'', utf8_encode($dados["numero"]));
        $sheet->setCellValue('B'.$celula.'', utf8_encode($dados["orgao"]));
        $sheet->setCellValue('C'.$celula.'', utf8_encode($dados["unidadeorcamentaria"]));
        $sheet->setCellValue('D'.$celula.'', utf8_encode($dados["fontederecurso"]));
        $sheet->setCellValue('E'.$celula.'', utf8_encode($dados["contadespesa"]));
        $sheet->setCellValue('F'.$celula.'', utf8_encode($dados["cpfcnpj"]));
        $sheet->setCellValue('G'.$celula.'', utf8_encode($dados["nome"]));
        $sheet->setCellValue('H'.$celula.'', utf8_encode($dados["dataempenho"]));
        $sheet->setCellValue('I'.$celula.'', utf8_encode($dados["historico"]));
        $sheet->setCellValue('J'.$celula.'', utf8_encode($dados["empenhado"]));
        $sheet->setCellValue('K'.$celula.'', utf8_encode($dados["liquidado"]));
        $sheet->setCellValue('L'.$celula.'', utf8_encode($dados["pago"]));
        $sheet->setCellValue('M'.$celula.'', utf8_encode($dados["essencial"]));
        $sheet->setCellValue('N'.$celula.'', utf8_encode($dados["justificativa"]));
        $celula++;
      }
      $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
      $objWriter->save('IncisoVI.xlsx');
    }
  }//FIM INCISO 6

  if($inciso == "inciso7"){
    $objPHPExcel = new PHPExcel();
    $sheet = $objPHPExcel->getActiveSheet();

    $sheet->setCellValue('A1', utf8_encode('Fonte Recurso'));
    $sheet->setCellValue('B1', utf8_encode('CPF/CNPJ'));
    $sheet->setCellValue('C1', utf8_encode('Nome/Razão Social'));
    $sheet->setCellValue('D1', utf8_encode('Data da Obrigação'));
    $sheet->setCellValue('E1', utf8_encode('Valor'));
    $sheet->setCellValue('F1', utf8_encode('Essencial, Contínua e Preexistente'));
    $sheet->setCellValue('G1', utf8_encode('Descrição'));
    $sheet->setCellValue('H1', utf8_encode('Justificativa'));

    $dados = inciso7();
    $dadosinciso7 = array();
    $indice = 0;

    //testa($dados); die("Confere");
    $guarda = array();
    $guardavalor = array();
    foreach ($dados as $linha){
      $chave = $linha["o15_codigo"].$linha["z01_cgccpf"]. str_replace("-", "", $linha["e60_emiss"]);
      

      if(array_key_exists($chave, $guardavalor)){
          $xvalor = $linha["e60_vlranu"];
          //$xvalor = (float)$xvalor;
          $guardavalor[$chave] += $xvalor;
      }else{
          $xvalor = $linha["e60_vlranu"];
          //$xvalor = (float)$xvalor;
          $guardavalor[$chave] = $xvalor;
      }

      if(in_array($chave, $guarda)){
        continue;
      }
      array_push($guarda, $chave);
      


      //if($linha["z01_cgccpf"] != "34315278000170"){continue;}
      $valorempenhado = (float)$xvalor; //$linha["e60_vlranu"];
      $motivo = buscaMotivoAnulacao($linha["e60_numemp"]);
      $fonterecurso = buscaDePara($linha["o15_codigo"]);
      //if($valorempenhado <= 0){continue;}

      $dadosinciso7[$indice]["fonterecurso"] = $fonterecurso; //$linha["o15_codigo"];
      $dadosinciso7[$indice]["cpfcnpj"] = $linha["z01_cgccpf"];
      $dadosinciso7[$indice]["nome"] = $linha["z01_nome"];
      $dadosinciso7[$indice]["data"] = implode("/", array_reverse(explode("-", $linha["e60_emiss"])));
      $dadosinciso7[$indice]["valor"] = $chave;//number_format($valorempenhado, 2, ",", "");
      $dadosinciso7[$indice]["essencial"] = ($linha["e60_emiss"] <= "2024-04-30") ? "" : 1;
      $dadosinciso7[$indice]["descricao"] = substr(trim($linha["e60_resumo"]), 0, 250);
      $dadosinciso7[$indice]["justificativa"] = ($dadosinciso7[$indice]["essencial"] == 1) ? $motivo : "";
      $indice++;
    }
    
    
    $indice = 0;
    foreach ($dadosinciso7 as $linha) {      
      $linha["valor"] = $guardavalor[$linha["valor"]];
      $dadosinciso7[$indice]["valor"] = number_format($linha["valor"], 2, ",", "");
      $indice++;
    }
    
    $celula = 2;
    foreach ($dadosinciso7 as $dados) {
      //$dados["valor"] = number_format($dados["valor"], 2, ",", "");
      $sheet->setCellValue('A'.$celula.'', utf8_encode($dados["fonterecurso"]));
      $sheet->setCellValue('B'.$celula.'', utf8_encode($dados["cpfcnpj"]));
      $sheet->setCellValue('C'.$celula.'', utf8_encode($dados["nome"]));
      $sheet->setCellValue('D'.$celula.'', utf8_encode($dados["data"]));
      $sheet->setCellValue('E'.$celula.'', utf8_encode($dados["valor"]));
      $sheet->setCellValue('F'.$celula.'', utf8_encode($dados["essencial"]));
      $sheet->setCellValue('G'.$celula.'', utf8_encode($dados["descricao"]));
      $sheet->setCellValue('H'.$celula.'', utf8_encode($dados["justificativa"]));
      $celula++;
    }

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('IncisoVII.xlsx');
  }//FIM INCISO 7
  
  
}//foreach dos incisos
}//fim do post


?>

<html>
  <head>
    <title>Microsist</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta http-equiv="Expires" CONTENT="0">
    <script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
    <script language="JavaScript" type="text/javascript" src="scripts/strings.js"></script>
    <script language="JavaScript" type="text/javascript" src="scripts/prototype.js"></script>
    <script language="JavaScript" type="text/javascript" src="scripts/widgets/dbmessageBoard.widget.js"></script>
    <script language="JavaScript" type="text/javascript" src="scripts/widgets/DBToogle.widget.js"></script>
    <link href="estilos.css" rel="stylesheet" type="text/css">
  </head>
  <style>
    legend {
      font-weight: bold;
    }
    #sigfis {
      width: 800px;
      min-height: 500px;
    }
    #arquivo-gerar {
      width: 548px;
      display: block;
      float: left;
      overflow: auto;
    }
    #lista-gerados {
      margin-top: 48px;
      width: 250px;
      float: left;
      overflow: auto;
    }
    #field-gerados {
      height: 430px;
    }

    .alinha-td-label {
      width: 400px;
      text-align: left;
    }

    .alinha-td-check {
      width: 30px;
      text-align: left;
    }

    

  </style>
  <body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" bgcolor="#cccccc">
  <div style="margin-top: 40px;"></div>
  <center>
    <form action="" method="post" name="fimdemandato">
    <fieldset id='sigfis'>
      <legend>SIGFIS - Término de Mandato</legend>
      <div id='arquivo-gerar' align="left" >
        
        <fieldset>
          <legend>Registros</legend>
          <table id="contabilidade">            

            <tr>
              <td class="alinha-td-check">
                <input type="checkbox" id='inciso1' value='inciso1' name="inciso[]" />
              </td>
              <td class="alinha-td-label">
                <label for="inciso1"><b>Inciso I</b></label>
              </td>
            </tr>

            <tr>
              <td class="alinha-td-check">
                <input type="checkbox" id='inciso2' value='inciso2' name="inciso[]" disabled />
              </td>
              <td class="alinha-td-label">
                <label for="inciso2"><b>Inciso II</b></label>
              </td>
            </tr>

            <tr>
              <td class="alinha-td-check">
                <input type="checkbox" id='inciso3' value='inciso3' name="inciso[]" disabled />
              </td>
              <td class="alinha-td-label">
                <label for="inciso3"><b>Inciso III</b></label>
              </td>
            </tr>

            <tr>
              <td class="alinha-td-check">
                <input type="checkbox" id='inciso4' value='inciso4' name="inciso[]" disabled />
              </td>
              <td class="alinha-td-label">
                <label for="inciso4"><b>Inciso IV</b></label>
              </td>
            </tr>

            <tr>
              <td class="alinha-td-check">
                <input type="checkbox" id='inciso5' value='inciso5' name="inciso[]" />
              </td>
              <td class="alinha-td-label">
                <label for="inciso5"><b>Inciso V</b></label>
              </td>
            </tr>

            <tr>
              <td class="alinha-td-check">
                <input type="checkbox" id='inciso6' value='inciso6' name="inciso[]" />
              </td>
              <td class="alinha-td-label">
                <label for="inciso6"><b>Inciso VI</b></label>
              </td>
            </tr>

            <tr>
              <td class="alinha-td-check">
                <input type="checkbox" id='inciso7' value='inciso7' name="inciso[]" />
              </td>
              <td class="alinha-td-label">
                <label for="inciso7"><b>Inciso VII</b></label>
              </td>
            </tr>

            <tr>
              <td class="alinha-td-check">
                <input type="checkbox" id='inciso8' value='inciso8' name="inciso[]" disabled />
              </td>
              <td class="alinha-td-label">
                <label for="inciso8"><b>Inciso VIII</b></label>
              </td>
            </tr>

            <tr>
              <td class="alinha-td-check">
                <input type="checkbox" id='inciso9' value='inciso9' name="inciso[]" disabled />
              </td>
              <td class="alinha-td-label">
                <label for="inciso9"><b>Inciso IX</b></label>
              </td>
            </tr>

            <tr>
              <td class="alinha-td-check">
                <input type="checkbox" id='inciso10' value='inciso10' name="inciso[]" disabled />
              </td>
              <td class="alinha-td-label">
                <label for="inciso10"><b>Inciso X</b></label>
              </td>
            </tr>

            

          </table>
        </fieldset>






        
      </div>
      <div id='lista-gerados'>
        <fieldset id='field-gerados'>
          <legend>Arquivos Gerados</legend>
          <div style='overflow:auto; text-align: left;' id='retorno'></div>
        </fieldset>
      </div>
      <div style="clear: both; margin-top: 20px">

      </div>
    </fieldset>
    <div style="margin-top: 10px;">
        <input disabled type="button" id='selecionar-todos' value='Selecionar Todos' name='Selecionar Todos'
               onclick="js_marcaTodos();"/>
        <input type="button" id='limpar-selecao' value='Limpar Seleção' name='Limpar Seleção' onclick="js_desmarcar();" />
        <?php /* ?><input disabled type="button" id='processar' value='Processar' name='Processar' onclick="js_processar();" /> <?php */ ?>

        <input type="submit" id='processar' value='Processar' name='Processar' onclick="js_processar();" />
    </div>
  </form>
  </center>

  <?php if($_POST && isset($_POST["inciso"])) : ?>
    
    <script type="text/javascript">
      var arquivo1 = "IncisoI.xlsx";
      var arquivo2 = "IncisoII.xlsx";
      var arquivo3 = "IncisoIII.xlsx";
      var arquivo4 = "IncisoIV.xlsx";
      var arquivo5 = "IncisoV.xlsx";
      var arquivo6 = "IncisoVI.xlsx";
      var arquivo7 = "IncisoVII.xlsx";
      var arquivo8 = "IncisoVIII.xlsx";
      var arquivo9 = "IncisoIX.xlsx";
      var arquivo10 = "IncisoX.xlsx";
        
      var divArquivos = document.getElementById("retorno");
      <?php foreach($_POST["inciso"] as $inciso) : ?>        
        
        <?php if($inciso == "inciso1") : ?>
          var link1 = document.createElement("a");
          link1.href = arquivo1;
          link1.download = arquivo1.split("/").pop();
          link1.textContent = "Inciso I";
        <?php endif; ?>

        <?php if($inciso == "inciso5") : ?>
          var link5 = document.createElement("a");
          link5.href = arquivo5;
          link5.download = arquivo5.split("/").pop();
          link5.textContent = "Inciso V";
        <?php endif; ?>

        <?php if($inciso == "inciso6") : ?>
          var link6 = document.createElement("a");
          link6.href = arquivo6;
          link6.download = arquivo6.split("/").pop();
          link6.textContent = "Inciso VI";
        <?php endif; ?>

        <?php if($inciso == "inciso7") : ?>
          var link7 = document.createElement("a");
          link7.href = arquivo7;
          link7.download = arquivo7.split("/").pop();
          link7.textContent = "Inciso VII";
        <?php endif; ?>        
      <?php endforeach; ?>
        
      
      <?php foreach($_POST["inciso"] as $inciso) : ?>
         <?php if($inciso == "inciso1") : ?>
          divArquivos.appendChild(link1);
          divArquivos.appendChild(document.createElement("br"));
        <?php endif; ?>

        <?php if($inciso == "inciso5") : ?>
          divArquivos.appendChild(link5);
          divArquivos.appendChild(document.createElement("br"));
        <?php endif; ?>

        <?php if($inciso == "inciso6") : ?>
          divArquivos.appendChild(link6);
          divArquivos.appendChild(document.createElement("br"));
        <?php endif; ?>

        <?php if($inciso == "inciso7") : ?>
          divArquivos.appendChild(link7);
          divArquivos.appendChild(document.createElement("br"));
        <?php endif; ?>

      <?php endforeach; ?>          
        
        


        
      </script>

  <?php endif; ?>
  

  <script type="text/javascript">

    var sURL = "processaretce.RPC.php";

    var oContabilidade = new DBToogle($('field-contabilidade'), true);
    var oFinanceiro    = new DBToogle($('field-financeiro'), false);
    var oRH            = new DBToogle($('field-rh'), false);

    function js_processar() {

      var oParam             = new Object();
      oParam.exec            = "processarSigfisFimMandato";
      oParam.iPeriodo        = $F('periodosigap');
      oParam.sCodigoTribunal = encodeURIComponent($F('codigoTribunal'));

      oParam.aArquivos  = new Array();
      var aArquivos     = $$("input[type='checkbox']");
      aArquivos.each(function (oCheckbox, id) {

        with (oCheckbox) {

          if (checked) {
            oParam.aArquivos.push(oCheckbox.name);
          }
        }

      });

      if (oParam.aArquivos.length == 0) {

        alert("Selecione ao menos uma Opção.");
        return false;
      }

      js_divCarregando('Aguarde, Processando Arquivos', 'msgBox');

      var oAjax = new Ajax.Request(sURL,
                                   {
                                     method:'post',
                                     parameters:'json='+Object.toJSON(oParam),
                                     onComplete:js_retornoProcessaSigap
                                   }
                                 );
   }

   function js_retornoProcessaSigap(oAjax) {

     js_removeObj('msgBox');

     var oRetorno = eval("("+oAjax.responseText+")");
     if (oRetorno.status == 1) {

       var sRetorno = "";
       //sRetorno    += "Verifique o arquivo SIGFIS.log.<br>";
       for (var i = 0; i < oRetorno.lista.length; i++) {

         with (oRetorno.lista[i]) {

           sRetorno += "<a  download href='db_download.php?arquivo="+caminho+"'>"+nome+"</a><br>";
         }
       }

       $('retorno').innerHTML = sRetorno;
     } else {

       $('retorno').innerHTML = '';
       alert(oRetorno.message.urlDecode());
       return false;
     }
   }

   function js_marcaTodos() {

  	  var aCheckboxes = $$('input[type=checkbox]');
  	  aCheckboxes.each(function(oCheckbox) {
  	    oCheckbox.checked = true;
  	  });
  	}

   function js_desmarcar() {

  	  var aCheckboxes = $$('input[type=checkbox]');
  	  aCheckboxes.each(function (oCheckbox) {
  	    oCheckbox.checked = false;
  	  });
  	}

  </script>
  </body>
</html>
<? db_menu(db_getsession("DB_id_usuario"), db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit")); ?>


