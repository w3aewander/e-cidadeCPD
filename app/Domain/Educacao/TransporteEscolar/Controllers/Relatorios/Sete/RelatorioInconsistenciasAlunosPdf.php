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

namespace App\Domain\Educacao\TransporteEscolar\Controllers\Relatorios\Sete;

use ECidade\Pdf\Pdf;

class RelatorioInconsistenciasAlunosPdf extends Pdf
{
    private $inconsistencias = [];

    public function __construct(array $inconsistencias)
    {
        parent::__construct('P');
        $this->inconsistencias = $inconsistencias;
    }

    public function emitirPdf()
    {
        $fileName = sprintf('tmp/inconsistenciasalunos-%s.pdf', time());
        $this->imprimir();
        $this->output('F', $fileName);

        return [
            'name' => 'Relatório com as inconsistências dos alunos para o SETE ',
            'path' => $fileName,
            'url' => ECIDADE_REQUEST_PATH . $fileName
        ];
    }

    private function headers()
    {
        $this->addTitulo("Inconsistências dos alunos para o SETE");
        $this->init(false);
    }

    private function imprimir()
    {
        $this->headers();
        $this->setFont('Times', 'b', '11');
        $this->addPage();
        $this->imprimirDados();
    }

    private function imprimirDados()
    {
        foreach ($this->inconsistencias as $inconsistencia) {
            if ($this->Gety() > $this->getH() - 35) {
                $this->addPage();
            }

            $bullet = chr(149);
            $this->cell(25, 5, "Cod.Aluno", 1, 0, "C", 1);
            $this->cell(135, 5, "Nome", 1, 1, "C", 1);
            $this->cell(25, 5, $inconsistencia['ed47_i_codigo'], 1, 0, "C", 0);
            $this->cell(135, 5, $inconsistencia['ed47_v_nome'], 1, 1, "C", 1);
            $this->ln();
            $this->cell(160, 5, "Inconsistências:", 0, 1, "C", 0);
            $this->setFont('Times', '', '11');
            
            if (empty($inconsistencia['ed47_d_nasc'])) {
                $this->cell(160, 5, $bullet." Data de nascimento não informada.", 0, 1, "C", 0);
            }
            
            if (empty($inconsistencia['ed47_v_sexo'])) {
                $this->cell(160, 5, $bullet." Sexo não informado.", 0, 1, "C", 0);
            }

            if (empty($inconsistencia['ed47_c_raca'])) {
                $this->cell(160, 5, $bullet." Cor não informada.", 0, 1, "C", 0);
            }

            if (empty($inconsistencia['ed47_c_zona'])) {
                $this->cell(160, 5, $bullet." Localização não informada.", 0, 1, "C", 0);
            }
            
            if (empty($inconsistencia['ed10_tipo'])) {
                $this->cell(160, 5, $bullet." Nível de ensino não informado.", 0, 1, "C", 0);
            }
            
            if (empty($inconsistencia['ed57_i_turno'])) {
                $this->cell(160, 5, $bullet." Turno não informado.", 0, 1, "C", 0);
            }
            $this->ln();
            $this->setFont('Times', 'b', '11');
        }
    }
}
