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
 * Classe modelo de relatório da raça e cor dos alunos matriculados
 * @package    Educacao
 * @subpackage Relatorio
 * @version    1.0
 */

class RelatorioAlunosRacaCor extends EstatisticaAlunosRacaCor {

  private $lPercentual = true;

  const COR_ENSINO = '180';
  const COR_ETAPA  = '225';
  const COR_TURMA  = '255';

  private $oPdf;

  private $iHeight;

  public function __construct( Calendario $oCalendario, array $aEtapa, Escola $oEscola, $oPdf = null) {
 

  parent::__construct( $oCalendario, $aEtapa, $oEscola );
  $this->getEstatisticaAlunosMatriculados();
  $this->getPercentual();

  global $head1;
  global $head2;
  global $head3;
  global $head4;

  $oEtapa = EtapaRepository::getEtapaByCodigo($aEtapa[0]);
  $sDescricaoEtapa = $oEtapa->getNome();

  if ( count($aEtapa) > 1 ) {
    $sDescricaoEtapa = "TODOS";
  }

  // Cria um novo PDF ou usa um existente
 
  $this->oPdf = $oPdf ?: new PDF();
  if ($oPdf === null){
    $this->oPdf->Open();
  }
  $this->oPdf->setfont('arial', '', 8);
  $this->oPdf->AliasNbPages();
  $this->oPdf->SetAutoPageBreak(true, 20);

  $head1 = 'RELATÓRIO DE RAÇA/COR DE ALUNOS MATRICULADOS';
  $head2 = "Calendário: {$oCalendario->getDescricao()}";
  $head3 = "Etapa : {$sDescricaoEtapa} ";
  $head4 = "Filtro: Raça/Cor" ;

  $this->oPdf->setfillcolor(223);
  $this->oPdf->SetMargins(8, 8, 8);
  $this->oPdf->addpage('P');
}
    /**
   * Método responsável por montar a linhas do pdf de acordo com as Turmas de cada Etapa por Ensino
   */
  public function imprimir() {

    $this->oPdf->setX(8);

    /**
     * Percorre os Ensinos montando as linhas por cada Ensino
     */
    foreach ( $this->aEnsino as $oEnsino ) {

      $this->oPdf->setfillcolor(self::COR_ENSINO);
      $this->oPdf->setfont('arial', 'b', 8);
      $this->oPdf->cell(138.5, 4, "{$oEnsino->sNome}", 1, 1, "L", 1);

      /**
       * Percorre as etapas de cada ensino montandos as linhas
       */
      foreach ( $oEnsino->aEtapa as $oEtapa ) {

        $this->oPdf->setfillcolor(self::COR_ETAPA);
        $this->oPdf->setfont('arial', 'b', 8);
        $this->oPdf->cell(58,   4, "Etapa: {$oEtapa->sNome}", 1, 0, "L", 1);
        $this->oPdf->cell(12,   4, "Branca",                  1, 0, "L", 1);
        $this->oPdf->cell(9.5,  4, "Preta",                   1, 0, "L", 1);
        $this->oPdf->cell(10,   4, "Parda",                   1, 0, "L", 1);
        $this->oPdf->cell(13.5, 4, "Amarela",                 1, 0, "L", 1);
        $this->oPdf->cell(14,   4, "Indígena",                  1, 0, "L", 1);
        $this->oPdf->cell(21.5, 4, "Não Declarada",          1, 1, "L", 1);


        /**
         * Monta as linhas contendo as informações de matriculas por turma
         */
        foreach ( $oEtapa->aTurmas as $oTurma ) {
          $sTurma = substr("{$oTurma->sTurma} - {$oTurma->sTurno}", 0, 35);
          $this->oPdf->setfillcolor(self::COR_TURMA);
          $this->oPdf->setfont('arial', '', 8);
          $this->oPdf->cell(58,   4, "{$sTurma}",                   1, 0, "L", 1);
          $this->oPdf->cell(12,   4, "$oTurma->raca_branca",        1, 0, "C", 1);
          $this->oPdf->cell(9.5,  4, "$oTurma->raca_preta",         1, 0, "C", 1);
          $this->oPdf->cell(10,   4, "$oTurma->raca_parda",         1, 0, "C", 1);
          $this->oPdf->cell(13.5, 4, "$oTurma->raca_amarela",       1, 0, "C", 1);
          $this->oPdf->cell(14,   4, "$oTurma->raca_indigena",      1, 0, "C", 1);
          $this->oPdf->cell(21.5, 4, "$oTurma->raca_naodeclarada",  1, 1, "C", 1);

        }

        /**
         * Monta os totais das Etapas
         */
        $this->oPdf->setfillcolor(self::COR_ETAPA);
        $this->oPdf->setfont('arial', 'b', 8);
        $this->oPdf->cell(58,   4, "Total da Etapa: {$oEtapa->sNome  }", 1, 0, "R", 1);
        $this->oPdf->cell(12,   4, "{$oEtapa->iTotalRaca_branca      }",    1, 0, "C", 1);
        $this->oPdf->cell(9.5,  4, "{$oEtapa->iTotalRaca_preta       }",    1, 0, "C", 1);
        $this->oPdf->cell(10,   4, "{$oEtapa->iTotalRaca_parda       }",    1, 0, "C", 1);
        $this->oPdf->cell(13.5, 4, "{$oEtapa->iTotalRaca_amarela     }",    1, 0, "C", 1);
        $this->oPdf->cell(14,   4, "{$oEtapa->iTotalRaca_indigena    }",    1, 0, "C", 1);
        $this->oPdf->cell(21.5, 4, "{$oEtapa->iTotalRaca_naodeclarada}",    1, 1, "C", 1);


        if ($this->lPercentual) {


          $this->oPdf->cell(58,   4, 'Percentuais: ',                       1, 0, "R", 1);
          $this->oPdf->cell(12,   4, "{$oEtapa->percentual_branca      }%", 1, 0, "C", 1);
          $this->oPdf->cell(9.5,  4, "{$oEtapa->percentual_preta       }%", 1, 0, "C", 1);
          $this->oPdf->cell(10,   4, "{$oEtapa->percentual_parda       }%", 1, 0, "C", 1);
          $this->oPdf->cell(13.5, 4, "{$oEtapa->percentual_amarela     }%", 1, 0, "C", 1);
          $this->oPdf->cell(14,   4, "{$oEtapa->percentual_indigena    }%", 1, 0, "C", 1);
          $this->oPdf->cell(21.5, 4, "{$oEtapa->percentual_naodeclarada}%", 1, 1, "C", 1);
        }

      }

      /**
       * Monta os totais dos Ensinos
       */
      $this->oPdf->setfillcolor(self::COR_ENSINO);
      $this->oPdf->setfont('arial', 'b', 8);

      $totalEnsino = "Total {$oEnsino->sNome}";
      $this->ajustaTamanhoFonte($totalEnsino, 58, 8);
      $this->oPdf->cell(58, 4, $totalEnsino, 1, 0, "R", 1);

      $this->oPdf->setfont('arial', 'b', 8);
      $this->oPdf->cell(12,   4, "{$oEnsino->iTotalRaca_branca      }",         1, 0, "C", 1);
      $this->oPdf->cell(9.5,  4, "{$oEnsino->iTotalRaca_preta       }",         1, 0, "C", 1);
      $this->oPdf->cell(10,  4, "{$oEnsino->iTotalRaca_parda       }",         1, 0, "C", 1);
      $this->oPdf->cell(13.5,   4, "{$oEnsino->iTotalRaca_amarela     }",         1, 0, "C", 1);
      $this->oPdf->cell(14,   4, "{$oEnsino->iTotalRaca_indigena    }",         1, 0, "C", 1);
      $this->oPdf->cell(21.5,   4, "{$oEnsino->iTotalRaca_naodeclarada}",         1, 1, "C", 1);

      if ($this->lPercentual) {
        $this->oPdf->cell(58,   4, 'Percentuais: ',                        1, 0, "R", 1);
        $this->oPdf->cell(12,  4, "{$oEnsino->percentual_branca      }%", 1, 0, "C", 1);
        $this->oPdf->cell(9.5,  4, "{$oEnsino->percentual_preta       }%", 1, 0, "C", 1);
        $this->oPdf->cell(10,   4, "{$oEnsino->percentual_parda       }%", 1, 0, "C", 1);
        $this->oPdf->cell(13.5,   4, "{$oEnsino->percentual_amarela     }%", 1, 0, "C", 1);
        $this->oPdf->cell(14,   4, "{$oEnsino->percentual_indigena    }%", 1, 0, "C", 1);
        $this->oPdf->cell(21.5,  4, "{$oEnsino->percentual_naodeclarada}%", 1, 1, "C", 1);

      }

    }

    /**
     * Monta a linha que imprime o total geral
     */
    $oTotalGeral = $this->getTotalGeral();

    $this->oPdf->ln();
    $this->oPdf->setfillcolor(self::COR_ENSINO);
    $this->oPdf->setfont('arial', 'b', 8);

    $this->oPdf->cell(138.5, 4, "TOTAL GERAL", 1, 1, "L", 1);

    $this->oPdf->cell(58,   4, "",                  1, 0, "L", 1);
    $this->oPdf->cell(12,   4, "Branca",            1, 0, "L", 1);
    $this->oPdf->cell(9.5,  4, "Preta",             1, 0, "L", 1);
    $this->oPdf->cell(10,  4, "Parda",             1, 0, "L", 1);
    $this->oPdf->cell(13.5,   4, "Amarela",           1, 0, "L", 1);
    $this->oPdf->cell(14,   4, "Indigena",          1, 0, "L", 1);
    $this->oPdf->cell(21.5,   4, "N. Declarada",      1, 1, "L", 1);


    $this->oPdf->setfillcolor(self::COR_TURMA);
    $this->oPdf->setfont('arial', 'b', 8);
    $this->oPdf->cell(58,   4, "Somas: ",                                 1, 0, "R", 1);
    $this->oPdf->cell(12,   4, "{$oTotalGeral->iTotalRaca_branca      }", 1, 0, "C", 1);
    $this->oPdf->cell(9.5,  4, "{$oTotalGeral->iTotalRaca_preta       }", 1, 0, "C", 1);
    $this->oPdf->cell(10,  4, "{$oTotalGeral->iTotalRaca_parda       }", 1, 0, "C", 1);
    $this->oPdf->cell(13.5,   4, "{$oTotalGeral->iTotalRaca_amarela     }", 1, 0, "C", 1);
    $this->oPdf->cell(14,   4, "{$oTotalGeral->iTotalRaca_indigena    }", 1, 0, "C", 1);
    $this->oPdf->cell(21.5,   4, "{$oTotalGeral->iTotalRaca_naodeclarada}", 1, 1, "C", 1);


    $this->oPdf->cell(58,   4, 'Percentuais: ',                                1, 0, "R", 1);
    $this->oPdf->cell(12,  4, "{$oTotalGeral->percentual_branca      }%", 1, 0, "C", 1);
    $this->oPdf->cell(9.5,  4, "{$oTotalGeral->percentual_preta      }%", 1, 0, "C", 1);
    $this->oPdf->cell(10,   4, "{$oTotalGeral->percentual_parda    }%", 1, 0, "C", 1);
    $this->oPdf->cell(13.5,   4, "{$oTotalGeral->percentual_amarela          }%", 1, 0, "C", 1);
    $this->oPdf->cell(14,   4, "{$oTotalGeral->percentual_indigena     }%", 1, 0, "C", 1);
    $this->oPdf->cell(21.5,  4, "{$oTotalGeral->percentual_naodeclarada          }%", 1, 1, "C", 1);

    $this->oPdf->Output();    

  }

  public function ajustaTamanhoFonte($content, $largura, $tamanhoFonteOriginal)
  {
      $content = "${content}   ";
      $tamanhoString = $this->oPdf->GetStringWidth($content);

      if ($tamanhoString > $largura) {
          $tamanhoFonte = $tamanhoFonteOriginal * $largura / $tamanhoString;
          $this->oPdf->SetFontSize($tamanhoFonte);
      }
  }
}
