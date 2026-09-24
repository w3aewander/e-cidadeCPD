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

namespace App\Domain\Tributario\ISSQN\DTO\Redesim\EnderecoInscricao;

class SaveEnderecoDTO
{
    public $inscricao;
    public $municipal;
    public $cep;
    public $rua;
    public $numero;
    public $destinatario;
    public $bairro;
    public $complemento;
    public $rualabel;
    public $bairrolabel;
    public $usuario;

    public function __construct(
        $inscricao,
        $municipal,
        $cep,
        $rua,
        $numero,
        $destinatario,
        $bairro,
        $complemento,
        $bairrolabel,
        $rualabel,
        $DB_id_usuario
    ) {
        $this->inscricao = $inscricao;
        $this->municipal = $municipal;
        $this->cep = $cep;
        $this->rua = $rua;
        $this->numero = $numero;
        $this->destinatario = $destinatario;
        $this->bairro = $bairro;
        $this->complemento = $complemento;
        $this->bairrolabel = $bairrolabel;
        $this->rualabel = $rualabel;
        $this->usuario = $DB_id_usuario;
    }

    public static function fromArray($data)
    {
        return new self(
            isset($data['inscricao']) ? $data['inscricao'] : '',
            isset($data['municipal']) ? $data['municipal'] : '',
            isset($data['cep'])? $data['cep'] : '',
            isset($data['rua'])? $data['rua'] : '',
            isset($data['numero'])? $data['numero'] : '',
            isset($data['destinatario'])? $data['destinatario'] : '',
            isset($data['bairro'])? $data['bairro'] : '',
            isset($data['complemento'])? $data['complemento'] : '',
            isset($data['bairrolabel'])? $data['bairrolabel'] : '',
            isset($data['rualabel'])? $data['rualabel'] : '',
            isset($data['DB_id_usuario'])? $data['DB_id_usuario'] : ''
        );
    }
}
