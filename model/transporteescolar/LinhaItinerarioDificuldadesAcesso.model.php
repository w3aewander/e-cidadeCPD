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

class LinhaItinerarioDificuldadesAcesso
{
   private $dao;
   private $linhaItinerario;
   private $dificuldadesAcesso;
   private $itinerario;

   public function __construct()
   {
     $this->dao = new cl_itinerariodificuldadesacesso;
   }
   
   /**
    * @param stdClass dificuldadesAcessos
    */
   public function setDificuldadesAcesso($dificuldadesAcesso)
   {
     $this->dificuldadesAcesso = $dificuldadesAcesso;
   }

   /**
    * @param stdClass dificuldadesAcessos
    */
   public function getDificuldadesAcesso()
   {
      return $this->dificuldadesAcesso;
   }

   public function setItinerario($itinerario)
   {
      $this->itinerario = $itinerario;
   }

   public function getItinerario()
   {
      return $this->itinerario;
   }   

   public function setLinhaItinerario(LinhaItinerario $linhaItinerario)
   {
      $this->linhaItinerario = $linhaItinerario;
   }

   public function getLinhaItinerario()
   {
      return $this->linhaItinerario;
   }

   public function salvar()
   {

      $where = " tre16_linhatransporteitinerario = ".$this->getLinhaItinerario()->getCodigo();
      $sqlVerificacaoItinerario = $this->dao->sql_query_file(null,"",null,$where);
      $rsVerificacaoItinerario = db_query($sqlVerificacaoItinerario);
      if(pg_num_rows($rsVerificacaoItinerario) > 0){         
         throw new Exception("Informações já cadastradas para o itinerário");
      } else {
         $this->dao = new cl_itinerariodificuldadesacesso;
         $this->dao->tre16_linhatransporteitinerario = $this->getLinhaItinerario()->getCodigo();
         $this->dao->tre16_porteira = $this->getDificuldadesAcesso()->porteira;
         $this->dao->tre16_mataburro = $this->getDificuldadesAcesso()->mataburro;
         $this->dao->tre16_colchete = $this->getDificuldadesAcesso()->colchete;
         $this->dao->tre16_atoleiro = $this->getDificuldadesAcesso()->atoleiro;
         $this->dao->tre16_ponterustica = $this->getDificuldadesAcesso()->ponterustica;
         $this->dao->incluir(null);
         if($this->dao->erro_status == '0'){
             throw new Exception($this->dao->erro_msg);
         }
      }
   }

   public function excluir()
   {
      $itinerario = $this->getItinerario();
      $where = "tre16_linhatransporteitinerario = {$itinerario}";
      $this->dao->excluir(null,$where);
      if($this->dao->erro_status == '0'){
         throw new Exception($this->dao->erro_msg);
      }
   }
}