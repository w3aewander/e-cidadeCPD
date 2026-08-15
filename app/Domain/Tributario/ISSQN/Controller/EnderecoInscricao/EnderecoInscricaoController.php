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

namespace App\Domain\Tributario\ISSQN\Controller\EnderecoInscricao;

use App\Domain\Core\Base\Http\Response\DBJsonResponse;
use App\Domain\Tributario\ISSQN\DTO\Redesim\EnderecoInscricao\GetBairrosDTO;
use App\Domain\Tributario\ISSQN\DTO\Redesim\EnderecoInscricao\SaveEnderecoDTO;
use App\Domain\Tributario\ISSQN\DTO\Redesim\EnderecoInscricao\UpdateEnderecoDTO;
use App\Domain\Tributario\ISSQN\Requests\EnderecoInscricao\EnderecoInscricaoRequest;
use App\Domain\Tributario\ISSQN\Services\EnderecoInscricao\CadastroEnderecoService;
use App\Http\Controllers\Controller;
use BusinessException;
use Exception;
use Log;
use Illuminate\Http\Request;

class EnderecoInscricaoController extends Controller
{
    public function getBairros(Request $request, CadastroEnderecoService $service)
    {
        $this->validate($request, [
            'sequencial' => 'numeric',
            'nome' => 'string'
        ]);

        try {
            $params = GetBairrosDTO::fromArray($request->all());
            $bairros = $service->getByParams($params);
            return new DBJsonResponse($bairros);
        } catch (BusinessException $e) {
            return new DBJsonResponse(null, $e->getMessage(), 400);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return new DBJsonResponse(null, 'Erro interno ao buscar bairros.', 500);
        }
    }

    public function getEndereco(Request $request, CadastroEnderecoService $service)
    {
        $this->validate($request, [
            'sequencial' => 'numeric',
            'nome' => 'string'
        ]);

        try {
            $cadastro = $service->getEndereco($request->sequencial, $request->nome);
            return new DBJsonResponse($cadastro);
        } catch (BusinessException $e) {
            return new DBJsonResponse(null, $e->getMessage(), 400);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return new DBJsonResponse(null, 'Erro interno ao buscar cadastro.', 500);
        }
    }

    public function saveEndereco(EnderecoInscricaoRequest $request, CadastroEnderecoService $service)
    {
        try {
            $data = SaveEnderecoDTO::fromArray($request->all());
            $response = $service->saveEndereco($data);
            return new DBJsonResponse($response);
        } catch (BusinessException $e) {
            return new DBJsonResponse(null, $e->getMessage(), 400);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return new DBJsonResponse(null, 'Erro Interno ao salvar endereço.', 500);
        }
    }

    public function updateEndereco(EnderecoInscricaoRequest $request, CadastroEnderecoService $service)
    {
        try {
            $data = UpdateEnderecoDTO::fromArray($request->all());
            $service->updateEndereco($data);
        } catch (BusinessException $e) {
            return new DBJsonResponse(null, $e->getMessage(), 400);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return new DBJsonResponse(null, 'Erro Interno ao alterar endereço.', 500);
        }
    }

    public function deleteEndereco(Request $request, CadastroEnderecoService $service)
    {
        $this->validate($request, [
            'inscricao' => 'numeric'
        ]);

        try {
            $service->deleteEndereco($request->inscricao);
        } catch (BusinessException $e) {
            return new DBJsonResponse(null, $e->getMessage(), 400);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return new DBJsonResponse(null, 'Erro Interno ao excluir endereço.', 500);
        }
    }
}
