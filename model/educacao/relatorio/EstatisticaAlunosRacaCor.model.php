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

/**
 * Classe modelo para estatística da raça e cor dos alunos matriculados
 * @package    Educacao
 * @subpackage Relatorio
 * @version    1.0
 */

class EstatisticaAlunosRacaCor {

  protected $oCalendario;
  protected $aEtapa;
  protected $oEscola;
  protected $aDadosRacaCor = array();
  protected $aEnsino = array();


  protected function __construct(Calendario $oCalendario, $aEtapa, Escola $oEscola) {
    $this->oCalendario = $oCalendario;
    $this->aEtapa      = $aEtapa;
    $this->oEscola     = $oEscola;
  }

  private function getTurmas(Etapa $oEtapa) {

    $aTurmaCalendarioEscola = TurmaRepository::getTurmaPorCalendarioEscola($this->oEscola, $this->oCalendario);
    $aTurmasSelecionadas    = array();

    foreach ( $aTurmaCalendarioEscola as $oTurma ) {

      foreach ($oTurma->getEtapas() as $oEtapaTurma) {

        if ( count($oTurma->getAlunosMatriculadosNaTurmaPorSerie($oEtapaTurma->getEtapa())) == 0 ) {
          continue;
        }

        if ( $oEtapaTurma->getEtapa()->getCodigo() == $oEtapa->getCodigo() ) {

          $aTurmasSelecionadas[] = $this->getCalculosTurma( $oTurma, $oEtapa->getCodigo() );
        }
      }
    }
    ksort($aTurmasSelecionadas);

    return $aTurmasSelecionadas;
  }

  private function getCalculosTurma(Turma $oTurma, $iCodigoEtapa) {

    $oEtapa       = EtapaRepository::getEtapaByCodigo($iCodigoEtapa);
    $oDadosTurma                     = new stdClass();
    $oDadosTurma->sTurma             = $oTurma->getDescricao();
    $oDadosTurma->sTurno             = $oTurma->getTurno()->getDescricao();
    $oDadosTurma->iCodigo            = $oTurma->getCodigo();
    $oDadosTurma->iTotalAlunos       = "0";
    $oDadosTurma->raca_branca        = "0";
    $oDadosTurma->raca_preta         = "0";
    $oDadosTurma->raca_parda         = "0";
    $oDadosTurma->raca_amarela       = "0";
    $oDadosTurma->raca_indigena      = "0";
    $oDadosTurma->raca_naodeclarada  = "0";
    foreach ($oTurma->getAlunosMatriculadosNaTurmaPorSerie($oEtapa) as $oMatricula) {

      $oDadosTurma->iTotalAlunos++;
      $oAluno = $oMatricula->getAluno();
      $raca = strtoupper($oAluno->getRaca());
      


      switch ($raca) {
          case 'BRANCA':
              $oDadosTurma->raca_branca++;
              break;
          case 'PRETA':
              $oDadosTurma->raca_preta++;
              break;
          case 'PARDA':
              $oDadosTurma->raca_parda++;
              break;
          case 'AMARELA':
              $oDadosTurma->raca_amarela++;
              break;
          case 'INDIGENA':
              $oDadosTurma->raca_indigena++;
              break;
          default:
              $oDadosTurma->raca_naodeclarada++;
              break;
        }
      }


      return($oDadosTurma);
  }


  protected function getEstatisticaAlunosMatriculados() {

    $aTurmasPercorridas = array();

    foreach ($this->aEtapa as $iEtapa) {
      
        $oEtapa        = EtapaRepository::getEtapaByCodigo($iEtapa);

        $iCodigoEnsino = $oEtapa->getEnsino()->getCodigo();

        if (!array_key_exists($iCodigoEnsino, $this->aEnsino)) {
            $oEnsino                             = new stdClass();
            $oEnsino->iCodigo                    = $oEtapa->getEnsino()->getCodigo();
            $oEnsino->sNome                      = $oEtapa->getEnsino()->getNome();
            $oEnsino->aEtapa                     = array();
            $oEnsino->iTotalRaca_branca          = 0;
            $oEnsino->iTotalRaca_preta           = 0;
            $oEnsino->iTotalRaca_parda           = 0;
            $oEnsino->iTotalRaca_amarela         = 0;
            $oEnsino->iTotalRaca_indigena        = 0;
            $oEnsino->iTotalraca_naodeclarada    = 0;
            $this->aEnsino[$iCodigoEnsino]       = $oEnsino;
        }

        $oEtapaDados                          = new stdClass();
        $oEtapaDados->iCodigo                 = $oEtapa->getCodigo();
        $oEtapaDados->sNome                   = $oEtapa->getNome();
        $oEtapaDados->aTurmas                 = array();
        $oEtapaDados->iTotalRaca_branca       = 0;
        $oEtapaDados->iTotalRaca_preta        = 0;
        $oEtapaDados->iTotalRaca_parda        = 0;
        $oEtapaDados->iTotalRaca_amarela      = 0;
        $oEtapaDados->iTotalRaca_indigena     = 0;
        $oEtapaDados->iTotalraca_naodeclarada = 0;

        $this->aEnsino[$iCodigoEnsino]->aEtapa[$oEtapa->getOrdem()] = $oEtapaDados;

        ksort($this->aEnsino[$iCodigoEnsino]->aEtapa);

        $this->aEnsino[$iCodigoEnsino]->aEtapa[$oEtapa->getOrdem()]->aTurmas = $this->getTurmas($oEtapa);

        if ( count($this->aEnsino[$iCodigoEnsino]->aEtapa[$oEtapa->getOrdem()]->aTurmas) == 0) {

          unset($this->aEnsino[$iCodigoEnsino]->aEtapa[$oEtapa->getOrdem()]);
          if ( count($this->aEnsino[$iCodigoEnsino]->aEtapa) == 0) {
            unset($this->aEnsino[$iCodigoEnsino]);
          }
          continue;
        }

        foreach ($this->aEnsino[$iCodigoEnsino]->aEtapa[$oEtapa->getOrdem()]->aTurmas as $oTurma) {
          $oEtapaDados->iTotalAlunos            += $oTurma->iTotalAlunos;
          $oEtapaDados->iTotalRaca_branca       += $oTurma->raca_branca;
          $oEtapaDados->iTotalRaca_preta        += $oTurma->raca_preta;
          $oEtapaDados->iTotalRaca_parda        += $oTurma->raca_parda;
          $oEtapaDados->iTotalRaca_amarela      += $oTurma->raca_amarela;
          $oEtapaDados->iTotalRaca_indigena     += $oTurma->raca_indigena;
          $oEtapaDados->iTotalRaca_naodeclarada += $oTurma->raca_naodeclarada;
            
        }
    }

    foreach ($this->aEnsino as $iCodigoEnsino => $oEnsino) {
        foreach ($oEnsino->aEtapa as $oEtapa) {
            $oEnsino->iTotalAlunos            += $oEtapa->iTotalAlunos;
            $oEnsino->iTotalRaca_branca       += $oEtapa->iTotalRaca_branca;
            $oEnsino->iTotalRaca_preta        += $oEtapa->iTotalRaca_preta;
            $oEnsino->iTotalRaca_parda        += $oEtapa->iTotalRaca_parda;
            $oEnsino->iTotalRaca_amarela      += $oEtapa->iTotalRaca_amarela;
            $oEnsino->iTotalRaca_indigena     += $oEtapa->iTotalRaca_indigena;
            $oEnsino->iTotalRaca_naodeclarada += $oEtapa->iTotalRaca_naodeclarada;
        }
    }

    return $this->aEnsino;
  }

  protected function getPercentual() {

    foreach ($this->aEnsino as $iCodigoEnsino => $oEnsino) {
      $oEnsino->percentual_branca = 0;
      $oEnsino->percentual_preta = 0;
      $oEnsino->percentual_parda = 0;
      $oEnsino->percentual_amarela = 0;
      $oEnsino->percentual_indigena = 0;
      $oEnsino->percentual_naodeclarada = 0;

      if ($oEnsino->iTotalAlunos != 0) {
        $oEnsino->percentual_branca = round(($oEnsino->iTotalRaca_branca / $oEnsino->iTotalAlunos) * 100, 2);
        $oEnsino->percentual_preta = round(($oEnsino->iTotalRaca_preta / $oEnsino->iTotalAlunos) * 100, 2);
        $oEnsino->percentual_parda = round(($oEnsino->iTotalRaca_parda / $oEnsino->iTotalAlunos) * 100, 2);
        $oEnsino->percentual_amarela = round(($oEnsino->iTotalRaca_amarela / $oEnsino->iTotalAlunos) * 100, 2);
        $oEnsino->percentual_indigena = round(($oEnsino->iTotalRaca_indigena / $oEnsino->iTotalAlunos) * 100, 2);
        $oEnsino->percentual_naodeclarada = round(($oEnsino->iTotalRaca_naodeclarada / $oEnsino->iTotalAlunos) * 100, 2);
      }


      foreach ($oEnsino->aEtapa as $oEtapa) {
        $oEtapa->percentual_branca = 0;
        $oEtapa->percentual_preta = 0;
        $oEtapa->percentual_parda = 0;
        $oEtapa->percentual_amarela = 0;
        $oEtapa->percentual_indigena = 0;
        $oEtapa->percentual_naodeclarada = 0;

        if ($oEtapa->iTotalAlunos != 0) {
          $oEtapa->percentual_branca = round(($oEtapa->iTotalRaca_branca / $oEtapa->iTotalAlunos) * 100, 2);
          $oEtapa->percentual_preta = round(($oEtapa->iTotalRaca_preta / $oEtapa->iTotalAlunos) * 100, 2);
          $oEtapa->percentual_parda = round(($oEtapa->iTotalRaca_parda / $oEtapa->iTotalAlunos) * 100, 2);
          $oEtapa->percentual_amarela = round(($oEtapa->iTotalRaca_amarela / $oEtapa->iTotalAlunos) * 100, 2);
          $oEtapa->percentual_indigena = round(($oEtapa->iTotalRaca_indigena / $oEtapa->iTotalAlunos) * 100, 2);
          $oEtapa->percentual_naodeclarada = round(($oEtapa->iTotalRaca_naodeclarada / $oEtapa->iTotalAlunos) * 100, 2);
        }
      }
    }
  }
  
  protected function getTotalGeral() {

    $oTotalGeral = new stdClass();
    $oTotalGeral->iTotalRaca_branca = 0;
    $oTotalGeral->iTotalRaca_preta = 0;
    $oTotalGeral->iTotalRaca_parda = 0;
    $oTotalGeral->iTotalRaca_amarela = 0;
    $oTotalGeral->iTotalRaca_indigena = 0;
    $oTotalGeral->iTotalRaca_naodeclarada = 0;

    foreach ($this->aEnsino as $oEnsino) {
        $oTotalGeral->iTotalRaca_branca += $oEnsino->iTotalRaca_branca;
        $oTotalGeral->iTotalRaca_preta += $oEnsino->iTotalRaca_preta;
        $oTotalGeral->iTotalRaca_parda += $oEnsino->iTotalRaca_parda;
        $oTotalGeral->iTotalRaca_amarela += $oEnsino->iTotalRaca_amarela;
        $oTotalGeral->iTotalRaca_indigena += $oEnsino->iTotalRaca_indigena;
        $oTotalGeral->iTotalRaca_naodeclarada += $oEnsino->iTotalRaca_naodeclarada;
    }

    $oTotalGeral->iTotalAlunos = $oTotalGeral->iTotalRaca_branca + $oTotalGeral->iTotalRaca_preta + 
                                  $oTotalGeral->iTotalRaca_parda + $oTotalGeral->iTotalRaca_amarela + 
                                  $oTotalGeral->iTotalRaca_indigena + $oTotalGeral->iTotalRaca_naodeclarada;

    $oTotalGeral->percentual_branca = round(($oTotalGeral->iTotalRaca_branca / $oTotalGeral->iTotalAlunos) * 100, 2);
    $oTotalGeral->percentual_preta = round(($oTotalGeral->iTotalRaca_preta / $oTotalGeral->iTotalAlunos) * 100, 2);
    $oTotalGeral->percentual_parda = round(($oTotalGeral->iTotalRaca_parda / $oTotalGeral->iTotalAlunos) * 100, 2);
    $oTotalGeral->percentual_amarela = round(($oTotalGeral->iTotalRaca_amarela / $oTotalGeral->iTotalAlunos) * 100, 2);
    $oTotalGeral->percentual_indigena = round(($oTotalGeral->iTotalRaca_indigena / $oTotalGeral->iTotalAlunos) * 100, 2);
    $oTotalGeral->percentual_naodeclarada = round(($oTotalGeral->iTotalRaca_naodeclarada / $oTotalGeral->iTotalAlunos) * 100, 2);

    return $oTotalGeral;
  }



  
}