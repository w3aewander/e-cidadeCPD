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

class cl_itinerariodificuldadesacesso
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
    public $tre16_sequencial = 0; 
    public $tre16_linhatransporteitinerario = 0; 
    public $tre16_porteira = 'f'; 
    public $tre16_mataburro = 'f'; 
    public $tre16_colchete = 'f'; 
    public $tre16_atoleiro = 'f'; 
    public $tre16_ponterustica = 'f'; 
   // cria propriedade com as variaveis do arquivo 
    public $campos = "
                 tre16_sequencial = int4 = Sequencial 
                 tre16_linhatransporteitinerario = int4 = Itinerário 
                 tre16_porteira = bool = Tem Porteira 
                 tre16_mataburro = bool = Tem Mata-Burro 
                 tre16_colchete = bool = Tem Colchete 
                 tre16_atoleiro = bool = Atoleiro 
                 tre16_ponterustica = bool = Ponte Rústica 
                 ";

    public function __construct()
    {
        $this->rotulo = new rotulo("itinerariodificuldadesacesso"); 
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
       $this->tre16_sequencial = ($this->tre16_sequencial == ""?@$GLOBALS["HTTP_POST_VARS"]["tre16_sequencial"]:$this->tre16_sequencial);
       $this->tre16_linhatransporteitinerario = ($this->tre16_linhatransporteitinerario == ""?@$GLOBALS["HTTP_POST_VARS"]["tre16_linhatransporteitinerario"]:$this->tre16_linhatransporteitinerario);
       $this->tre16_porteira = ($this->tre16_porteira == ""?@$GLOBALS["HTTP_POST_VARS"]["tre16_porteira"]:$this->tre16_porteira);
       $this->tre16_mataburro = ($this->tre16_mataburro == ""?@$GLOBALS["HTTP_POST_VARS"]["tre16_mataburro"]:$this->tre16_mataburro);
       $this->tre16_colchete = ($this->tre16_colchete == ""?@$GLOBALS["HTTP_POST_VARS"]["tre16_colchete"]:$this->tre16_colchete);
       $this->tre16_atoleiro = ($this->tre16_atoleiro == ""?@$GLOBALS["HTTP_POST_VARS"]["tre16_atoleiro"]:$this->tre16_atoleiro);
       $this->tre16_ponterustica = ($this->tre16_ponterustica == ""?@$GLOBALS["HTTP_POST_VARS"]["tre16_ponterustica"]:$this->tre16_ponterustica);
     }else{
       $this->tre16_sequencial = ($this->tre16_sequencial == ""?@$GLOBALS["HTTP_POST_VARS"]["tre16_sequencial"]:$this->tre16_sequencial);
     }
   }

    public function incluir($tre16_sequencial)
    {
      $this->atualizacampos();
     if($this->tre16_linhatransporteitinerario == null ){ 
       $this->erro_sql = " Campo Itinerário não informado.";
       $this->erro_campo = "tre16_linhatransporteitinerario";
       $this->erro_banco = "";
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }
     if($this->tre16_porteira == null ){ 
       $this->erro_sql = " Campo Tem Porteira não informado.";
       $this->erro_campo = "tre16_porteira";
       $this->erro_banco = "";
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }
     if($this->tre16_mataburro == null ){ 
       $this->erro_sql = " Campo Tem Mata-Burro não informado.";
       $this->erro_campo = "tre16_mataburro";
       $this->erro_banco = "";
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }
     if($this->tre16_colchete == null ){ 
       $this->erro_sql = " Campo Tem Colchete não informado.";
       $this->erro_campo = "tre16_colchete";
       $this->erro_banco = "";
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }
     if($this->tre16_atoleiro == null ){ 
       $this->erro_sql = " Campo Atoleiro não informado.";
       $this->erro_campo = "tre16_atoleiro";
       $this->erro_banco = "";
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }
     if($this->tre16_ponterustica == null ){ 
       $this->erro_sql = " Campo Ponte Rústica não informado.";
       $this->erro_campo = "tre16_ponterustica";
       $this->erro_banco = "";
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }
     if($tre16_sequencial == "" || $tre16_sequencial == null ){
       $result = db_query("select nextval('itinerariodificuldadesacesso_tre16_sequencial_seq')"); 
       if($result==false){
         $this->erro_banco = str_replace("\n","",@pg_last_error());
         $this->erro_sql   = "Verifique o cadastro da sequencia: itinerariodificuldadesacesso_tre16_sequencial_seq do campo: tre16_sequencial"; 
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false; 
       }
       $this->tre16_sequencial = pg_result($result,0,0); 
     }else{
       $result = db_query("select last_value from itinerariodificuldadesacesso_tre16_sequencial_seq");
       if(($result != false) && (pg_result($result,0,0) < $tre16_sequencial)){
         $this->erro_sql = " Campo tre16_sequencial maior que último número da sequencia.";
         $this->erro_banco = "Sequencia menor que este número.";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }else{
         $this->tre16_sequencial = $tre16_sequencial; 
       }
     }
     if(($this->tre16_sequencial == null) || ($this->tre16_sequencial == "") ){ 
       $this->erro_sql = " Campo tre16_sequencial não declarado.";
       $this->erro_banco = "Chave Primaria zerada.";
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }
     $sql = "insert into itinerariodificuldadesacesso(
                                       tre16_sequencial 
                                      ,tre16_linhatransporteitinerario 
                                      ,tre16_porteira 
                                      ,tre16_mataburro 
                                      ,tre16_colchete 
                                      ,tre16_atoleiro 
                                      ,tre16_ponterustica 
                       )
                values (
                                $this->tre16_sequencial 
                               ,$this->tre16_linhatransporteitinerario 
                               ,'$this->tre16_porteira' 
                               ,'$this->tre16_mataburro' 
                               ,'$this->tre16_colchete' 
                               ,'$this->tre16_atoleiro' 
                               ,'$this->tre16_ponterustica' 
                      )";
     $result = db_query($sql); 
     if($result==false){ 
       $this->erro_banco = str_replace("\n","",@pg_last_error());
       if( strpos(strtolower($this->erro_banco),"duplicate key") != 0 ){
         $this->erro_sql   = "Itinerario Dificuldades Acesso ($this->tre16_sequencial) não Incluído. Inclusão Abortada.";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_banco = "Itinerario Dificuldades Acesso já Cadastrado";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       }else{
         $this->erro_sql   = "Itinerario Dificuldades Acesso ($this->tre16_sequencial) não Incluído. Inclusão Abortada.";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       }
       $this->erro_status = "0";
       $this->numrows_incluir= 0;
       return false;
     }
     $this->erro_banco = "";
     $this->erro_sql = "Inclusão efetuada com sucesso.\\n";
         $this->erro_sql .= "Valores : ".$this->tre16_sequencial;
     $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
     $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
     $this->erro_status = "1";
     $this->numrows_incluir= pg_affected_rows($result);
     return true;
   } 

    public function alterar($tre16_sequencial=null)
    {
      $this->atualizacampos();
     $sql = " update itinerariodificuldadesacesso set ";
     $virgula = "";
     if(trim($this->tre16_sequencial)!="" || isset($GLOBALS["HTTP_POST_VARS"]["tre16_sequencial"])){ 
       $sql  .= $virgula." tre16_sequencial = $this->tre16_sequencial ";
       $virgula = ",";
       if(trim($this->tre16_sequencial) == null ){ 
         $this->erro_sql = " Campo Sequencial não informado.";
         $this->erro_campo = "tre16_sequencial";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->tre16_linhatransporteitinerario)!="" || isset($GLOBALS["HTTP_POST_VARS"]["tre16_linhatransporteitinerario"])){ 
       $sql  .= $virgula." tre16_linhatransporteitinerario = $this->tre16_linhatransporteitinerario ";
       $virgula = ",";
       if(trim($this->tre16_linhatransporteitinerario) == null ){ 
         $this->erro_sql = " Campo Itinerário não informado.";
         $this->erro_campo = "tre16_linhatransporteitinerario";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->tre16_porteira)!="" || isset($GLOBALS["HTTP_POST_VARS"]["tre16_porteira"])){ 
       $sql  .= $virgula." tre16_porteira = '$this->tre16_porteira' ";
       $virgula = ",";
       if(trim($this->tre16_porteira) == null ){ 
         $this->erro_sql = " Campo Tem Porteira não informado.";
         $this->erro_campo = "tre16_porteira";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->tre16_mataburro)!="" || isset($GLOBALS["HTTP_POST_VARS"]["tre16_mataburro"])){ 
       $sql  .= $virgula." tre16_mataburro = '$this->tre16_mataburro' ";
       $virgula = ",";
       if(trim($this->tre16_mataburro) == null ){ 
         $this->erro_sql = " Campo Tem Mata-Burro não informado.";
         $this->erro_campo = "tre16_mataburro";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->tre16_colchete)!="" || isset($GLOBALS["HTTP_POST_VARS"]["tre16_colchete"])){ 
       $sql  .= $virgula." tre16_colchete = '$this->tre16_colchete' ";
       $virgula = ",";
       if(trim($this->tre16_colchete) == null ){ 
         $this->erro_sql = " Campo Tem Colchete não informado.";
         $this->erro_campo = "tre16_colchete";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->tre16_atoleiro)!="" || isset($GLOBALS["HTTP_POST_VARS"]["tre16_atoleiro"])){ 
       $sql  .= $virgula." tre16_atoleiro = '$this->tre16_atoleiro' ";
       $virgula = ",";
       if(trim($this->tre16_atoleiro) == null ){ 
         $this->erro_sql = " Campo Atoleiro não informado.";
         $this->erro_campo = "tre16_atoleiro";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->tre16_ponterustica)!="" || isset($GLOBALS["HTTP_POST_VARS"]["tre16_ponterustica"])){ 
       $sql  .= $virgula." tre16_ponterustica = '$this->tre16_ponterustica' ";
       $virgula = ",";
       if(trim($this->tre16_ponterustica) == null ){ 
         $this->erro_sql = " Campo Ponte Rústica não informado.";
         $this->erro_campo = "tre16_ponterustica";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     $sql .= " where ";
     if($tre16_sequencial!=null){
       $sql .= " tre16_sequencial = $this->tre16_sequencial";
     }
     $result = db_query($sql);
     if (!$result) { 
       $this->erro_banco = str_replace("\n","",@pg_last_error());
       $this->erro_sql   = "Itinerario Dificuldades Acesso não Alterado. Alteração Abortada.\\n";
         $this->erro_sql .= "Valores : ".$this->tre16_sequencial;
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       $this->numrows_alterar = 0;
       return false;
     } else {
       if (pg_affected_rows($result) == 0) {
         $this->erro_banco = "";
         $this->erro_sql = "Itinerario Dificuldades Acesso não foi Alterado. Alteração Executada.\\n";
         $this->erro_sql .= "Valores : ".$this->tre16_sequencial;
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "1";
         $this->numrows_alterar = 0;
         return true;
       } else {
         $this->erro_banco = "";
         $this->erro_sql = "Alteração efetuada com sucesso.\\n";
         $this->erro_sql .= "Valores : ".$this->tre16_sequencial;
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "1";
         $this->numrows_alterar = pg_affected_rows($result);
         return true;
       } 
     } 
   } 

    public function excluir($tre16_sequencial=null, $dbwhere = null)
    {
     $sql = " delete from itinerariodificuldadesacesso
                    where ";
     $sql2 = "";
     if (empty($dbwhere)) {
        if (!empty($tre16_sequencial)){
          if (!empty($sql2)) {
            $sql2 .= " and ";
          }
          $sql2 .= " tre16_sequencial = $tre16_sequencial ";
        }
     } else {
       $sql2 = $dbwhere;
     }
     $result = db_query($sql.$sql2);
     if ($result == false) { 
       $this->erro_banco = str_replace("\n","",@pg_last_error());
       $this->erro_sql   = "Itinerario Dificuldades Acesso não Excluído. Exclusão Abortada.\\n";
       $this->erro_sql .= "Valores : ".$tre16_sequencial;
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       $this->numrows_excluir = 0;
       return false;
     } else {
       if (pg_affected_rows($result) == 0) {
         $this->erro_banco = "";
         $this->erro_sql = "Itinerario Dificuldades Acesso não Encontrado. Exclusão não Efetuada.\\n";
         $this->erro_sql .= "Valores : ".$tre16_sequencial;
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "1";
         $this->numrows_excluir = 0;
         return true;
       } else {
         $this->erro_banco = "";
         $this->erro_sql = "Exclusão efetuada com sucesso.\\n";
         $this->erro_sql .= "Valores : ".$tre16_sequencial;
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
        $this->erro_sql   = "Record Vazio na Tabela:itinerariodificuldadesacesso";
        $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
        $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
        $this->erro_status = "0";
        return false;
      }
     return $result;
   }

    public function sql_query($tre16_sequencial = null,$campos = "*", $ordem = null, $dbwhere = "") { 

     $sql  = "select {$campos}";
     $sql .= "  from itinerariodificuldadesacesso ";
     $sql .= "      inner join linhatransporteitinerario  on  linhatransporteitinerario.tre09_sequencial = itinerariodificuldadesacesso.tre16_linhatransporteitinerario";
     $sql .= "      inner join linhatransporte  on  linhatransporte.tre06_sequencial = linhatransporteitinerario.tre09_linhatransporte";
     $sql2 = "";
     if (empty($dbwhere)) {
       if (!empty($tre16_sequencial)) {
         $sql2 .= " where itinerariodificuldadesacesso.tre16_sequencial = $tre16_sequencial "; 
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

    public function sql_query_file($tre16_sequencial = null, $campos = "*", $ordem = null, $dbwhere = "") {

     $sql  = "select {$campos} ";
     $sql .= "  from itinerariodificuldadesacesso ";
     $sql2 = "";
     if (empty($dbwhere)) {
       if (!empty($tre16_sequencial)){
         $sql2 .= " where itinerariodificuldadesacesso.tre16_sequencial = $tre16_sequencial "; 
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
