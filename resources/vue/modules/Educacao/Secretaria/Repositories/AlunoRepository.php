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

namespace App\Domain\Educacao\Secretaria\Repositories;

use App\Domain\Core\Base\Repository\BaseRepository;
use App\Domain\Educacao\Escola\Enums\NacionalidadeEnum;
use App\Domain\Educacao\Escola\Models\Aluno;

class AlunoRepository extends BaseRepository
{
    protected $modelClass = Aluno::class;
    public function getAlunosEstrangeiros($escola, $campos, $ano)
    {
        return $this->newQuery()
            ->select($campos)
            ->join('matricula', 'ed60_i_aluno', '=', 'ed47_i_codigo')
            ->join('turma', 'ed60_i_turma', '=', 'ed57_i_codigo')
            ->join('regencia', 'ed59_i_turma', '=', 'ed57_i_codigo')
            ->join('serie', 'ed59_i_serie', '=', 'ed11_i_codigo')
            ->join('calendario', 'ed57_i_calendario', '=', 'ed52_i_codigo')
            ->join('pais', 'ed47_i_pais', '=', 'ed228_i_codigo')
            ->where('ed47_i_nacion', NacionalidadeEnum::ESTRANGEIRA)
            ->where('ed57_i_escola', $escola)
            ->where('ed52_i_ano', $ano)
            ->distinct()
            ->get();
    }
}
