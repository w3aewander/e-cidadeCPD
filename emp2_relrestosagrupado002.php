<?
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

include("fpdf151/pdf.php");
include("libs/db_sql.php");
include("libs/db_utils.php");
include("classes/db_empempenho_classe.php");
include("classes/db_empempenholiberado_classe.php");

$oGet  = db_utils::postMemory($_GET,0);

$iInstit       = db_getsession('DB_instit');
$iAnousu       = db_getsession('DB_anousu');
$sWhere        = " 1=1 and ";
$sAnd          = "";

$sSql = "

select $oGet->ano,
    tbfonte.o15_codigo as fonte_recurso,
    o15_descr,
    tbfuncao.o52_funcao as funcao,
    o52_descr,
    tbsfuncao.o53_subfuncao as subfuncao,
    o53_descr,
    '01 - PESSOAL E ENCARGOS' as natureza,
    sum( ( e91_vlrliq-e91_vlrpag ) + ( e91_vlremp-e91_vlranu-e91_vlrliq ) ) total_inscrito,
    sum(e91_vlrliq-e91_vlrpag) insc_processado,
    sum(e91_vlremp-e91_vlranu) -
    sum(e91_vlrliq) inscrito_nao_processado,
    
    (select coalesce(sum(conlancam.c70_valor),0)
    from    contabilidade.conlancam,
        contabilidade.conlancamemp,
        contabilidade.conlancamdoc,
        empenho.empempenho,
        orcamento.orcdotacao,
        orcamento.orcelemento

    where (conlancam.c70_codlan = conlancamemp.c75_codlan)
    and    (conlancam.c70_anousu = $iAnousu)
    and    (extract (month from conlancam.c70_data) = extract ( month from conlancamemp.c75_data))
    and    (extract(month from c75_data) = $oGet->mes)
    and    (conlancam.c70_codlan = conlancamdoc.c71_codlan)
    and    (conlancamdoc.c71_coddoc = 31)
    and    (extract (month from conlancamdoc.c71_data) = extract (month from conlancam.c70_data))

    and    (empempenho.e60_anousu = $oGet->ano)
    and    (empempenho.e60_instit = $iInstit)
    and    (empempenho.e60_numemp = conlancamemp.c75_numemp)
    and    (empempenho.e60_anousu = o58_anousu)
    and    (empempenho.e60_coddot = o58_coddot)
    and    (orcdotacao.o58_codigo = tbfonte.o15_codigo)
    and    (orcdotacao.o58_funcao = tbfuncao.o52_funcao)
    and    (orcdotacao.o58_subfuncao = tbsfuncao.o53_subfuncao)
    and    (orcdotacao.o58_codele = orcelemento.o56_codele)
    and    (orcdotacao.o58_anousu = orcelemento.o56_anousu)
    and    (substr(orcelemento.o56_elemento,2,2) = '31')) as anul_nomes_proc,

    (select coalesce(sum(conlancam.c70_valor),0)
    from    contabilidade.conlancam,
        contabilidade.conlancamemp,
        contabilidade.conlancamdoc,
        empenho.empempenho,
        orcamento.orcdotacao,
        orcamento.orcelemento

    where (conlancam.c70_codlan = conlancamemp.c75_codlan)
    and    (conlancam.c70_anousu = $iAnousu)
    and    (extract (month from conlancam.c70_data) = extract ( month from conlancamemp.c75_data))
    and    (extract(month from c75_data) <= $oGet->mes)
    and    (conlancam.c70_codlan = conlancamdoc.c71_codlan)
    and    (conlancamdoc.c71_coddoc = 31)
    and    (extract (month from conlancamdoc.c71_data) = extract (month from conlancam.c70_data))

    and    (empempenho.e60_anousu = $oGet->ano)
    and    (empempenho.e60_instit = $iInstit)
    and    (empempenho.e60_numemp = conlancamemp.c75_numemp)
    and    (empempenho.e60_anousu = o58_anousu)
    and    (empempenho.e60_coddot = o58_coddot)
    and    (orcdotacao.o58_codigo = tbfonte.o15_codigo)
    and    (orcdotacao.o58_funcao = tbfuncao.o52_funcao)
    and    (orcdotacao.o58_subfuncao = tbsfuncao.o53_subfuncao)
    and    (orcdotacao.o58_codele = orcelemento.o56_codele)
    and    (orcdotacao.o58_anousu = orcelemento.o56_anousu)
    and    (substr(orcelemento.o56_elemento,2,2) = '31')) as anul_atemes_proc,
    
    (select coalesce(sum(conlancam.c70_valor),0)
    from    contabilidade.conlancam,
        contabilidade.conlancamemp,
        contabilidade.conlancamdoc,
        empenho.empempenho,
        orcamento.orcdotacao,
        orcamento.orcelemento

    where (conlancam.c70_codlan = conlancamemp.c75_codlan)
    and    (conlancam.c70_anousu = $iAnousu)
    and    (extract (month from conlancam.c70_data) = extract ( month from conlancamemp.c75_data))
    and    (extract(month from c75_data) = $oGet->mes)
    and    (conlancam.c70_codlan = conlancamdoc.c71_codlan)
    and    (conlancamdoc.c71_coddoc = 32)
    and    (extract (month from conlancamdoc.c71_data) = extract (month from conlancam.c70_data))

    and    (empempenho.e60_anousu = $oGet->ano)
    and    (empempenho.e60_instit = $iInstit)
    and    (empempenho.e60_numemp = conlancamemp.c75_numemp)
    and    (empempenho.e60_anousu = o58_anousu)
    and    (empempenho.e60_coddot = o58_coddot)
    and    (orcdotacao.o58_codigo = tbfonte.o15_codigo)
    and    (orcdotacao.o58_funcao = tbfuncao.o52_funcao)
    and    (orcdotacao.o58_subfuncao = tbsfuncao.o53_subfuncao)
    and    (orcdotacao.o58_codele = orcelemento.o56_codele)
    and    (orcdotacao.o58_anousu = orcelemento.o56_anousu)
    and    (substr(orcelemento.o56_elemento,2,2) = '31')) as anul_nomes_naoproc,
    
    (select coalesce(sum(conlancam.c70_valor),0)
    from    contabilidade.conlancam,
        contabilidade.conlancamemp,
        contabilidade.conlancamdoc,
        empenho.empempenho,
        orcamento.orcdotacao,
        orcamento.orcelemento

    where (conlancam.c70_codlan = conlancamemp.c75_codlan)
    and    (conlancam.c70_anousu = $iAnousu)
    and    (extract (month from conlancam.c70_data) = extract ( month from conlancamemp.c75_data))
    and    (extract(month from c75_data) <= $oGet->mes)
    and    (conlancam.c70_codlan = conlancamdoc.c71_codlan)
    and    (conlancamdoc.c71_coddoc = 32)
    and    (extract (month from conlancamdoc.c71_data) = extract (month from conlancam.c70_data))

    and    (empempenho.e60_anousu = $oGet->ano)
    and    (empempenho.e60_instit = $iInstit)
    and    (empempenho.e60_numemp = conlancamemp.c75_numemp)
    and    (empempenho.e60_anousu = o58_anousu)
    and    (empempenho.e60_coddot = o58_coddot)
    and    (orcdotacao.o58_codigo = tbfonte.o15_codigo)
    and    (orcdotacao.o58_funcao = tbfuncao.o52_funcao)
    and    (orcdotacao.o58_subfuncao = tbsfuncao.o53_subfuncao)
    and    (orcdotacao.o58_codele = orcelemento.o56_codele)
    and    (orcdotacao.o58_anousu = orcelemento.o56_anousu)
    and    (substr(orcelemento.o56_elemento,2,2) = '31')) as anul_atemes_naoproc,

   (SELECT coalesce(sum(case conlancamdoc.c71_coddoc when 35 then conlancam.c70_valor when 36 then (conlancam.c70_valor * -1) end),0)
    from contabilidade.conlancam,
        contabilidade.conlancamemp,
        contabilidade.conlancamdoc,
        conlancaminstit,
        empenho.empempenho,
            orcamento.orcdotacao,
            orcamento.orcelemento

    where    (conlancam.c70_codlan = conlancamemp.c75_codlan)
    and    (conlancam.c70_anousu = $iAnousu)
    and    (extract(month from conlancamemp.c75_data) = $oGet->mes)
    and    (extract (month from conlancam.c70_data) = extract ( month from conlancamemp.c75_data))
    and    (conlancam.c70_codlan = conlancamdoc.c71_codlan)
    and    (conlancamdoc.c71_coddoc in (35,36))
    and    (extract (month from conlancamdoc.c71_data) = extract (month from conlancam.c70_data))

    and    (conlancam.c70_codlan = conlancaminstit.c02_codlan)
    and    (conlancaminstit.c02_instit = $iInstit)

    and    (empempenho.e60_anousu = $oGet->ano)
    and    (empempenho.e60_instit = $iInstit)
    and    (empempenho.e60_numemp = conlancamemp.c75_numemp)
    and    (empempenho.e60_anousu = o58_anousu)
    and    (empempenho.e60_coddot = o58_coddot)
    and    (orcdotacao.o58_codigo = tbfonte.o15_codigo)
    and    (orcdotacao.o58_funcao = tbfuncao.o52_funcao)
    and    (orcdotacao.o58_subfuncao = tbsfuncao.o53_subfuncao)
    and    (orcdotacao.o58_codele = orcelemento.o56_codele)
    and    (orcdotacao.o58_anousu = orcelemento.o56_anousu)
    and    (substr(orcelemento.o56_elemento,2,2) = '31')) as pag_nomes_proc,

   (SELECT coalesce( sum(case conlancamdoc.c71_coddoc when 35 then conlancam.c70_valor when 36 then (conlancam.c70_valor * -1) end),0)
    from contabilidade.conlancam,
        contabilidade.conlancamemp,
        contabilidade.conlancamdoc,
        conlancaminstit,
        empenho.empempenho,
        orcamento.orcdotacao,
        orcamento.orcelemento
    where  (conlancam.c70_codlan = conlancamemp.c75_codlan)
    and    (conlancam.c70_anousu = $iAnousu)
    and    (extract(month from conlancamemp.c75_data) <= $oGet->mes)
    and    (extract (month from conlancam.c70_data) <= extract ( month from conlancamemp.c75_data))
    and    (conlancam.c70_codlan = conlancamdoc.c71_codlan)
    and    (conlancamdoc.c71_coddoc in (35,36))
    and    (extract (month from conlancamdoc.c71_data) <= extract (month from conlancam.c70_data))
    and    (conlancam.c70_codlan = conlancaminstit.c02_codlan)
    and    (conlancaminstit.c02_instit = $iInstit)
    and    (empempenho.e60_anousu = $oGet->ano)
    and    (empempenho.e60_instit = $iInstit)
    and    (empempenho.e60_numemp = conlancamemp.c75_numemp)
    and    (empempenho.e60_anousu = o58_anousu)
    and    (empempenho.e60_coddot = o58_coddot)
    and    (orcdotacao.o58_codigo = tbfonte.o15_codigo)
    and    (orcdotacao.o58_funcao = tbfuncao.o52_funcao)
    and    (orcdotacao.o58_subfuncao = tbsfuncao.o53_subfuncao)
    and    (orcdotacao.o58_codele = orcelemento.o56_codele)
    and    (orcdotacao.o58_anousu = orcelemento.o56_anousu)
    and    (substr(orcelemento.o56_elemento,2,2) = '31')) as pag_atemes_proc,

   (SELECT coalesce( sum(case conlancamdoc.c71_coddoc when 37 then conlancam.c70_valor when 38 then (conlancam.c70_valor * -1) end),0) 
   from contabilidade.conlancam,
        contabilidade.conlancamemp,
        contabilidade.conlancamdoc,
        conlancaminstit,
        empenho.empempenho,
        orcamento.orcdotacao,
        orcamento.orcelemento

    where    (conlancam.c70_codlan = conlancamemp.c75_codlan)
    and    (conlancam.c70_anousu = $iAnousu)
    and    (extract(month from conlancamemp.c75_data) = $oGet->mes)
    and    (extract (month from conlancam.c70_data) = extract ( month from conlancamemp.c75_data))
    and    (conlancam.c70_codlan = conlancamdoc.c71_codlan)
    and    (conlancamdoc.c71_coddoc in (37,38))
    and    (extract (month from conlancamdoc.c71_data) = extract (month from conlancam.c70_data))
    and    (conlancam.c70_codlan = conlancaminstit.c02_codlan)
    and    (conlancaminstit.c02_instit = $iInstit)
    and    (empempenho.e60_anousu = $oGet->ano)
    and    (empempenho.e60_instit = $iInstit)
    and    (empempenho.e60_numemp = conlancamemp.c75_numemp)
    and    (empempenho.e60_anousu = o58_anousu)
    and    (empempenho.e60_coddot = o58_coddot)
    and    (orcdotacao.o58_codigo = tbfonte.o15_codigo)
    and    (orcdotacao.o58_funcao = tbfuncao.o52_funcao)
    and    (orcdotacao.o58_subfuncao = tbsfuncao.o53_subfuncao)
    and    (orcdotacao.o58_codele = orcelemento.o56_codele)
    and    (orcdotacao.o58_anousu = orcelemento.o56_anousu)
    and    (substr(orcelemento.o56_elemento,2,2) = '31')) as pag_nomes_nproc,
   
   (SELECT coalesce( sum(case conlancamdoc.c71_coddoc when 37 then conlancam.c70_valor when 38 then (conlancam.c70_valor * -1) end),0) 
   from contabilidade.conlancam,
        contabilidade.conlancamemp,
        contabilidade.conlancamdoc,
        conlancaminstit,
        empenho.empempenho,
        orcamento.orcdotacao,
        orcamento.orcelemento

    where   (conlancam.c70_codlan = conlancamemp.c75_codlan)
    and    (conlancam.c70_anousu = $iAnousu)
    and    (extract(month from conlancamemp.c75_data) <= $oGet->mes)
    and    (extract (month from conlancam.c70_data) <= extract ( month from conlancamemp.c75_data))
    and    (conlancam.c70_codlan = conlancamdoc.c71_codlan)
    and    (conlancamdoc.c71_coddoc in (37,38))
    and    (extract (month from conlancamdoc.c71_data) <= extract (month from conlancam.c70_data))
    and    (conlancam.c70_codlan = conlancaminstit.c02_codlan)
    and    (conlancaminstit.c02_instit = $iInstit)
    and    (empempenho.e60_anousu = $oGet->ano)
    and    (empempenho.e60_instit = $iInstit)
    and    (empempenho.e60_numemp = conlancamemp.c75_numemp)
    and    (empempenho.e60_anousu = o58_anousu)
    and    (empempenho.e60_coddot = o58_coddot)
    and    (orcdotacao.o58_codigo = tbfonte.o15_codigo)
    and    (orcdotacao.o58_funcao = tbfuncao.o52_funcao)
    and    (orcdotacao.o58_subfuncao = tbsfuncao.o53_subfuncao)
    and    (orcdotacao.o58_codele = orcelemento.o56_codele)
    and    (orcdotacao.o58_anousu = orcelemento.o56_anousu)
    and    (substr(orcelemento.o56_elemento,2,2) = '31')) as pag_atemes_nproc
    
from empenho.empempenho,
     empenho.empresto,
     orcamento.orcdotacao,
     orcamento.orcelemento,
     orcamento.orctiporec tbfonte,
     orcamento.orcfuncao tbfuncao,
     orcamento.orcsubfuncao tbsfuncao

where (empempenho.e60_anousu = $oGet->ano)
and   (empempenho.e60_instit = $iInstit)
and   (empresto.e91_anousu = $iAnousu)

and   (empresto.e91_numemp = empempenho.e60_numemp)
and   (empempenho.e60_anousu = orcdotacao.o58_anousu)
and   (empempenho.e60_coddot = orcdotacao.o58_coddot)
and   (orcdotacao.o58_codele = orcelemento.o56_codele)
and   (orcdotacao.o58_anousu = orcelemento.o56_anousu)
and   (orcdotacao.o58_codigo = tbfonte.o15_codigo)
and   (orcdotacao.o58_funcao = tbfuncao.o52_funcao)
and   (orcdotacao.o58_subfuncao = tbsfuncao.o53_subfuncao)
and   (substr(orcelemento.o56_elemento,2,2) = '31')
group by 1,
       tbfonte.o15_codigo,
       o15_descr,
       tbfuncao.o52_funcao,
       o52_descr,
       tbsfuncao.o53_subfuncao,
       o53_descr

union all

select $oGet->ano,
    tbfonte.o15_codigo as fonte_recurso,
    o15_descr,
    tbfuncao.o52_funcao as funcao,
    o52_descr,
    tbsfuncao.o53_subfuncao as subfuncao,
    o53_descr,
    '02- OUTRAS DESPESAS' as natureza,
    sum( ( e91_vlrliq-e91_vlrpag ) + ( e91_vlremp-e91_vlranu-e91_vlrliq ) ) total_inscrito,
    sum(e91_vlrliq-e91_vlrpag) insc_processado,
    sum(e91_vlremp-e91_vlranu) -
    sum(e91_vlrliq) inscrito_nao_processado,

    (select coalesce(sum(conlancam.c70_valor),0)
    from    contabilidade.conlancam,
        contabilidade.conlancamemp,
        contabilidade.conlancamdoc,
        empenho.empempenho,
        orcamento.orcdotacao,
        orcamento.orcelemento

    where (conlancam.c70_codlan = conlancamemp.c75_codlan)
    and    (conlancam.c70_anousu = $iAnousu)
    and    (extract (month from conlancam.c70_data) = extract ( month from conlancamemp.c75_data))
    and    (extract(month from c75_data) = $oGet->mes)
    and    (conlancam.c70_codlan = conlancamdoc.c71_codlan)
    and    (conlancamdoc.c71_coddoc = 31)
    and    (extract (month from conlancamdoc.c71_data) = extract (month from conlancam.c70_data))
    and    (empempenho.e60_anousu = $oGet->ano)
    and    (empempenho.e60_instit = $iInstit)
    and    (empempenho.e60_numemp = conlancamemp.c75_numemp)
    and    (empempenho.e60_anousu = o58_anousu)
    and    (empempenho.e60_coddot = o58_coddot)
    and    (orcdotacao.o58_codigo = tbfonte.o15_codigo)
    and    (orcdotacao.o58_funcao = tbfuncao.o52_funcao)
    and    (orcdotacao.o58_subfuncao = tbsfuncao.o53_subfuncao)
    and    (orcdotacao.o58_codele = orcelemento.o56_codele)
    and    (orcdotacao.o58_anousu = orcelemento.o56_anousu)
    and    (substr(orcelemento.o56_elemento,2,2) <> '31')) as anul_nomes_proc,

    (select coalesce(sum(conlancam.c70_valor),0)
    from    contabilidade.conlancam,
        contabilidade.conlancamemp,
        contabilidade.conlancamdoc,
        empenho.empempenho,
        orcamento.orcdotacao,
        orcamento.orcelemento

    where (conlancam.c70_codlan = conlancamemp.c75_codlan)
    and    (conlancam.c70_anousu = $iAnousu)
    and    (extract (month from conlancam.c70_data) = extract ( month from conlancamemp.c75_data))
    and    (extract(month from c75_data) <= $oGet->mes)
    and    (conlancam.c70_codlan = conlancamdoc.c71_codlan)
    and    (conlancamdoc.c71_coddoc = 31)
    and    (extract (month from conlancamdoc.c71_data) = extract (month from conlancam.c70_data))
    and    (empempenho.e60_anousu = $oGet->ano)
    and    (empempenho.e60_instit = $iInstit)
    and    (empempenho.e60_numemp = conlancamemp.c75_numemp)
    and    (empempenho.e60_anousu = o58_anousu)
    and    (empempenho.e60_coddot = o58_coddot)
    and    (orcdotacao.o58_codigo = tbfonte.o15_codigo)
    and    (orcdotacao.o58_funcao = tbfuncao.o52_funcao)
    and    (orcdotacao.o58_subfuncao = tbsfuncao.o53_subfuncao)
    and    (orcdotacao.o58_codele = orcelemento.o56_codele)
    and    (orcdotacao.o58_anousu = orcelemento.o56_anousu)
    and    (substr(orcelemento.o56_elemento,2,2) <> '31')) as anul_atemes_proc,
    
    (select coalesce(sum(conlancam.c70_valor),0)
    from    contabilidade.conlancam,
        contabilidade.conlancamemp,
        contabilidade.conlancamdoc,
        empenho.empempenho,
        orcamento.orcdotacao,
        orcamento.orcelemento

    where (conlancam.c70_codlan = conlancamemp.c75_codlan)
    and    (conlancam.c70_anousu = $iAnousu)
    and    (extract (month from conlancam.c70_data) = extract ( month from conlancamemp.c75_data))
    and    (extract(month from c75_data) = $oGet->mes)
    and    (conlancam.c70_codlan = conlancamdoc.c71_codlan)
    and    (conlancamdoc.c71_coddoc = 32)
    and    (extract (month from conlancamdoc.c71_data) = extract (month from conlancam.c70_data))
    and    (empempenho.e60_anousu = $oGet->ano)
    and    (empempenho.e60_instit = $iInstit)
    and    (empempenho.e60_numemp = conlancamemp.c75_numemp)
    and    (empempenho.e60_anousu = o58_anousu)
    and    (empempenho.e60_coddot = o58_coddot)
    and    (orcdotacao.o58_codigo = tbfonte.o15_codigo)
    and    (orcdotacao.o58_funcao = tbfuncao.o52_funcao)
    and    (orcdotacao.o58_subfuncao = tbsfuncao.o53_subfuncao)
    and    (orcdotacao.o58_codele = orcelemento.o56_codele)
    and    (orcdotacao.o58_anousu = orcelemento.o56_anousu)
    and    (substr(orcelemento.o56_elemento,2,2) <> '31')) as anul_nomes_naoproc,
    
    (select coalesce(sum(conlancam.c70_valor),0)
    from    contabilidade.conlancam,
        contabilidade.conlancamemp,
        contabilidade.conlancamdoc,
        empenho.empempenho,
        orcamento.orcdotacao,
        orcamento.orcelemento

    where (conlancam.c70_codlan = conlancamemp.c75_codlan)
    and    (conlancam.c70_anousu = $iAnousu)
    and    (extract (month from conlancam.c70_data) = extract ( month from conlancamemp.c75_data))
    and    (extract(month from c75_data) <= $oGet->mes)
    and    (conlancam.c70_codlan = conlancamdoc.c71_codlan)
    and    (conlancamdoc.c71_coddoc = 32)
    and    (extract (month from conlancamdoc.c71_data) = extract (month from conlancam.c70_data))
    and    (empempenho.e60_anousu = $oGet->ano)
    and    (empempenho.e60_instit = $iInstit)
    and    (empempenho.e60_numemp = conlancamemp.c75_numemp)
    and    (empempenho.e60_anousu = o58_anousu)
    and    (empempenho.e60_coddot = o58_coddot)
    and    (orcdotacao.o58_codigo = tbfonte.o15_codigo)
    and    (orcdotacao.o58_funcao = tbfuncao.o52_funcao)
    and    (orcdotacao.o58_subfuncao = tbsfuncao.o53_subfuncao)
    and    (orcdotacao.o58_codele = orcelemento.o56_codele)
    and    (orcdotacao.o58_anousu = orcelemento.o56_anousu)
    and    (substr(orcelemento.o56_elemento,2,2) <> '31')) as anul_atemes_naoproc,

   (SELECT coalesce( sum(case conlancamdoc.c71_coddoc when 35 then conlancam.c70_valor when 36 then (conlancam.c70_valor * -1) end),0)      from contabilidade.conlancam,
        contabilidade.conlancamemp,
        contabilidade.conlancamdoc,
        conlancaminstit,
        empenho.empempenho,
        orcamento.orcdotacao,
        orcamento.orcelemento

    where    (conlancam.c70_codlan = conlancamemp.c75_codlan)
    and    (conlancam.c70_anousu = $iAnousu)
    and    (extract(month from conlancamemp.c75_data) = $oGet->mes)
    and    (extract (month from conlancam.c70_data) = extract ( month from conlancamemp.c75_data))
    and    (conlancam.c70_codlan = conlancamdoc.c71_codlan)
    and    (conlancamdoc.c71_coddoc in (35,36))
    and    (extract (month from conlancamdoc.c71_data) = extract (month from conlancam.c70_data))
    and    (conlancam.c70_codlan = conlancaminstit.c02_codlan)
    and    (conlancaminstit.c02_instit = $iInstit)
    and    (empempenho.e60_anousu = $oGet->ano)
    and    (empempenho.e60_instit = $iInstit)
    and    (empempenho.e60_numemp = conlancamemp.c75_numemp)
    and    (empempenho.e60_anousu = o58_anousu)
    and    (empempenho.e60_coddot = o58_coddot)
    and    (orcdotacao.o58_codigo = tbfonte.o15_codigo)
    and    (orcdotacao.o58_funcao = tbfuncao.o52_funcao)
    and    (orcdotacao.o58_subfuncao = tbsfuncao.o53_subfuncao)
    and    (orcdotacao.o58_codele = orcelemento.o56_codele)
    and    (orcdotacao.o58_anousu = orcelemento.o56_anousu)
    and    (substr(orcelemento.o56_elemento,2,2) <> '31')) as pag_nomes_proc,

    (SELECT coalesce( sum(case conlancamdoc.c71_coddoc when 35 then conlancam.c70_valor when 36 then (conlancam.c70_valor * -1) end),0)
    from contabilidade.conlancam,
        contabilidade.conlancamemp,
        contabilidade.conlancamdoc,
        conlancaminstit,
        empenho.empempenho,
        orcamento.orcdotacao,
        orcamento.orcelemento

    where  (conlancam.c70_codlan = conlancamemp.c75_codlan)
    and    (conlancam.c70_anousu = $iAnousu)
    and    (extract(month from conlancamemp.c75_data) <= $oGet->mes)
    and    (extract (month from conlancam.c70_data) <= extract ( month from conlancamemp.c75_data))
    and    (conlancam.c70_codlan = conlancamdoc.c71_codlan)
    and    (conlancamdoc.c71_coddoc in (35,36))
    and    (extract (month from conlancamdoc.c71_data) <= extract (month from conlancam.c70_data))
    and    (conlancam.c70_codlan = conlancaminstit.c02_codlan)
    and    (conlancaminstit.c02_instit = $iInstit)
    and    (empempenho.e60_anousu = $oGet->ano)
    and    (empempenho.e60_instit = $iInstit)
    and    (empempenho.e60_numemp = conlancamemp.c75_numemp)
    and    (empempenho.e60_anousu = o58_anousu)
    and    (empempenho.e60_coddot = o58_coddot)
    and    (orcdotacao.o58_codigo = tbfonte.o15_codigo)
    and    (orcdotacao.o58_funcao = tbfuncao.o52_funcao)
    and    (orcdotacao.o58_subfuncao = tbsfuncao.o53_subfuncao)
    and    (orcdotacao.o58_codele = orcelemento.o56_codele)
    and    (orcdotacao.o58_anousu = orcelemento.o56_anousu)
    and    (substr(orcelemento.o56_elemento,2,2) <> '31')) as pag_atemes_proc,
   
    (SELECT coalesce( sum(case conlancamdoc.c71_coddoc when 37 then conlancam.c70_valor when 38 then (conlancam.c70_valor * -1) end),0)
    from contabilidade.conlancam,
        contabilidade.conlancamemp,
        contabilidade.conlancamdoc,
        conlancaminstit,
        empenho.empempenho,
        orcamento.orcdotacao,
        orcamento.orcelemento

    where    (conlancam.c70_codlan = conlancamemp.c75_codlan)
    and    (conlancam.c70_anousu = $iAnousu)
    and    (extract(month from conlancamemp.c75_data) = $oGet->mes)
    and    (extract (month from conlancam.c70_data) = extract ( month from conlancamemp.c75_data))
    and    (conlancam.c70_codlan = conlancamdoc.c71_codlan)
    and    (conlancamdoc.c71_coddoc in (37,38))
    and    (extract (month from conlancamdoc.c71_data) = extract (month from conlancam.c70_data))
    and    (conlancam.c70_codlan = conlancaminstit.c02_codlan)
    and    (conlancaminstit.c02_instit = $iInstit)
    and    (empempenho.e60_anousu = $oGet->ano)
    and    (empempenho.e60_instit = $iInstit)
    and    (empempenho.e60_numemp = conlancamemp.c75_numemp)
    and    (empempenho.e60_anousu = o58_anousu)
    and    (empempenho.e60_coddot = o58_coddot)
    and    (orcdotacao.o58_codigo = tbfonte.o15_codigo)
    and    (orcdotacao.o58_funcao = tbfuncao.o52_funcao)
    and    (orcdotacao.o58_subfuncao = tbsfuncao.o53_subfuncao)
    and    (orcdotacao.o58_codele = orcelemento.o56_codele)
    and    (orcdotacao.o58_anousu = orcelemento.o56_anousu)
    and    (substr(orcelemento.o56_elemento,2,2) <> '31')) as pag_nomes_nproc,
   
    (SELECT coalesce( sum(case conlancamdoc.c71_coddoc when 37 then conlancam.c70_valor when 38 then (conlancam.c70_valor * -1) end),0)
    from contabilidade.conlancam,
        contabilidade.conlancamemp,
        contabilidade.conlancamdoc,
        conlancaminstit,
        empenho.empempenho,
        orcamento.orcdotacao,
        orcamento.orcelemento
    where  (conlancam.c70_codlan = conlancamemp.c75_codlan)
    and    (conlancam.c70_anousu = $iAnousu)
    and    (extract(month from conlancamemp.c75_data) <= $oGet->mes)
    and    (extract (month from conlancam.c70_data) <= extract ( month from conlancamemp.c75_data))
    and    (conlancam.c70_codlan = conlancamdoc.c71_codlan)
    and    (conlancamdoc.c71_coddoc in (37,38))
    and    (extract (month from conlancamdoc.c71_data) <= extract (month from conlancam.c70_data))
    and    (conlancam.c70_codlan = conlancaminstit.c02_codlan)
    and    (conlancaminstit.c02_instit = $iInstit)
    and    (empempenho.e60_anousu = $oGet->ano)
    and    (empempenho.e60_instit = $iInstit)
    and    (empempenho.e60_numemp = conlancamemp.c75_numemp)
    and    (empempenho.e60_anousu = o58_anousu)
    and    (empempenho.e60_coddot = o58_coddot)
    and    (orcdotacao.o58_codigo = tbfonte.o15_codigo)
    and    (orcdotacao.o58_funcao = tbfuncao.o52_funcao)
    and    (orcdotacao.o58_subfuncao = tbsfuncao.o53_subfuncao)
    and    (orcdotacao.o58_codele = orcelemento.o56_codele)
    and    (orcdotacao.o58_anousu = orcelemento.o56_anousu)
    and    (substr(orcelemento.o56_elemento,2,2) <> '31')) as pag_atemes_nproc
    
from empenho.empempenho,
     empenho.empresto,
     orcamento.orcdotacao,
     orcamento.orcelemento,
     orcamento.orctiporec tbfonte,
     orcamento.orcfuncao tbfuncao,
     orcamento.orcsubfuncao tbsfuncao
where (empempenho.e60_anousu = $oGet->ano)
and   (empempenho.e60_instit = $iInstit)
and   (empresto.e91_anousu = $iAnousu)
and   (empresto.e91_numemp = empempenho.e60_numemp)
and   (empempenho.e60_anousu = o58_anousu)
and   (empempenho.e60_coddot = o58_coddot)
and   (orcdotacao.o58_codigo = tbfonte.o15_codigo)
and   (orcdotacao.o58_funcao = tbfuncao.o52_funcao)
and    (orcdotacao.o58_subfuncao = tbsfuncao.o53_subfuncao)
and   (orcdotacao.o58_codele = orcelemento.o56_codele)
and   (orcdotacao.o58_anousu = orcelemento.o56_anousu)
and   (substr(orcelemento.o56_elemento,2,2) <> '31')
group by 1,
       tbfonte.o15_codigo,
       o15_descr,
       tbfuncao.o52_funcao,
       o52_descr,
       tbsfuncao.o53_subfuncao,
       o53_descr

order by 1,2,4,6,8;
";
//die($sSql);

$rsSql   = db_query($sSql);
$iRsSql  = pg_num_rows($rsSql);

//db_criatabela( $rsSql );exit;

if ($iRsSql == 0){
  db_redireciona('db_erros.php?fechar=true&db_erro=Não existem registros cadastrados.');
}

/*
 * Monta os cabecalho do relatorio
 */

$head2 = "RELATORIO DE RESTOS A PAGAR AGRUPADOS";

$head5 = "Ano: $oGet->ano";	
$head6 = "Mes: $oGet->mes";

$pdf = new PDF(); 
$pdf->Open(); 
//$pdf->AddPage("L");
$pdf->AliasNbPages(); 

$pdf->setfillcolor(235);
$pdf->setfont('arial','b',8);

$troca = 1;
$prenc = 0;
$alt   = 4;

/*
 * Se retornar registros monta relatorio
 */

$nTotalIncrito              = 0;
$nTotalIncritoAnuladoNoMes  = 0;
$nTotalIncritoAnuladoAteMes = 0;
$nTotalIncritoPagosNoMes    = 0;
$nTotalIncritoPagosAteMes   = 0;
$nTotalIncritoSaldo         = 0;

$nTotalProc              = 0;
$nTotalProcAnuladoNoMes  = 0;
$nTotalProcAnuladoAteMes = 0;
$nTotalProcPagosNoMes    = 0;
$nTotalProcPagosAteMes   = 0;
$nTotalProcSaldo         = 0;

$nTotalNProc              = 0;
$nTotalNProcAnuladoNoMes  = 0;
$nTotalNProcAnuladoAteMes = 0;
$nTotalNProcPagosNoMes    = 0;
$nTotalNProcPagosAteMes   = 0;
$nTotalNProcSaldo         = 0;

$sUltimaFonte 		= "";
$sUltimaFuncao 		= "";
$sUltimaSubfuncao 	= "";

for ( $iInd = 0; $iInd  < $iRsSql; $iInd++ ) {
    $oEmpenhos = db_utils::fieldsMemory($rsSql,$iInd);

    if ( $pdf->Gety() > $pdf->h - 25 or $troca != 0 ) {
      ImprimeCabec($pdf, $troca, $nTotalGeralVlr, $nTotalGeralVlrSaldo);
      $troca=0;
    }
    
    if ( $prenc == 0 ) {
//      $prenc = 1;
    } else {
//      $prenc = 0;
    }
    
    $pdf->setfont('arial','',7);

    if ( $sUltimaFonte != $oEmpenhos->fonte_recurso ) {
      $pdf->cell(10,$alt,$oEmpenhos->fonte_recurso                               	,0,0,"L",$prenc);
      $pdf->cell(80,$alt,$oEmpenhos->o15_descr                                        	,0,0,"L",$prenc);
      $pdf->ln();
      $sUltimaFonte = $oEmpenhos->fonte_recurso;
    }

    if ( $sUltimaFuncao != str_pad($oEmpenhos->fonte_recurso,4,' ', STR_PAD_LEFT ) . str_pad($oEmpenhos->funcao,4,' ', STR_PAD_LEFT ) ) {
      $pdf->cell(10,$alt,""  			                                           	,0,0,"L",$prenc);
      $pdf->cell(10,$alt,$oEmpenhos->funcao                                             	,0,0,"L",$prenc);
      $pdf->cell(80,$alt,$oEmpenhos->o52_descr                                          	,0,0,"L",$prenc);
      $pdf->ln();
      $sUltimaFuncao = str_pad($oEmpenhos->fonte_recurso,4,' ', STR_PAD_LEFT ) . str_pad($oEmpenhos->funcao,4,' ', STR_PAD_LEFT );
    }

    if ( $pdf->Gety() > $pdf->h - 25 ) {
      ImprimeCabec($pdf, $troca, $nTotalGeralVlr, $nTotalGeralVlrSaldo);
    }

    if ( $sUltimaSubFuncao != str_pad($oEmpenhos->fonte_recurso,4,' ', STR_PAD_LEFT ) . str_pad($oEmpenhos->funcao,4,' ', STR_PAD_LEFT ) . str_pad($oEmpenhos->subfuncao,4,' ', STR_PAD_LEFT ) ) {
      $pdf->cell(10,$alt,""  			                                           	,0,0,"L",$prenc);
      $pdf->cell(10,$alt,""  			                                           	,0,0,"L",$prenc);
      $pdf->cell(10,$alt,$oEmpenhos->subfuncao                                          	,0,0,"L",$prenc);
      $pdf->cell(80,$alt,$oEmpenhos->o53_descr                                          	,0,0,"L",$prenc);
      $pdf->ln();
      $sUltimaSubFuncao = str_pad($oEmpenhos->fonte_recurso,4,' ', STR_PAD_LEFT ) . str_pad($oEmpenhos->funcao,4,' ', STR_PAD_LEFT ) . str_pad($oEmpenhos->subfuncao,4,' ', STR_PAD_LEFT );
    }

    if ( $pdf->Gety() > $pdf->h - 25 ) {
      ImprimeCabec($pdf, $troca, $nTotalGeralVlr, $nTotalGeralVlrSaldo);
    }

    $pdf->cell(30,$alt,""  			                                       			 ,0,0,"L",$prenc);
    $pdf->cell(35,$alt,$oEmpenhos->natureza                                           			 ,0,0,"L",$prenc);

    $pdf->cell(30,$alt,db_formatar($oEmpenhos->insc_processado + $oEmpenhos->inscrito_nao_processado,'f'),0,0,"R",$prenc);
    $pdf->cell(30,$alt,db_formatar($oEmpenhos->anul_nomes_proc + $oEmpenhos->anul_nomes_naoproc,'f')     ,0,0,"R",$prenc);
    $pdf->cell(30,$alt,db_formatar($oEmpenhos->anul_atemes_proc + $oEmpenhos->anul_atemes_naoproc,'f')   ,0,0,"R",$prenc);
    $pdf->cell(30,$alt,db_formatar($oEmpenhos->pag_nomes_proc  + $oEmpenhos->pag_nomes_nproc,'f')        ,0,0,"R",$prenc);
    $pdf->cell(30,$alt,db_formatar($oEmpenhos->pag_atemes_proc  + $oEmpenhos->pag_atemes_nproc,'f')      ,0,0,"R",$prenc);
    $pdf->cell(30,$alt,db_formatar($oEmpenhos->insc_processado + $oEmpenhos->inscrito_nao_processado - $oEmpenhos->anul_atemes_proc - $oEmpenhos->anul_atemes_naoproc - $oEmpenhos->pag_atemes_proc - $oEmpenhos->pag_atemes_nproc,'f')      ,0,0,"R",$prenc);

    $nTotalIncrito              += $oEmpenhos->insc_processado + $oEmpenhos->inscrito_nao_processado;
    $nTotalIncritoAnuladoNoMes  += $oEmpenhos->anul_nomes_proc + $oEmpenhos->anul_nomes_naoproc;
    $nTotalIncritoAnuladoAteMes += $oEmpenhos->anul_atemes_proc + $oEmpenhos->anul_atemes_naoproc;
    $nTotalIncritoPagosNoMes    += $oEmpenhos->pag_nomes_proc  + $oEmpenhos->pag_nomes_nproc;
    $nTotalIncritoPagosAteMes   += $oEmpenhos->pag_atemes_proc  + $oEmpenhos->pag_atemes_nproc;
    $nTotalIncritoSaldo         += $oEmpenhos->insc_processado + $oEmpenhos->inscrito_nao_processado - $oEmpenhos->anul_atemes_proc - $oEmpenhos->anul_atemes_naoproc - $oEmpenhos->pag_atemes_proc - $oEmpenhos->pag_atemes_nproc;

    $pdf->ln();
    if ( $pdf->Gety() > $pdf->h - 25 ) {
      ImprimeCabec($pdf, $troca, $nTotalGeralVlr, $nTotalGeralVlrSaldo);
    }

    $pdf->cell(30,$alt,""  			                                       	,0,0,"L",$prenc);
    $pdf->cell(35,$alt,""  			                                       	,0,0,"L",$prenc);
    $pdf->cell(30,$alt,db_formatar($oEmpenhos->insc_processado,'f')       	    	,0,0,"R",$prenc);
    $pdf->cell(30,$alt,db_formatar($oEmpenhos->anul_nomes_proc,'f')       	    	,0,0,"R",$prenc);
    $pdf->cell(30,$alt,db_formatar($oEmpenhos->anul_atemes_proc,'f')    	       	,0,0,"R",$prenc);
    $pdf->cell(30,$alt,db_formatar($oEmpenhos->pag_nomes_proc,'f')       	    	,0,0,"R",$prenc);
    $pdf->cell(30,$alt,db_formatar($oEmpenhos->pag_atemes_proc,'f') 	          	,0,0,"R",$prenc);
    $pdf->cell(30,$alt,db_formatar($oEmpenhos->insc_processado - $oEmpenhos->anul_atemes_proc - $oEmpenhos->pag_atemes_proc,'f') 	          	,0,0,"R",$prenc);

    $nTotalProc              += $oEmpenhos->insc_processado;
    $nTotalProcAnuladoNoMes  += $oEmpenhos->anul_nomes_proc;
    $nTotalProcAnuladoAteMes += $oEmpenhos->anul_atemes_proc;
    $nTotalProcPagosNoMes    += $oEmpenhos->pag_nomes_proc;
    $nTotalProcPagosAteMes   += $oEmpenhos->pag_atemes_proc;
    $nTotalProcSaldo         += $oEmpenhos->insc_processado - $oEmpenhos->anul_atemes_proc - $oEmpenhos->pag_atemes_proc;

    $pdf->ln();
    if ( $pdf->Gety() > $pdf->h - 25 ) {
      ImprimeCabec($pdf, $troca, $nTotalGeralVlr, $nTotalGeralVlrSaldo);
    }

    $pdf->cell(30,$alt,""  			                                       	,0,0,"L",$prenc);
    $pdf->cell(35,$alt,""  			                                       	,0,0,"L",$prenc);
    $pdf->cell(30,$alt,db_formatar($oEmpenhos->inscrito_nao_processado,'f')       	    	,0,0,"R",$prenc);
    $pdf->cell(30,$alt,db_formatar($oEmpenhos->anul_nomes_naoproc,'f')       	    	,0,0,"R",$prenc);
    $pdf->cell(30,$alt,db_formatar($oEmpenhos->anul_atemes_naoproc,'f')    	       	,0,0,"R",$prenc);
    $pdf->cell(30,$alt,db_formatar($oEmpenhos->pag_nomes_nproc,'f')       	    	,0,0,"R",$prenc);
    $pdf->cell(30,$alt,db_formatar($oEmpenhos->pag_atemes_nproc,'f') 	          	,0,0,"R",$prenc);
    $pdf->cell(30,$alt,db_formatar($oEmpenhos->inscrito_nao_processado - $oEmpenhos->anul_atemes_naoproc - $oEmpenhos->pag_atemes_nproc,'f') 	        ,0,0,"R",$prenc);

    $nTotalNProc              += $oEmpenhos->inscrito_nao_processado;
    $nTotalNProcAnuladoNoMes  += $oEmpenhos->anul_nomes_naoproc;
    $nTotalNProcAnuladoAteMes += $oEmpenhos->anul_atemes_naoproc;
    $nTotalNProcPagosNoMes    += $oEmpenhos->pag_nomes_nproc;
    $nTotalNProcPagosAteMes   += $oEmpenhos->pag_atemes_nproc;
    $nTotalNProcSaldo         += $oEmpenhos->inscrito_nao_processado - $oEmpenhos->anul_atemes_naoproc - $oEmpenhos->pag_atemes_nproc;

    $pdf->ln();
    if ( $pdf->Gety() > $pdf->h - 25 ) {
      ImprimeCabec($pdf, $troca, $nTotalGeralVlr, $nTotalGeralVlrSaldo);
    }
    
    $nTotalGeralVlr      += $oEmpenhos->e60_vlremp;
    $nTotalGeralVlrSaldo += $oEmpenhos->saldo;

}

/*
 * Total de registros retornados
 */

$pdf->ln();
$pdf->ln();
$pdf->setfont('arial','b',7);
$pdf->cell(65,6,'TOTAL DE REGISTROS:   ' . $iRsSql,0,0,"L",0);

//$pdf->cell(65,$alt,"",0,0,"L",$prenc);
$pdf->cell(30,$alt,db_formatar($nTotalIncrito             ,'f')        	,0,0,"R",$prenc);
$pdf->cell(30,$alt,db_formatar($nTotalIncritoAnuladoNoMes ,'f')        	,0,0,"R",$prenc);
$pdf->cell(30,$alt,db_formatar($nTotalIncritoAnuladoAteMes,'f')        	,0,0,"R",$prenc);
$pdf->cell(30,$alt,db_formatar($nTotalIncritoPagosNoMes   ,'f')        	,0,0,"R",$prenc);
$pdf->cell(30,$alt,db_formatar($nTotalIncritoPagosAteMes  ,'f')    	,0,0,"R",$prenc);
$pdf->cell(30,$alt,db_formatar($nTotalIncritoSaldo        ,'f')     	,0,0,"R",$prenc);
$pdf->ln();

$pdf->cell(65,$alt,"",0,0,"L",$prenc);
$pdf->cell(30,$alt,db_formatar($nTotalProc             ,'f')         	,0,0,"R",$prenc);
$pdf->cell(30,$alt,db_formatar($nTotalProcAnuladoNoMes ,'f')         	,0,0,"R",$prenc);
$pdf->cell(30,$alt,db_formatar($nTotalProcAnuladoAteMes,'f')    	,0,0,"R",$prenc);
$pdf->cell(30,$alt,db_formatar($nTotalProcPagosNoMes   ,'f')     	,0,0,"R",$prenc);
$pdf->cell(30,$alt,db_formatar($nTotalProcPagosAteMes  ,'f') 	      	,0,0,"R",$prenc);
$pdf->cell(30,$alt,db_formatar($nTotalProcSaldo        ,'f') 	        ,0,0,"R",$prenc);
$pdf->ln();

$pdf->cell(65,$alt,"",0,0,"L",$prenc);
$pdf->cell(30,$alt,db_formatar($nTotalNProc             ,'f')         	,0,0,"R",$prenc);
$pdf->cell(30,$alt,db_formatar($nTotalNProcAnuladoNoMes ,'f')         	,0,0,"R",$prenc);
$pdf->cell(30,$alt,db_formatar($nTotalNProcAnuladoAteMes,'f')    	,0,0,"R",$prenc);
$pdf->cell(30,$alt,db_formatar($nTotalNProcPagosNoMes   ,'f')     	,0,0,"R",$prenc);
$pdf->cell(30,$alt,db_formatar($nTotalNProcPagosAteMes  ,'f') 	      	,0,0,"R",$prenc);
$pdf->cell(30,$alt,db_formatar($nTotalNProcSaldo        ,'f') 	        ,0,0,"R",$prenc);
$pdf->ln();

$pdf->Output();

function ImprimeCabec($pdf, $troca, $nTotalGeralVlr, $nTotalGeralVlrSaldo) {
//  die("xxx [get: " . $pdf->Gety() . "] - h - 25: [" . ( $pdf->h - 25 ) . "] $troca");

  if ($pdf->Gety() > $pdf->h - 25 or $troca != 0) {

    $pdf->AddPage("L");

    $alt = 6;
    $pdf->setfont('arial','b',8);
    $pdf->cell(70,$alt,"Fonte de recurso"                                                                 ,0,0,"L",1);
    $pdf->cell(30,$alt,"RP inscrito"                                                                      ,0,0,"R",1);
    $pdf->cell(60,$alt,"Anulados"                                                                         ,0,0,"C",1);
    $pdf->cell(60,$alt,"Pagos"                                                                            ,0,0,"C",1);
    $pdf->cell(30,$alt,"Saldo a pagar"                                                                    ,0,0,"C",1);
    $pdf->ln();

    $pdf->cell(70,$alt,""                                                                                 ,0,0,"R",1);
    $pdf->cell(30,$alt,"RP Proc"                                                                          ,0,0,"R",1);
    $pdf->cell(30,$alt,"No Mes"                                                                           ,0,0,"C",1);
    $pdf->cell(30,$alt,"Ate o Mes"                                                                        ,0,0,"C",1);
    $pdf->cell(30,$alt,"No Mes"                                                                           ,0,0,"C",1);
    $pdf->cell(30,$alt,"Ate o Mes"                                                                        ,0,0,"C",1);
    $pdf->cell(30,$alt,""                                                                                 ,0,0,"C",1);
    $pdf->ln();

    $pdf->cell(70,$alt,""                                                                                 ,0,0,"R",1);
    $pdf->cell(30,$alt,"RP Nao Proc"                                                                      ,0,0,"R",1);
    $pdf->cell(30,$alt,""                                                                                 ,0,0,"C",1);
    $pdf->cell(30,$alt,""                                                                                 ,0,0,"C",1);
    $pdf->cell(30,$alt,""                                                                                 ,0,0,"C",1);
    $pdf->cell(30,$alt,""                                                                                 ,0,0,"C",1);
    $pdf->cell(30,$alt,""                                                                                 ,0,0,"C",1);
    $pdf->ln();
    $pdf->ln();
    $pdf->setfont('arial','',7);

    $nTotalGeralVlr      = 0;
    $nTotalGeralVlrSaldo = 0;

  }

}

?>
