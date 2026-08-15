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

namespace App\Domain\Educacao\TransporteEscolar\Controllers\Sete;

use App\Http\Controllers\Controller;
use App\Domain\Core\Base\Http\Response\DBJsonResponse;
use Illuminate\Http\Request;
use App\Domain\Educacao\TransporteEscolar\Services\Sete\ExportacaoAlunosService;
use App\Domain\Educacao\TransporteEscolar\Controllers\Relatorios\Sete\ExportacaoAlunosCsv;
use App\Domain\Educacao\TransporteEscolar\Controllers\Relatorios\Sete\RelatorioInconsistenciasAlunosPdf;
use App\Domain\Educacao\TransporteEscolar\Requests\ExportacaoAlunosRequest;

class ExportacaoAlunosController extends Controller
{
    protected $service;

    public function __construct()
    {
        $this->service = new ExportacaoAlunosService();
    }
    public function exportar(Request $request)
    {
        ExportacaoAlunosRequest::validaRequestExportacao($request->all());
        $alunos = $this->service->getDados($request->all());
        $csv = new ExportacaoAlunosCsv($alunos);
        return new DBJsonResponse($csv->emitir(), 'Dados exportados com sucesso!');
    }

    public function gerarRelatorioInconsistencias(Request $request)
    {
        ExportacaoAlunosRequest::validaRequestExportacao($request->all());
        $inconsistencias = $this->service->getDadosInconsistentes($request->all());
        $pdf = new RelatorioInconsistenciasAlunosPdf($inconsistencias);
        return new DBJsonResponse($pdf->emitirPdf(), 'Relatório de inconsistência exportado com sucesso!');
    }

    public function verificarInconsistencias(Request $request)
    {
        ExportacaoAlunosRequest::validaRequestExportacao($request->all());
        return new DBJsonResponse(
            $this->service->verificarInconsistencias($request->all()),
            'Inconsistência verificada com sucesso!'
        );
    }
}
