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

namespace App\Domain\Educacao\Secretaria\Resources;

use App\Domain\Educacao\Escola\Models\DiarioFinal;
use App\Domain\Educacao\Escola\Models\Historico;
use Illuminate\Support\Facades\DB;

class AlunosEstrangeirosResource
{
    public static function toObjectToRelatorio($aluno)
    {
        $percentuaal = DB::table('historicomps')->whereRaw("
            ed62_i_historico in (
                select ed61_i_codigo from historico
                    where
                ed61_i_aluno = {$aluno->ed47_i_codigo}
            ) and
            ed62_i_serie = {$aluno->ed11_i_codigo}
        ")->get();

        if (count($percentuaal) > 0) {
            $percentuaal = $percentuaal[0]->ed62_percentualfrequencia;
        } else {
            $percentuaal = DiarioFinal
                ::whereRaw("ed74_i_diario in(select ed95_i_codigo from diario
                where ed95_i_aluno = {$aluno->ed47_i_codigo} and
                ed95_i_escola = {$aluno->ed57_i_escola} and
                ed95_i_calendario = {$aluno->ed52_i_codigo})")
                ->limit(1)->get();

             $percentuaal = isset($percentuaal[0]) ? $percentuaal[0]->ed74_i_percfreq : 0;
        }

        $retorno = [
            'codigo' => $aluno->ed47_i_codigo,
            'nome' => trim($aluno->ed47_v_nome),
            'sexo' => $aluno->ed47_v_sexo == 'F' ? 'FEMININO' : 'MASCULINO',
            'dataNascimento' => $aluno->ed47_d_nasc->format('d/m/Y'),
            'dataMatricula' => \DBDate::converter($aluno->ed60_d_datamatricula),
            'dataTransferencia' =>  trim($aluno->ed60_c_situacao) == 'TRANSFERIDO FORA' ||
                trim($aluno->ed60_c_situacao) == 'TRANSFERIDO REDE' ?
                    \DBDate::converter($aluno->ed60_d_datasaida) : '',
            'etapa' => trim($aluno->ed11_c_descr),
            'percentualFrequencia' => $percentuaal,
            'pais' => trim($aluno->ed228_c_descr),
            'paisAbreviatura' => trim($aluno->ed228_c_abrev)
        ];

        return (object) $retorno;
    }
}
