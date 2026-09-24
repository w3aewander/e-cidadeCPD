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


class cl_diarioprogressao extends DAOBasica {

  public function __construct() {
    parent::__construct("diarioprogressao");
  }

  public function sql_query_avaliacoes($iDiarioProgressao) {

    $sSql  = " select ";
    $sSql .= "        ed999_sequencial    as codigo, ";
    $sSql .= "        ed999_procavaliacao as codigo_elemento, ";
    $sSql .= "        ed999_faltas        as numero_faltas, ";
    $sSql .= "        ed999_nota          as valor_nota, ";
    $sSql .= "        ed999_nota          as valor_nota_real, ";
    $sSql .= "        ed999_conceito      as valor_conceito, ";
    $sSql .= "        ed999_parecer       as parecer, ";
    $sSql .= "        trim(ed999_parecerpadronizado) as parecerpadronizado, ";
    $sSql .= "        ed999_atingiominimo as minimo, ";
    $sSql .= "        ed999_amparado      as amparo, ";
    $sSql .= "        ed999_observacao    as observacao, ";
    $sSql .= "        false               as em_recuperacao, ";
    $sSql .= "        (select ed39_i_sequencia ";
    $sSql .= "           from conceito ";
    $sSql .= "          where conceito.ed39_i_formaavaliacao = ed41_i_formaavaliacao ";
    $sSql .= "            and conceito.ed39_c_conceito = ed999_conceito) as ordem_conceito, ";
    $sSql .= "        ed41_i_sequencia    as sequencia, ";
    $sSql .= "        'A'                 as tipo_elemento ";
    $sSql .= "   from plugins.diarioprogressaoavaliacao";    
    $sSql .= "  inner join procavaliacao on ed41_i_codigo         = ed999_procavaliacao";
    $sSql .= "  where ed999_diarioprogressao = {$iDiarioProgressao}";

    $sSql .= "  union all";

    $sSql .= "  select ";
    $sSql .= "         ed998_sequencial    as codigo, ";
    $sSql .= "         ed998_procresultado as codigo_elemento, ";
    $sSql .= "         null                as numero_faltas, ";
    $sSql .= "         ed998_nota          as valor_nota, ";
    $sSql .= "         ed998_valorreal     as valor_nota_real, ";
    $sSql .= "         ed998_conceito      as valor_conceito, ";
    $sSql .= "         ed998_parecer       as parecer, ";
    $sSql .= "         ed998_parecerpadronizado as parecerpadronizado, ";
    $sSql .= "         ed998_atingiominimo as minimo, ";
    $sSql .= "         ed998_amparado      as amparo, ";
    $sSql .= "         null                as observacao, ";
    $sSql .= "         diarioprogressaoresultadorecuperacao.ed994_diarioprogressaoresultado is not null as em_recuperacao, ";
    $sSql .= "         (select ed39_i_sequencia ";
    $sSql .= "            from conceito ";
    $sSql .= "           where conceito.ed39_i_formaavaliacao = ed43_i_formaavaliacao ";
    $sSql .= "             and conceito.ed39_c_conceito = ed998_conceito) as ordem_conceito, ";
    $sSql .= "         ed43_i_sequencia   as sequencia, ";
    $sSql .= "         'R'                as tipo_elemento";
    $sSql .= "    from plugins.diarioprogressaoresultado";    
    $sSql .= "   inner join procresultado on ed43_i_codigo = ed998_procresultado";
    $sSql .= "    left join plugins.diarioprogressaoresultadorecuperacao on ed994_diarioprogressaoresultado  = ed998_sequencial";    
    $sSql .= "   where ed998_diarioprogressao = {$iDiarioProgressao}";

    return "select * from ($sSql) as x order by sequencia ";
  }

}

