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

namespace App\Domain\Tributario\ISSQN\Services\EnderecoInscricao;

use App\Domain\Tributario\ISSQN\DTO\Redesim\EnderecoInscricao\GetBairrosDTO;
use App\Domain\Tributario\ISSQN\DTO\Redesim\EnderecoInscricao\SaveEnderecoDTO;
use App\Domain\Tributario\ISSQN\DTO\Redesim\EnderecoInscricao\UpdateEnderecoDTO;
use App\Domain\Tributario\ISSQN\Repository\CadastroEndereco\CadastroEnderecoRepository;
use ECidade\Tributario\Cadastro\Repository\BairroRepository;

class CadastroEnderecoService
{
    protected $cadastroEnderecoRepository;

    public function __construct(CadastroEnderecoRepository $cadastroEnderecoRepository)
    {
        $this->cadastroEnderecoRepository = $cadastroEnderecoRepository;
    }

    public function getEndereco($sequencial, $nome)
    {
        return $this->cadastroEnderecoRepository->getEndereco($sequencial, $nome);
    }

    public function getByParams(GetBairrosDTO $params)
    {
        $repository = new BairroRepository;
        return $repository->findByParams($params);
    }

    public function saveEndereco(SaveEnderecoDTO $dados)
    {
        return $this->cadastroEnderecoRepository->saveEndereco($dados);
    }

    public function updateEndereco(UpdateEnderecoDTO $dados)
    {
        return $this->cadastroEnderecoRepository->updateEndereco($dados);
    }

    public function deleteEndereco($sequencial)
    {
        return $this->cadastroEnderecoRepository->deleteEndereco($sequencial);
    }
}
