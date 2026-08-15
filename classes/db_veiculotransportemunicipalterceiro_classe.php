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

class cl_veiculotransportemunicipalterceiro
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
    public $tre03_sequencial = 0; 
    public $tre03_cgm = 0; 
    public $tre03_veiculotransportemunicipal = 0; 
    public $tre03_placa = null; 
    public $tre03_renavam = null; 
   // cria propriedade com as variaveis do arquivo 
    public $campos = "
                 tre03_sequencial = int4 = Sequencial 
                 tre03_cgm = int4 = Numcgm 
                 tre03_veiculotransportemunicipal = int4 = Sequencial 
                 tre03_placa = varchar(8) = Placa 
                 tre03_renavam = varchar(11) = Renavam 
                 ";

    public function __construct()
    {
        $this->rotulo = new rotulo("veiculotransportemunicipalterceiro"); 
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
       $this->tre03_sequencial = ($this->tre03_sequencial == ""?@$GLOBALS["HTTP_POST_VARS"]["tre03_sequencial"]:$this->tre03_sequencial);
       $this->tre03_cgm = ($this->tre03_cgm == ""?@$GLOBALS["HTTP_POST_VARS"]["tre03_cgm"]:$this->tre03_cgm);
       $this->tre03_veiculotransportemunicipal = ($this->tre03_veiculotransportemunicipal == ""?@$GLOBALS["HTTP_POST_VARS"]["tre03_veiculotransportemunicipal"]:$this->tre03_veiculotransportemunicipal);
       $this->tre03_placa = ($this->tre03_placa == ""?@$GLOBALS["HTTP_POST_VARS"]["tre03_placa"]:$this->tre03_placa);
       $this->tre03_renavam = ($this->tre03_renavam == ""?@$GLOBALS["HTTP_POST_VARS"]["tre03_renavam"]:$this->tre03_renavam);
     }else{
       $this->tre03_sequencial = ($this->tre03_sequencial == ""?@$GLOBALS["HTTP_POST_VARS"]["tre03_sequencial"]:$this->tre03_sequencial);
     }
   }

    public function incluir($tre03_sequencial)
    {
      $this->atualizacampos();
     if($this->tre03_cgm == null ){ 
       $this->erro_sql = " Campo Numcgm não informado.";
       $this->erro_campo = "tre03_cgm";
       $this->erro_banco = "";
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }
     if($this->tre03_veiculotransportemunicipal == null ){ 
       $this->tre03_veiculotransportemunicipal = "0";
     }
    if($tre03_sequencial == "" || $tre03_sequencial == null ){
      $result = db_query("select nextval('veiculotransportemunicipalterceiro_tre03_sequencial_seq')"); 
      if($result==false){
        $this->erro_banco = str_replace("\n","",@pg_last_error());
        $this->erro_sql   = "Verifique o cadastro da sequencia: veiculotransportemunicipalterceiro_tre03_sequencial_seq do campo: tre03_sequencial"; 
        $this->erro_msg   = "Usurio: \\n\\n ".$this->erro_sql." \\n\\n";
        $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
        $this->erro_status = "0";
        return false; 
      }
      $this->tre03_sequencial = pg_result($result,0,0); 
    }else{
      $result = db_query("select last_value from veiculotransportemunicipalterceiro_tre03_sequencial_seq");
      if(($result != false) && (pg_result($result,0,0) < $tre03_sequencial)){
        $this->erro_sql = " Campo tre03_sequencial maior que ltimo nmero da sequencia.";
        $this->erro_banco = "Sequencia menor que este nmero.";
        $this->erro_msg   = "Usurio: \\n\\n ".$this->erro_sql." \\n\\n";
        $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
        $this->erro_status = "0";
        return false;
      }else{
        $this->tre03_sequencial = $tre03_sequencial; 
      }
    }
 
     $sql = "insert into veiculotransportemunicipalterceiro(
                                       tre03_sequencial 
                                      ,tre03_cgm 
                                      ,tre03_veiculotransportemunicipal 
                                      ,tre03_placa 
                                      ,tre03_renavam 
                       )
                values (
                                $this->tre03_sequencial 
                               ,$this->tre03_cgm 
                               ,$this->tre03_veiculotransportemunicipal 
                               ,'$this->tre03_placa' 
                               ,'$this->tre03_renavam' 
                      )";
     $result = db_query($sql); 
     if($result==false){ 
       $this->erro_banco = str_replace("\n","",@pg_last_error());
       if( strpos(strtolower($this->erro_banco),"duplicate key") != 0 ){
         $this->erro_sql   = "Veículo transporte municipal terceiro ($this->tre03_sequencial) não Incluído. Inclusão Abortada.";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_banco = "Veículo transporte municipal terceiro já Cadastrado";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       }else{
         $this->erro_sql   = "Veículo transporte municipal terceiro ($this->tre03_sequencial) não Incluído. Inclusão Abortada.";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       }
       $this->erro_status = "0";
       $this->numrows_incluir= 0;
       return false;
     }
     $this->erro_banco = "";
     $this->erro_sql = "Inclusão efetuada com sucesso.\\n";
         $this->erro_sql .= "Valores : ".$this->tre03_sequencial;
     $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
     $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
     $this->erro_status = "1";
     $this->numrows_incluir= pg_affected_rows($result);
     $lSessaoDesativarAccount = db_getsession("DB_desativar_account", false);
     if (!isset($lSessaoDesativarAccount) || (isset($lSessaoDesativarAccount)
       && ($lSessaoDesativarAccount === false))) {

       $resaco = $this->sql_record($this->sql_query_file($this->tre03_sequencial  ));
       if(($resaco!=false)||($this->numrows!=0)){

         $resac = db_query("select nextval('db_acount_id_acount_seq') as acount");
         $acount = pg_result($resac,0,0);
         $resac = db_query("insert into db_acountacesso values($acount,".db_getsession("DB_acessado").")");
         $resac = db_query("insert into db_acountkey values($acount,20079,'$this->tre03_sequencial','I')");
         $resac = db_query("insert into db_acount values($acount,3600,20079,'','".AddSlashes(pg_result($resaco,0,'tre03_sequencial'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,3600,20080,'','".AddSlashes(pg_result($resaco,0,'tre03_cgm'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,3600,20081,'','".AddSlashes(pg_result($resaco,0,'tre03_veiculotransportemunicipal'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,3600,203092742,'','".AddSlashes(pg_result($resaco,0,'tre03_placa'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,3600,216293371,'','".AddSlashes(pg_result($resaco,0,'tre03_renavam'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
       }
     }
     return true;
   } 

    public function alterar($tre03_sequencial=null)
    {
      $this->atualizacampos();
     $sql = " update veiculotransportemunicipalterceiro set ";
     $virgula = "";
     if(trim($this->tre03_sequencial)!="" || isset($GLOBALS["HTTP_POST_VARS"]["tre03_sequencial"])){ 
       $sql  .= $virgula." tre03_sequencial = $this->tre03_sequencial ";
       $virgula = ",";
       if(trim($this->tre03_sequencial) == null ){ 
         $this->erro_sql = " Campo Sequencial não informado.";
         $this->erro_campo = "tre03_sequencial";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->tre03_cgm)!="" || isset($GLOBALS["HTTP_POST_VARS"]["tre03_cgm"])){ 
       $sql  .= $virgula." tre03_cgm = $this->tre03_cgm ";
       $virgula = ",";
       if(trim($this->tre03_cgm) == null ){ 
         $this->erro_sql = " Campo Numcgm não informado.";
         $this->erro_campo = "tre03_cgm";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->tre03_veiculotransportemunicipal)!="" || isset($GLOBALS["HTTP_POST_VARS"]["tre03_veiculotransportemunicipal"])){ 
        if(trim($this->tre03_veiculotransportemunicipal)=="" && isset($GLOBALS["HTTP_POST_VARS"]["tre03_veiculotransportemunicipal"])){ 
           $this->tre03_veiculotransportemunicipal = "0" ; 
        } 
       $sql  .= $virgula." tre03_veiculotransportemunicipal = $this->tre03_veiculotransportemunicipal ";
       $virgula = ",";
     }
     if(trim($this->tre03_placa)!="" || isset($GLOBALS["HTTP_POST_VARS"]["tre03_placa"])){ 
       $sql  .= $virgula." tre03_placa = '$this->tre03_placa' ";
       $virgula = ",";
     }
     if(trim($this->tre03_renavam)!="" || isset($GLOBALS["HTTP_POST_VARS"]["tre03_renavam"])){ 
       $sql  .= $virgula." tre03_renavam = '$this->tre03_renavam' ";
       $virgula = ",";
     }
     $sql .= " where ";
     if($tre03_sequencial!=null){
       $sql .= " tre03_sequencial = $this->tre03_sequencial";
     }
     $lSessaoDesativarAccount = db_getsession("DB_desativar_account", false);
     if (!isset($lSessaoDesativarAccount) || (isset($lSessaoDesativarAccount)
       && ($lSessaoDesativarAccount === false))) {

       $resaco = $this->sql_record($this->sql_query_file($this->tre03_sequencial));
       if ($this->numrows > 0) {

         for ($conresaco = 0; $conresaco < $this->numrows; $conresaco++) {

           $resac = db_query("select nextval('db_acount_id_acount_seq') as acount");
           $acount = pg_result($resac,0,0);
           $resac = db_query("insert into db_acountacesso values($acount,".db_getsession("DB_acessado").")");
           $resac = db_query("insert into db_acountkey values($acount,20079,'$this->tre03_sequencial','A')");
           if (isset($GLOBALS["HTTP_POST_VARS"]["tre03_sequencial"]) || $this->tre03_sequencial != "")
             $resac = db_query("insert into db_acount values($acount,3600,20079,'".AddSlashes(pg_result($resaco,$conresaco,'tre03_sequencial'))."','$this->tre03_sequencial',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           if (isset($GLOBALS["HTTP_POST_VARS"]["tre03_cgm"]) || $this->tre03_cgm != "")
             $resac = db_query("insert into db_acount values($acount,3600,20080,'".AddSlashes(pg_result($resaco,$conresaco,'tre03_cgm'))."','$this->tre03_cgm',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           if (isset($GLOBALS["HTTP_POST_VARS"]["tre03_veiculotransportemunicipal"]) || $this->tre03_veiculotransportemunicipal != "")
             $resac = db_query("insert into db_acount values($acount,3600,20081,'".AddSlashes(pg_result($resaco,$conresaco,'tre03_veiculotransportemunicipal'))."','$this->tre03_veiculotransportemunicipal',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           if (isset($GLOBALS["HTTP_POST_VARS"]["tre03_placa"]) || $this->tre03_placa != "")
             $resac = db_query("insert into db_acount values($acount,3600,203092742,'".AddSlashes(pg_result($resaco,$conresaco,'tre03_placa'))."','$this->tre03_placa',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           if (isset($GLOBALS["HTTP_POST_VARS"]["tre03_renavam"]) || $this->tre03_renavam != "")
             $resac = db_query("insert into db_acount values($acount,3600,216293371,'".AddSlashes(pg_result($resaco,$conresaco,'tre03_renavam'))."','$this->tre03_renavam',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         }
       }
     }
     $result = db_query($sql);
     if (!$result) { 
       $this->erro_banco = str_replace("\n","",@pg_last_error());
       $this->erro_sql   = "Veículo transporte municipal terceiro não Alterado. Alteração Abortada.\\n";
         $this->erro_sql .= "Valores : ".$this->tre03_sequencial;
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       $this->numrows_alterar = 0;
       return false;
     } else {
       if (pg_affected_rows($result) == 0) {
         $this->erro_banco = "";
         $this->erro_sql = "Veículo transporte municipal terceiro não foi Alterado. Alteração Executada.\\n";
         $this->erro_sql .= "Valores : ".$this->tre03_sequencial;
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "1";
         $this->numrows_alterar = 0;
         return true;
       } else {
         $this->erro_banco = "";
         $this->erro_sql = "Alteração efetuada com sucesso.\\n";
         $this->erro_sql .= "Valores : ".$this->tre03_sequencial;
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "1";
         $this->numrows_alterar = pg_affected_rows($result);
         return true;
       } 
     } 
   } 

    public function excluir($tre03_sequencial=null, $dbwhere = null)
    {
     $lSessaoDesativarAccount = db_getsession("DB_desativar_account", false);
     if (!isset($lSessaoDesativarAccount) || (isset($lSessaoDesativarAccount)
       && ($lSessaoDesativarAccount === false))) {

       if (empty($dbwhere)) {

         $resaco = $this->sql_record($this->sql_query_file($tre03_sequencial));
       } else { 
         $resaco = $this->sql_record($this->sql_query_file(null,"*",null,$dbwhere));
       }
       if (($resaco != false) || ($this->numrows!=0)) {

         for ($iresaco = 0; $iresaco < $this->numrows; $iresaco++) {

           $resac  = db_query("select nextval('db_acount_id_acount_seq') as acount");
           $acount = pg_result($resac,0,0);
           $resac  = db_query("insert into db_acountacesso values($acount,".db_getsession("DB_acessado").")");
           $resac  = db_query("insert into db_acountkey values($acount,20079,'$tre03_sequencial','E')");
           $resac  = db_query("insert into db_acount values($acount,3600,20079,'','".AddSlashes(pg_result($resaco,$iresaco,'tre03_sequencial'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           $resac  = db_query("insert into db_acount values($acount,3600,20080,'','".AddSlashes(pg_result($resaco,$iresaco,'tre03_cgm'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           $resac  = db_query("insert into db_acount values($acount,3600,20081,'','".AddSlashes(pg_result($resaco,$iresaco,'tre03_veiculotransportemunicipal'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           $resac  = db_query("insert into db_acount values($acount,3600,203092742,'','".AddSlashes(pg_result($resaco,$iresaco,'tre03_placa'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           $resac  = db_query("insert into db_acount values($acount,3600,216293371,'','".AddSlashes(pg_result($resaco,$iresaco,'tre03_renavam'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         }
       }
     }
     $sql = " delete from veiculotransportemunicipalterceiro
                    where ";
     $sql2 = "";
     if (empty($dbwhere)) {
        if (!empty($tre03_sequencial)){
          if (!empty($sql2)) {
            $sql2 .= " and ";
          }
          $sql2 .= " tre03_sequencial = $tre03_sequencial ";
        }
     } else {
       $sql2 = $dbwhere;
     }
     $result = db_query($sql.$sql2);
     if ($result == false) { 
       $this->erro_banco = str_replace("\n","",@pg_last_error());
       $this->erro_sql   = "Veículo transporte municipal terceiro não Excluído. Exclusão Abortada.\\n";
       $this->erro_sql .= "Valores : ".$tre03_sequencial;
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       $this->numrows_excluir = 0;
       return false;
     } else {
       if (pg_affected_rows($result) == 0) {
         $this->erro_banco = "";
         $this->erro_sql = "Veículo transporte municipal terceiro não Encontrado. Exclusão não Efetuada.\\n";
         $this->erro_sql .= "Valores : ".$tre03_sequencial;
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "1";
         $this->numrows_excluir = 0;
         return true;
       } else {
         $this->erro_banco = "";
         $this->erro_sql = "Exclusão efetuada com sucesso.\\n";
         $this->erro_sql .= "Valores : ".$tre03_sequencial;
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
        $this->erro_sql   = "Record Vazio na Tabela:veiculotransportemunicipalterceiro";
        $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
        $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
        $this->erro_status = "0";
        return false;
      }
     return $result;
   }

    public function sql_query($tre03_sequencial = null,$campos = "*", $ordem = null, $dbwhere = "") { 

     $sql  = "select {$campos}";
     $sql .= "  from veiculotransportemunicipalterceiro ";
     $sql .= "      inner join cgm  on  cgm.z01_numcgm = veiculotransportemunicipalterceiro.tre03_cgm";
     $sql .= "      left  join veiculotransportemunicipal  on  veiculotransportemunicipal.tre01_sequencial = veiculotransportemunicipalterceiro.tre03_veiculotransportemunicipal";
     $sql .= "      left  join tipotransportemunicipal  on  tipotransportemunicipal.tre00_sequencial = veiculotransportemunicipal.tre01_tipotransportemunicipal";
     $sql .= "      left  join tipoveiculo  on  tipoveiculo.tre17_sequencial = veiculotransportemunicipal.tre01_tipoveiculo";
     $sql2 = "";
     if (empty($dbwhere)) {
       if (!empty($tre03_sequencial)) {
         $sql2 .= " where veiculotransportemunicipalterceiro.tre03_sequencial = $tre03_sequencial "; 
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

    public function sql_query_file($tre03_sequencial = null, $campos = "*", $ordem = null, $dbwhere = "") {

     $sql  = "select {$campos} ";
     $sql .= "  from veiculotransportemunicipalterceiro ";
     $sql2 = "";
     if (empty($dbwhere)) {
       if (!empty($tre03_sequencial)){
         $sql2 .= " where veiculotransportemunicipalterceiro.tre03_sequencial = $tre03_sequencial "; 
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
