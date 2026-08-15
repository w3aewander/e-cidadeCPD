<?php
/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2009  DBSeller Servicos de Informatica
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

class cl_itinerariomeiotransporte
{
   // cria variaveis de erro 
    public $rotulo = null; 
    public $query_sql = null; 
    public $numrows = 0; 
    public $numrows_incluir = 0; 
    public $numrows_alterar = 0; 
    public $numrows_excluir = 0; 
    public $erro_status = null; 
    public $erro_sql = null; 
    public $erro_banco = null;  
    public $erro_msg = null;  
    public $erro_campo = null;  
    public $pagina_retorno = null; 
    /* Variáveis do Arquivo */
    public $tre15_sequencial = 0; 
    public $tre15_linhatransporteitinerario = 0; 
    public $tre15_meiotransporte = 0; 
   // cria propriedade com as variaveis do arquivo 
    public $campos = "
                 tre15_sequencial = int4 = Sequencial 
                 tre15_linhatransporteitinerario = int4 = Itinerário 
                 tre15_meiotransporte = int4 = Meio de transporte 
                 ";

    public function __construct()
    {
        $this->rotulo = new rotulo("itinerariomeiotransporte"); 
        $this->pagina_retorno = basename($_SERVER['PHP_SELF']);
    }

    public function erro($mostra, $retorna)
    {
     if(($this->erro_status == "0") || ($mostra == true && $this->erro_status != null )){
        echo "<script>alert(\"".$this->erro_msg."\")</script>";
        if($retorna==true){
           echo "<script>location.href='".$this->pagina_retorno."'</script>";
        }
     }
   }

    public function atualizacampos($exclusao = false)
    {
     if($exclusao==false){
       $this->tre15_sequencial = ($this->tre15_sequencial == ""?@$GLOBALS["HTTP_POST_VARS"]["tre15_sequencial"]:$this->tre15_sequencial);
       $this->tre15_linhatransporteitinerario = ($this->tre15_linhatransporteitinerario == ""?@$GLOBALS["HTTP_POST_VARS"]["tre15_linhatransporteitinerario"]:$this->tre15_linhatransporteitinerario);
       $this->tre15_meiotransporte = ($this->tre15_meiotransporte == ""?@$GLOBALS["HTTP_POST_VARS"]["tre15_meiotransporte"]:$this->tre15_meiotransporte);
     }else{
       $this->tre15_sequencial = ($this->tre15_sequencial == ""?@$GLOBALS["HTTP_POST_VARS"]["tre15_sequencial"]:$this->tre15_sequencial);
     }
   }

    public function incluir($tre15_sequencial)
    {
      $this->atualizacampos();
     if($this->tre15_linhatransporteitinerario == null ){ 
       $this->erro_sql = " Campo Itinerário não informado.";
       $this->erro_campo = "tre15_linhatransporteitinerario";
       $this->erro_banco = "";
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }
     if($this->tre15_meiotransporte == null ){ 
       $this->erro_sql = " Campo Meio de transporte não informado.";
       $this->erro_campo = "tre15_meiotransporte";
       $this->erro_banco = "";
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }
     if($tre15_sequencial == "" || $tre15_sequencial == null ){
       $result = db_query("select nextval('itinerariomeiotransporte_tre15_sequencial_seq')"); 
       if($result==false){
         $this->erro_banco = str_replace("\n","",@pg_last_error());
         $this->erro_sql   = "Verifique o cadastro da sequencia: itinerariomeiotransporte_tre15_sequencial_seq do campo: tre15_sequencial"; 
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false; 
       }
       $this->tre15_sequencial = pg_result($result,0,0); 
     }else{
       $result = db_query("select last_value from itinerariomeiotransporte_tre15_sequencial_seq");
       if(($result != false) && (pg_result($result,0,0) < $tre15_sequencial)){
         $this->erro_sql = " Campo tre15_sequencial maior que último número da sequencia.";
         $this->erro_banco = "Sequencia menor que este número.";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }else{
         $this->tre15_sequencial = $tre15_sequencial; 
       }
     }
     if(($this->tre15_sequencial == null) || ($this->tre15_sequencial == "") ){ 
       $this->erro_sql = " Campo tre15_sequencial não declarado.";
       $this->erro_banco = "Chave Primaria zerada.";
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }
     $sql = "insert into itinerariomeiotransporte(
                                       tre15_sequencial 
                                      ,tre15_linhatransporteitinerario 
                                      ,tre15_meiotransporte 
                       )
                values (
                                $this->tre15_sequencial 
                               ,$this->tre15_linhatransporteitinerario 
                               ,$this->tre15_meiotransporte 
                      )";
     $result = db_query($sql); 
     if($result==false){ 
       $this->erro_banco = str_replace("\n","",@pg_last_error());
       if( strpos(strtolower($this->erro_banco),"duplicate key") != 0 ){
         $this->erro_sql   = "Itinerario Meio Transporte ($this->tre15_sequencial) não Incluído. Inclusão Abortada.";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_banco = "Itinerario Meio Transporte já Cadastrado";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       }else{
         $this->erro_sql   = "Itinerario Meio Transporte ($this->tre15_sequencial) não Incluído. Inclusão Abortada.";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       }
       $this->erro_status = "0";
       $this->numrows_incluir= 0;
       return false;
     }
     $this->erro_banco = "";
     $this->erro_sql = "Inclusão efetuada com sucesso.\\n";
         $this->erro_sql .= "Valores : ".$this->tre15_sequencial;
     $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
     $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
     $this->erro_status = "1";
     $this->numrows_incluir= pg_affected_rows($result);
     return true;
   } 

    public function alterar($tre15_sequencial=null)
    {
      $this->atualizacampos();
     $sql = " update itinerariomeiotransporte set ";
     $virgula = "";
     if(trim($this->tre15_sequencial)!="" || isset($GLOBALS["HTTP_POST_VARS"]["tre15_sequencial"])){ 
       $sql  .= $virgula." tre15_sequencial = $this->tre15_sequencial ";
       $virgula = ",";
       if(trim($this->tre15_sequencial) == null ){ 
         $this->erro_sql = " Campo Sequencial não informado.";
         $this->erro_campo = "tre15_sequencial";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->tre15_linhatransporteitinerario)!="" || isset($GLOBALS["HTTP_POST_VARS"]["tre15_linhatransporteitinerario"])){ 
       $sql  .= $virgula." tre15_linhatransporteitinerario = $this->tre15_linhatransporteitinerario ";
       $virgula = ",";
       if(trim($this->tre15_linhatransporteitinerario) == null ){ 
         $this->erro_sql = " Campo Itinerário não informado.";
         $this->erro_campo = "tre15_linhatransporteitinerario";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->tre15_meiotransporte)!="" || isset($GLOBALS["HTTP_POST_VARS"]["tre15_meiotransporte"])){ 
       $sql  .= $virgula." tre15_meiotransporte = $this->tre15_meiotransporte ";
       $virgula = ",";
       if(trim($this->tre15_meiotransporte) == null ){ 
         $this->erro_sql = " Campo Meio de transporte não informado.";
         $this->erro_campo = "tre15_meiotransporte";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     $sql .= " where ";
     if($tre15_sequencial!=null){
       $sql .= " tre15_sequencial = $this->tre15_sequencial";
     }
     $result = db_query($sql);
     if (!$result) { 
       $this->erro_banco = str_replace("\n","",@pg_last_error());
       $this->erro_sql   = "Itinerario Meio Transporte não Alterado. Alteração Abortada.\\n";
         $this->erro_sql .= "Valores : ".$this->tre15_sequencial;
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       $this->numrows_alterar = 0;
       return false;
     } else {
       if (pg_affected_rows($result) == 0) {
         $this->erro_banco = "";
         $this->erro_sql = "Itinerario Meio Transporte não foi Alterado. Alteração Executada.\\n";
         $this->erro_sql .= "Valores : ".$this->tre15_sequencial;
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "1";
         $this->numrows_alterar = 0;
         return true;
       } else {
         $this->erro_banco = "";
         $this->erro_sql = "Alteração efetuada com sucesso.\\n";
         $this->erro_sql .= "Valores : ".$this->tre15_sequencial;
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "1";
         $this->numrows_alterar = pg_affected_rows($result);
         return true;
       } 
     } 
   } 

    public function excluir($tre15_sequencial=null, $dbwhere = null)
    {
     $sql = " delete from itinerariomeiotransporte
                    where ";
     $sql2 = "";
     if (empty($dbwhere)) {
        if (!empty($tre15_sequencial)){
          if (!empty($sql2)) {
            $sql2 .= " and ";
          }
          $sql2 .= " tre15_sequencial = $tre15_sequencial ";
        }
     } else {
       $sql2 = $dbwhere;
     }
     $result = db_query($sql.$sql2);
     if ($result == false) { 
       $this->erro_banco = str_replace("\n","",@pg_last_error());
       $this->erro_sql   = "Itinerario Meio Transporte não Excluído. Exclusão Abortada.\\n";
       $this->erro_sql .= "Valores : ".$tre15_sequencial;
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       $this->numrows_excluir = 0;
       return false;
     } else {
       if (pg_affected_rows($result) == 0) {
         $this->erro_banco = "";
         $this->erro_sql = "Itinerario Meio Transporte não Encontrado. Exclusão não Efetuada.\\n";
         $this->erro_sql .= "Valores : ".$tre15_sequencial;
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "1";
         $this->numrows_excluir = 0;
         return true;
       } else {
         $this->erro_banco = "";
         $this->erro_sql = "Exclusão efetuada com sucesso.\\n";
         $this->erro_sql .= "Valores : ".$tre15_sequencial;
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "1";
         $this->numrows_excluir = pg_affected_rows($result);
         return true;
       } 
     } 
   } 

    public function sql_record($sql)
    {
     $result = db_query($sql);
     if (!$result) {
       $this->numrows    = 0;
       $this->erro_banco = str_replace("\n","",@pg_last_error());
       $this->erro_sql   = "Erro ao selecionar os registros.";
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }
     $this->numrows = pg_num_rows($result);
      if ($this->numrows == 0) {
        $this->erro_banco = "";
        $this->erro_sql   = "Record Vazio na Tabela:itinerariomeiotransporte";
        $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
        $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
        $this->erro_status = "0";
        return false;
      }
     return $result;
   }

    public function sql_query($tre15_sequencial = null,$campos = "*", $ordem = null, $dbwhere = "") { 

     $sql  = "select {$campos}";
     $sql .= "  from itinerariomeiotransporte ";
     $sql .= "      inner join linhatransporteitinerario  on  linhatransporteitinerario.tre09_sequencial = itinerariomeiotransporte.tre15_linhatransporteitinerario";
     $sql .= "      inner join linhatransporte  on  linhatransporte.tre06_sequencial = linhatransporteitinerario.tre09_linhatransporte";
     $sql2 = "";
     if (empty($dbwhere)) {
       if (!empty($tre15_sequencial)) {
         $sql2 .= " where itinerariomeiotransporte.tre15_sequencial = $tre15_sequencial "; 
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

    public function sql_query_file($tre15_sequencial = null, $campos = "*", $ordem = null, $dbwhere = "") {

     $sql  = "select {$campos} ";
     $sql .= "  from itinerariomeiotransporte ";
     $sql2 = "";
     if (empty($dbwhere)) {
       if (!empty($tre15_sequencial)){
         $sql2 .= " where itinerariomeiotransporte.tre15_sequencial = $tre15_sequencial "; 
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
