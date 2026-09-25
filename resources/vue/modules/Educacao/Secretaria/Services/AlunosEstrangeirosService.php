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

namespace App\Domain\Educacao\Secretaria\Services;

use App\Domain\Educacao\Escola\Enums\NacionalidadeEnum;
use App\Domain\Educacao\Escola\Models\Aluno;
use App\Domain\Educacao\Escola\Models\Escola;
use App\Domain\Educacao\Secretaria\Repositories\AlunoRepository;
use App\Domain\Educacao\Secretaria\Resources\AlunosEstrangeirosResource;

class AlunosEstrangeirosService
{
    private $repository;
    public function __construct(AlunoRepository $repository)
    {
        $this->repository = $repository;
    }
    public function getAlunosEstrangeiros($escola, $ano)
    {
        $campos = [
            'ed47_i_codigo', 'ed47_v_nome', 'ed47_v_sexo', 'ed47_d_nasc', 'ed60_d_datamatricula', 'ed52_i_ano',
            'ed60_d_datasaida', 'ed60_c_situacao', 'ed11_c_descr', 'ed11_i_codigo', 'ed52_i_codigo', 'ed57_i_escola',
            'ed228_c_descr', 'ed228_c_abrev'
        ];
        return $this->repository->getAlunosEstrangeiros($escola, $campos, $ano);
    }

    public function getDadosRelatorio($escolas, $ano)
    {
        $retorno = [];
        foreach ($escolas as $escola) {
            $retorno[] = (object) [
                'escola' => Escola::find($escola)->ed18_c_nome,
                'alunosEstrangeiros' => $this->getAlunosEstrangeiros($escola, $ano)->map(function ($aluno) {
                    return AlunosEstrangeirosResource::toObjectToRelatorio($aluno);
                })->unique(),
                'ano' => $ano
            ];
        }
        return $retorno;
    }
}
