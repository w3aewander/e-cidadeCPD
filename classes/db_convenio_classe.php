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

//MODULO: pessoal
//CLASSE DA ENTIDADE convenio
class cl_convenio
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
    public $r56_codrel = 0; 
    public $r56_local = 0; 
    public $r56_posano = 0; 
    public $r56_posmes = 0; 
    public $r56_posreg = 0; 
    public $r56_poseve = 0; 
    public $r56_posq01 = 0; 
    public $r56_posq02 = 0; 
    public $r56_posq03 = 0; 
    public $r56_vq01 = 'f'; 
    public $r56_vq02 = 'f'; 
    public $r56_vq03 = 'f'; 
    public $r56_descr = 0; 
    public $r56_dirarq = 0; 
    public $r56_instit = 0; 
    public $r56_linhastrailler = 0; 
    public $r56_linhasheader = 0; 
    public $r56_posrubrica = null; 
    public $r56_poscpf = null; 
   // cria propriedade com as variaveis do arquivo 
    public $campos = "
                 r56_codrel = bpchar(4) = convênio 
                 r56_local = bpchar(40) = leitura 
                 r56_posano = bpchar(6) = leitura do ano 
                 r56_posmes = bpchar(6) = leitura do mês 
                 r56_posreg = bpchar(6) = Servidor 
                 r56_poseve = bpchar(6) = relacionamento 
                 r56_posq01 = bpchar(6) = rubrica 1 
                 r56_posq02 = bpchar(6) = rubrica 02 
                 r56_posq03 = bpchar(6) = rubrica 3 
                 r56_vq01 = bool = define se e valor ou quantidad 
                 r56_vq02 = bool = def.se quant02 e valor ou quan 
                 r56_vq03 = bool = def.se rubr.3 e valor ou qtd. 
                 r56_descr = bpchar(40) = convênio 
                 r56_dirarq = bpchar(40) = Caminho 
                 r56_instit = int4 = Cod. Instituição 
                 r56_linhastrailler = int4 = Linhas trailler 
                 r56_linhasheader = int4 = Linhas header 
                 r56_posrubrica = varchar(6) = Posição da Rubrica 
                 r56_poscpf = varchar(6) = Busca por CPF 
                 ";

   //funcao construtor da classe
    public function cl_convenio()
    {
      //classes dos rotulos dos campos
        $this->rotulo = new rotulo("convenio");
        $this->pagina_retorno =  basename($GLOBALS["HTTP_SERVER_VARS"]["PHP_SELF"]);
    }

   //funcao erro
    public function erro($mostra, $retorna)
    {
        if (($this->erro_status == "0") || ($mostra == true && $this->erro_status != null )) {
            echo "<script>alert(\"".$this->erro_msg."\");</script>";
            if ($retorna==true) {
                echo "<script>location.href='".$this->pagina_retorno."'</script>";
            }
        }
    }

   // funcao para atualizar campos
    public function atualizacampos($exclusao = false)
    {
     if($exclusao==false){
       $this->r56_codrel = ($this->r56_codrel == ""?@$GLOBALS["HTTP_POST_VARS"]["r56_codrel"]:$this->r56_codrel);
       $this->r56_local = ($this->r56_local == ""?@$GLOBALS["HTTP_POST_VARS"]["r56_local"]:$this->r56_local);
       $this->r56_posano = ($this->r56_posano == ""?@$GLOBALS["HTTP_POST_VARS"]["r56_posano"]:$this->r56_posano);
       $this->r56_posmes = ($this->r56_posmes == ""?@$GLOBALS["HTTP_POST_VARS"]["r56_posmes"]:$this->r56_posmes);
       $this->r56_posreg = ($this->r56_posreg == ""?@$GLOBALS["HTTP_POST_VARS"]["r56_posreg"]:$this->r56_posreg);
       $this->r56_poseve = ($this->r56_poseve == ""?@$GLOBALS["HTTP_POST_VARS"]["r56_poseve"]:$this->r56_poseve);
       $this->r56_posq01 = ($this->r56_posq01 == ""?@$GLOBALS["HTTP_POST_VARS"]["r56_posq01"]:$this->r56_posq01);
       $this->r56_posq02 = ($this->r56_posq02 == ""?@$GLOBALS["HTTP_POST_VARS"]["r56_posq02"]:$this->r56_posq02);
       $this->r56_posq03 = ($this->r56_posq03 == ""?@$GLOBALS["HTTP_POST_VARS"]["r56_posq03"]:$this->r56_posq03);
       $this->r56_vq01 = ($this->r56_vq01 == "f"?@$GLOBALS["HTTP_POST_VARS"]["r56_vq01"]:$this->r56_vq01);
       $this->r56_vq02 = ($this->r56_vq02 == "f"?@$GLOBALS["HTTP_POST_VARS"]["r56_vq02"]:$this->r56_vq02);
       $this->r56_vq03 = ($this->r56_vq03 == "f"?@$GLOBALS["HTTP_POST_VARS"]["r56_vq03"]:$this->r56_vq03);
       $this->r56_descr = ($this->r56_descr == ""?@$GLOBALS["HTTP_POST_VARS"]["r56_descr"]:$this->r56_descr);
       $this->r56_dirarq = ($this->r56_dirarq == ""?@$GLOBALS["HTTP_POST_VARS"]["r56_dirarq"]:$this->r56_dirarq);
       $this->r56_instit = ($this->r56_instit == ""?@$GLOBALS["HTTP_POST_VARS"]["r56_instit"]:$this->r56_instit);
       $this->r56_linhastrailler = ($this->r56_linhastrailler == ""?@$GLOBALS["HTTP_POST_VARS"]["r56_linhastrailler"]:$this->r56_linhastrailler);
       $this->r56_linhasheader = ($this->r56_linhasheader == ""?@$GLOBALS["HTTP_POST_VARS"]["r56_linhasheader"]:$this->r56_linhasheader);
       $this->r56_posrubrica = ($this->r56_posrubrica == ""?@$GLOBALS["HTTP_POST_VARS"]["r56_posrubrica"]:$this->r56_posrubrica);
       $this->r56_poscpf = ($this->r56_poscpf == ""?@$GLOBALS["HTTP_POST_VARS"]["r56_poscpf"]:$this->r56_poscpf);
     }else{
       $this->r56_codrel = ($this->r56_codrel == ""?@$GLOBALS["HTTP_POST_VARS"]["r56_codrel"]:$this->r56_codrel);
       $this->r56_instit = ($this->r56_instit == ""?@$GLOBALS["HTTP_POST_VARS"]["r56_instit"]:$this->r56_instit);
     }
   }

   // funcao para inclusao
    public function incluir($r56_codrel, $r56_instit)
    {
        $this->atualizacampos();
        if ($this->r56_descr == null) {
            $this->erro_sql = " Campo convênio nao Informado.";
            $this->erro_campo = "r56_descr";
            $this->erro_banco = "";
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            return false;
        }
        if ($this->r56_local == null) {
            $this->erro_sql = " Campo leitura nao Informado.";
            $this->erro_campo = "r56_local";
            $this->erro_banco = "";
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            return false;
        }
        if ($this->r56_dirarq == null) {
            $this->erro_sql = " Campo Caminho nao Informado.";
            $this->erro_campo = "r56_dirarq";
            $this->erro_banco = "";
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            return false;
        }
        if ($this->r56_vq01 == null) {
            $this->erro_sql = " Campo define se e valor ou quantidad nao Informado.";
            $this->erro_campo = "r56_vq01";
            $this->erro_banco = "";
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            return false;
        }
        if ($this->r56_vq02 == null) {
            $this->erro_sql = " Campo def.se quant02 e valor ou quan nao Informado.";
            $this->erro_campo = "r56_vq02";
            $this->erro_banco = "";
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            return false;
        }
        if ($this->r56_vq03 == null) {
            $this->erro_sql = " Campo def.se rubr.3 e valor ou qtd. nao Informado.";
            $this->erro_campo = "r56_vq03";
            $this->erro_banco = "";
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            return false;
        }
        if ($this->r56_linhastrailler == null) {
            $this->r56_linhastrailler = "0";
        }
        if ($this->r56_linhasheader == null) {
            $this->r56_linhasheader = "0";
        }
        $this->r56_codrel = $r56_codrel;
        $this->r56_instit = $r56_instit;
        if (($this->r56_codrel == null) || ($this->r56_codrel == "")) {
            $this->erro_sql = " Campo r56_codrel nao declarado.";
            $this->erro_banco = "Chave Primaria zerada.";
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            return false;
        }
        if (($this->r56_instit == null) || ($this->r56_instit == "")) {
            $this->erro_sql = " Campo r56_instit nao declarado.";
            $this->erro_banco = "Chave Primaria zerada.";
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            return false;
        }
     $sql = "insert into convenio(
                                       r56_codrel 
                                      ,r56_local 
                                      ,r56_posano 
                                      ,r56_posmes 
                                      ,r56_posreg 
                                      ,r56_poseve 
                                      ,r56_posq01 
                                      ,r56_posq02 
                                      ,r56_posq03 
                                      ,r56_vq01 
                                      ,r56_vq02 
                                      ,r56_vq03 
                                      ,r56_descr 
                                      ,r56_dirarq 
                                      ,r56_instit 
                                      ,r56_linhastrailler 
                                      ,r56_linhasheader 
                                      ,r56_posrubrica 
                                      ,r56_poscpf 
                       )
                values (
                                '$this->r56_codrel' 
                               ,'$this->r56_local' 
                               ,'$this->r56_posano' 
                               ,'$this->r56_posmes' 
                               ,'$this->r56_posreg' 
                               ,'$this->r56_poseve' 
                               ,'$this->r56_posq01' 
                               ,'$this->r56_posq02' 
                               ,'$this->r56_posq03' 
                               ,'$this->r56_vq01' 
                               ,'$this->r56_vq02' 
                               ,'$this->r56_vq03' 
                               ,'$this->r56_descr' 
                               ,'$this->r56_dirarq' 
                               ,$this->r56_instit 
                               ,'$this->r56_linhastrailler' 
                               ,'$this->r56_linhasheader' 
                               ,'$this->r56_posrubrica' 
                               ,'$this->r56_poscpf' 
                      )";

     $result = db_query($sql); 
     if($result==false){ 
       $this->erro_banco = str_replace("\n","",@pg_last_error());
       if( strpos(strtolower($this->erro_banco),"duplicate key") != 0 ){
         $this->erro_sql   = "Definicao do convenio, local de leitura e toda a e ($this->r56_codrel."-".$this->r56_instit) não Incluído. Inclusão Abortada.";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_banco = "Definicao do convenio, local de leitura e toda a e já Cadastrado";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       }else{
         $this->erro_sql   = "Definicao do convenio, local de leitura e toda a e ($this->r56_codrel."-".$this->r56_instit) não Incluído. Inclusão Abortada.";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       }
       $this->erro_status = "0";
       $this->numrows_incluir= 0;
       return false;
     }
     $this->erro_banco = "";
     $this->erro_sql = "Inclusão efetuada com sucesso.\\n";
         $this->erro_sql .= "Valores : ".$this->r56_codrel."-".$this->r56_instit;
     $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
     $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
     $this->erro_status = "1";
     $this->numrows_incluir= pg_affected_rows($result);
     return true;
   } 

   // funcao para alteração
    public function alterar($r56_codrel = null, $r56_instit = null)
    {
      $this->atualizacampos();
     $sql = " update convenio set ";
     $virgula = "";
     if(trim($this->r56_codrel)!="" || isset($GLOBALS["HTTP_POST_VARS"]["r56_codrel"])){ 
       $sql  .= $virgula." r56_codrel = '{$this->r56_codrel}' ";
       $virgula = ",";
       if(trim($this->r56_codrel) == null ){ 
         $this->erro_sql = " Campo convênio não informado.";
         $this->erro_campo = "r56_codrel";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->r56_local)!="" || isset($GLOBALS["HTTP_POST_VARS"]["r56_local"])){ 
       $sql  .= $virgula." r56_local = '$this->r56_local' ";
       $virgula = ",";
     }
     if(trim($this->r56_posano)!="" || isset($GLOBALS["HTTP_POST_VARS"]["r56_posano"]) || trim($this->r56_posano) == ""){ 
       $sql  .= $virgula." r56_posano = '$this->r56_posano' ";
       $virgula = ",";
     }
     if(trim($this->r56_posmes)!="" || isset($GLOBALS["HTTP_POST_VARS"]["r56_posmes"]) || trim($this->r56_posmes) == ""){ 
       $sql  .= $virgula." r56_posmes = '$this->r56_posmes' ";
       $virgula = ",";
     }
     if(trim($this->r56_posreg)!="" || isset($GLOBALS["HTTP_POST_VARS"]["r56_posreg"]) || trim($this->r56_posreg) == ""){ 
       $sql  .= $virgula." r56_posreg = '$this->r56_posreg' ";
       $virgula = ",";
     }
     if(trim($this->r56_poseve)!="" || isset($GLOBALS["HTTP_POST_VARS"]["r56_poseve"]) || trim($this->r56_poseve) == ""){ 
       $sql  .= $virgula." r56_poseve = '$this->r56_poseve' ";
       $virgula = ",";
     }
     if(trim($this->r56_posq01)!="" || isset($GLOBALS["HTTP_POST_VARS"]["r56_posq01"]) || trim($this->r56_posq01) == ""){ 
       $sql  .= $virgula." r56_posq01 = '$this->r56_posq01' ";
       $virgula = ",";
     }
     if(trim($this->r56_posq02)!="" || isset($GLOBALS["HTTP_POST_VARS"]["r56_posq02"]) || trim($this->r56_posq02) == ""){ 
       $sql  .= $virgula." r56_posq02 = '$this->r56_posq02' ";
       $virgula = ",";
     }
     if(trim($this->r56_posq03)!="" || isset($GLOBALS["HTTP_POST_VARS"]["r56_posq03"]) || trim($this->r56_posq03) == ""){ 
       $sql  .= $virgula." r56_posq03 = '$this->r56_posq03' ";
       $virgula = ",";
     }
     if(trim($this->r56_vq01)!="" || isset($GLOBALS["HTTP_POST_VARS"]["r56_vq01"]) || trim($this->r56_vq01) == ""){ 
       $sql  .= $virgula." r56_vq01 = '$this->r56_vq01' ";
       $virgula = ",";
     }
     if(trim($this->r56_vq02)!="" || isset($GLOBALS["HTTP_POST_VARS"]["r56_vq02"]) || trim($this->r56_vq02) == ""){ 
       $sql  .= $virgula." r56_vq02 = '$this->r56_vq02' ";
       $virgula = ",";
     }
     if(trim($this->r56_vq03)!="" || isset($GLOBALS["HTTP_POST_VARS"]["r56_vq03"]) || trim($this->r56_vq03) == ""){ 
       $sql  .= $virgula." r56_vq03 = '$this->r56_vq03' ";
       $virgula = ",";
     }
     if(trim($this->r56_descr)!="" || isset($GLOBALS["HTTP_POST_VARS"]["r56_descr"])){ 
       $sql  .= $virgula." r56_descr = '$this->r56_descr' ";
       $virgula = ",";
     }
     if(trim($this->r56_dirarq)!="" || isset($GLOBALS["HTTP_POST_VARS"]["r56_dirarq"])){ 
       $sql  .= $virgula." r56_dirarq = '$this->r56_dirarq' ";
       $virgula = ",";
     }
     if(trim($this->r56_instit)!="" || isset($GLOBALS["HTTP_POST_VARS"]["r56_instit"])){ 
       $sql  .= $virgula." r56_instit = $this->r56_instit ";
       $virgula = ",";
       if(trim($this->r56_instit) == null ){ 
         $this->erro_sql = " Campo Cod. Instituição não informado.";
         $this->erro_campo = "r56_instit";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->r56_linhastrailler)!="" || isset($GLOBALS["HTTP_POST_VARS"]["r56_linhastrailler"])){ 
        if(trim($this->r56_linhastrailler)=="" && isset($GLOBALS["HTTP_POST_VARS"]["r56_linhastrailler"])){ 
           $this->r56_linhastrailler = "0" ; 
        } 
       $sql  .= $virgula." r56_linhastrailler = $this->r56_linhastrailler ";
       $virgula = ",";
     }
     if(trim($this->r56_linhasheader)!="" || isset($GLOBALS["HTTP_POST_VARS"]["r56_linhasheader"])){ 
        if(trim($this->r56_linhasheader)=="" && isset($GLOBALS["HTTP_POST_VARS"]["r56_linhasheader"])){ 
           $this->r56_linhasheader = "0" ; 
        } 
       $sql  .= $virgula." r56_linhasheader = $this->r56_linhasheader ";
       $virgula = ",";
     }
     if(trim($this->r56_posrubrica)!="" || isset($GLOBALS["HTTP_POST_VARS"]["r56_posrubrica"]) || trim($this->r56_posrubrica) == "" ){ 
       $sql  .= $virgula." r56_posrubrica = '$this->r56_posrubrica' ";
       $virgula = ",";
     }
     if(trim($this->r56_poscpf)!="" || isset($GLOBALS["HTTP_POST_VARS"]["r56_poscpf"]) || trim($this->r56_poscpf) == ""){ 
       $sql  .= $virgula." r56_poscpf = '$this->r56_poscpf' ";
       $virgula = ",";
     }
     $sql .= " where ";
     if($r56_codrel!=null){
       $sql .= " r56_codrel = '$this->r56_codrel'";
     }
     if($r56_instit!=null){
       $sql .= " and  r56_instit = $this->r56_instit";
     }

     $result = db_query($sql);
     if (!$result) { 
       $this->erro_banco = str_replace("\n","",@pg_last_error());
       $this->erro_sql   = "Definicao do convenio, local de leitura e toda a e não Alterado. Alteração Abortada.\\n";
         $this->erro_sql .= "Valores : ".$this->r56_codrel."-".$this->r56_instit;
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       $this->numrows_alterar = 0;
       return false;
     } else {
       if (pg_affected_rows($result) == 0) {
         $this->erro_banco = "";
         $this->erro_sql = "Definicao do convenio, local de leitura e toda a e não foi Alterado. Alteração Executada.\\n";
         $this->erro_sql .= "Valores : ".$this->r56_codrel."-".$this->r56_instit;
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "1";
         $this->numrows_alterar = 0;
         return true;
       } else {
         $this->erro_banco = "";
         $this->erro_sql = "Alteração efetuada com sucesso.\\n";
         $this->erro_sql .= "Valores : ".$this->r56_codrel."-".$this->r56_instit;
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "1";
         $this->numrows_alterar = pg_affected_rows($result);
         return true;
       } 
     } 
   } 

   // funcao para exclusao
    public function excluir($r56_codrel = null, $r56_instit = null, $dbwhere = null)
    {
        $sql = " delete from convenio
                    where ";
        $sql2 = "";
        if ($dbwhere==null || $dbwhere =="") {
            if ($r56_codrel != "") {
                if ($sql2!="") {
                      $sql2 .= " and ";
                }
                $sql2 .= " r56_codrel = '$r56_codrel' ";
            }
            if ($r56_instit != "") {
                if ($sql2!="") {
                    $sql2 .= " and ";
                }
                $sql2 .= " r56_instit = $r56_instit ";
            }
        } else {
            $sql2 = $dbwhere;
        }
        $result = db_query($sql.$sql2);
        if ($result==false) {
            $this->erro_banco = str_replace("\n", "", @pg_last_error());
            $this->erro_sql   = "Definicao do convenio, local de leitura e toda a e nao Excluído. Exclusão Abortada.\\n";
            $this->erro_sql .= "Valores : ".$r56_codrel."-".$r56_instit;
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            $this->numrows_excluir = 0;
            return false;
        } else {
            if (pg_affected_rows($result)==0) {
                $this->erro_banco = "";
                $this->erro_sql = "Definicao do convenio, local de leitura e toda a e nao Encontrado. Exclusão não Efetuada.\\n";
                $this->erro_sql .= "Valores : ".$r56_codrel."-".$r56_instit;
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "1";
                $this->numrows_excluir = 0;
                return true;
            } else {
                $this->erro_banco = "";
                $this->erro_sql = "Exclusão efetuada com Sucesso\\n";
                $this->erro_sql .= "Valores : ".$r56_codrel."-".$r56_instit;
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "1";
                $this->numrows_excluir = pg_affected_rows($result);
                return true;
            }
        }
    }

   // funcao do recordset
    public function sql_record($sql)
    {
        $result = db_query($sql);
        if ($result==false) {
            $this->numrows    = 0;
            $this->erro_banco = str_replace("\n", "", @pg_last_error());
            $this->erro_sql   = "Erro ao selecionar os registros.";
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            return false;
        }
        $this->numrows = pg_num_rows($result);
        if ($this->numrows==0) {
            $this->erro_banco = "";
            $this->erro_sql   = "Record Vazio na Tabela:convenio";
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            return false;
        }
        return $result;
    }

    public function sql_query($r56_codrel = null, $r56_instit = null, $campos = "*", $ordem = null, $dbwhere = "")
    {
        $sql = "select ";
        $sql .= $campos;
        $sql .= " from convenio ";
        $sql .= "      inner join db_config  on  db_config.codigo = convenio.r56_instit";
        $sql .= "      inner join cgm  on  cgm.z01_numcgm = db_config.numcgm";
        $sql2 = "";
        if ($dbwhere=="") {
            if ($r56_codrel!=null) {
                $sql2 .= " where convenio.r56_codrel = '$r56_codrel' ";
            }
            if ($r56_instit!=null) {
                if ($sql2!="") {
                     $sql2 .= " and ";
                } else {
                    $sql2 .= " where ";
                }
                $sql2 .= " convenio.r56_instit = $r56_instit ";
            }
        } elseif ($dbwhere != "") {
            $sql2 = " where $dbwhere";
        }
        $sql .= $sql2;
        if ($ordem != null) {
            $sql .= " order by {$ordem}";
        }
        return $sql;
    }

    public function sql_query_file($r56_codrel = null, $r56_instit = null, $campos = "*", $ordem = null, $dbwhere = "")
    {
        $sql = "select ";
        $sql .= $campos;
        $sql .= " from convenio ";
        $sql2 = "";
        if ($dbwhere=="") {
            if ($r56_codrel!=null) {
                $sql2 .= " where convenio.r56_codrel = '$r56_codrel' ";
            }
            if ($r56_instit!=null) {
                if ($sql2!="") {
                     $sql2 .= " and ";
                } else {
                    $sql2 .= " where ";
                }
                $sql2 .= " convenio.r56_instit = $r56_instit ";
            }
        } elseif ($dbwhere != "") {
            $sql2 = " where $dbwhere";
        }
        $sql .= $sql2;
        if ($ordem != null) {
            $sql .= " order by {$ordem}";
        }
        return $sql;
    }

    public function sql_query_relac($r56_codrel = null, $r56_instit = null, $campos = "*", $ordem = null, $dbwhere = "")
{
    $sql = "select ";
    $sql .= $campos;
    $sql .= " from convenio ";
    $sql .= "      inner join relac on relac.r55_codeve = convenio.r56_codrel and relac.r55_instit = convenio.r56_instit";
    $sql2 = "";

    if ($dbwhere == "") {
        $dbwhere = false;

        if ($r56_codrel != null) {
            $sql2 .= " where trim(convenio.r56_codrel) = '" . trim($r56_codrel) . "' ";
            $dbwhere = true;
        }

        if ($r56_instit != null) {
            if ($dbwhere) {
                $sql2 .= " and "; 
            } else {
                $sql2 .= " where "; 
            }
            $sql2 .= " convenio.r56_instit = " . $r56_instit . " ";
        }

    } elseif ($dbwhere != "") {
        $sql2 = " where " . $dbwhere;
    }

    $sql .= $sql2;
    if ($ordem != null) {
        $sql .= " order by " . $ordem;
    }

    return $sql;
}
}
