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

namespace App\Domain\Educacao\Escola\Controllers\Relatorios;

use App\Domain\Core\Base\Http\Response\DBJsonResponse;
use App\Domain\Educacao\Escola\Models\Disciplina;
use App\Domain\Educacao\Escola\Relatorios\HistoricoEscolarBncc;
use App\Domain\Educacao\Escola\Services\HistoricoService;
use App\Http\Controllers\Controller;
use DBException;
use ECidade\Pdf\Pdf;
use Illuminate\Http\Request;
use Aluno;
use Escola;
use Instituicao;
use ParameterException;
use RelatorioHistoricoEscolarRetrato;
use stdClass;

class Historico extends Controller
{
    /**
     * @throws DBException
     * @throws ParameterException
     */
    public function emitirBncc(Request $request)
    {
        $parametros = $request->get('0');
        $service = new HistoricoService($parametros);
        $response = $service->criaPdf();

        return new DBJsonResponse($response, 'Histórico gerado com sucesso!', 200);
    }
}
