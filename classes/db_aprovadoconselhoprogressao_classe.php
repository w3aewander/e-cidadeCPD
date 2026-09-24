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

class cl_aprovadoconselhoprogressao extends DAOBasica {

  public function __construct() {
    parent::__construct("aprovadoconselhoprogressao");
  }

  // funcao do sql
   public function sql_aprovadoconselhoprogressao ($ed996_sequencial = null,$campos = "*", $ordem = null, $dbwhere = "") {

     $sql  = "select {$campos}";
     $sql .= "  from plugins.aprovadoconselhoprogressao ";
     $sql .= " inner join plugins.diarioprogressao            on ed993_sequencial = ed996_diarioprogressao ";
     $sql .= " inner join progressaoparcialalunoturmaregencia on ed115_sequencial = ed993_progressaoparcialalunoturmaregencia ";
     $sql .= " inner join progressaoparcialalunomatricula     on ed150_sequencial = ed115_progressaoparcialalunomatricula ";
     $sql .= " inner join progressaoparcialaluno on ed114_sequencial = ed150_progressaoparcialaluno ";
     $sql .= " inner join aluno         on ed47_i_codigo    = ed114_aluno ";
     $sql .= " inner join regencia      on ed59_i_codigo    = ed115_regencia ";
     $sql .= " inner join disciplina    on disciplina.ed12_i_codigo     = regencia.ed59_i_disciplina ";
     $sql .= " inner join caddisciplina on caddisciplina.ed232_i_codigo = disciplina.ed12_i_caddisciplina ";
     $sql .= " inner join turma         on ed57_i_codigo    = ed59_i_turma ";
     $sql .= " inner join serie         on ed59_i_serie     = ed11_i_codigo ";
     $sql .= " inner join calendario    on ed52_i_codigo    = ed57_i_calendario ";

     $sql2 = "";
     if (empty($dbwhere)) {
       if (!empty($ed996_sequencial)) {
         $sql2 .= " where aprovadoconselhoprogressao.ed996_sequencial = {$ed996_sequencial} ";
       }
     } else if (!empty($dbwhere)) {
       $sql2 = " where $dbwhere";
     }
     $sql .= $sql2;
     if (!empty($ordem)) {
       $sql .= " order by {$ordem}";
     }
     return $sql;
  }
}