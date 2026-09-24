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

namespace App\Domain\Educacao\TransporteEscolar\Services\Sete;

use App\Domain\Educacao\TransporteEscolar\Repositories\Sete\ExportacaoAlunosRepository;

class ExportacaoAlunosService
{
    protected $repository;

    public function __construct()
    {
        $this->repository = new ExportacaoAlunosRepository();
    }

    public function getDados($dados)
    {
        return $this->repository->getDadosAlunos($dados)->toArray();
    }
    public function getDadosInconsistentes($dados)
    {
        return $this->repository->getDadosAlunosInconsistentes($dados)->toArray();
    }
    public function verificarInconsistencias($dados)
    {
        $numeroInconsistencias = $this->repository->getDadosAlunosInconsistentes($dados, true)->toArray();
        if ($numeroInconsistencias[0]['numero_inconsistencias'] > 0) {
            return true;
        }
        return false;
    }
}
