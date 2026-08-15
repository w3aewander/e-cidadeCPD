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

namespace App\Domain\Tributario\ISSQN\Repository\CadastroEndereco;

use App\Domain\Tributario\ISSQN\Model\CadastroEndereco\CadastroEndereco;
use DB;

class CadastroEnderecoRepository
{
    public function getEndereco($sequencial, $nome)
    {
        $endereco = DB::table('issbaseendereco')
        ->selectRaw("
            issbaseendereco.q205_codigo as codigo,
            issbaseendereco.q205_inscr as sequencial,
            issbaseendereco.q205_cep as cep,
            issbaseendereco.q205_num as numero,
            issbaseendereco.q205_compl as complemento,
            issbaseendereco.q205_dest as destinatario,
            issbaseendereco.q205_municipal as municipal,
            cgm.z01_nome as nome,
            issbaseendereco.q205_bairro as bairro,
            issbaseendereco.q205_rua as rua,
            CASE 
                WHEN issbaseendereco.q205_municipal = true THEN bairro.j13_descr
                ELSE issbaseendereco.q205_bairronome
            END as bairrolabel,
            CASE 
                WHEN issbaseendereco.q205_municipal = true THEN ruastipo.j88_sigla || ' ' || ruas.j14_nome
                ELSE issbaseendereco.q205_ruanome
            END as rualabel
        ")
        ->leftJoin('issqn.issbase', 'issbase.q02_inscr', '=', 'issbaseendereco.q205_inscr')
        ->join('cgm', 'cgm.z01_numcgm', '=', 'issbase.q02_numcgm')
        ->leftJoin('bairro', 'bairro.j13_codi', '=', 'issbaseendereco.q205_bairro')
        ->leftJoin('ruas', 'ruas.j14_codigo', '=', 'issbaseendereco.q205_rua')
        ->leftJoin('ruastipo', 'ruastipo.j88_codigo', '=', 'ruas.j14_tipo')
        ->when($sequencial, function ($query) use ($sequencial) {
            $query->where('issbaseendereco.q205_inscr', '=', $sequencial);
        })
        ->when($nome, function ($query) use ($nome) {
            $query->where('cgm.z01_nome', '=', $nome);
        })
        ->orderBy('cgm.z01_nome', 'asc')
        ->get();

        
        
        return $endereco;
    }

    public function saveEndereco($dados)
    {
        $endereco = CadastroEndereco::where('q205_inscr', $dados->inscricao)->first();
        if ($endereco) {
            return '0';
        }

        $endereco = new CadastroEndereco;
        if ($dados->municipal == 1) {
            $endereco->q205_bairro = $dados->bairro;
            $endereco->q205_rua = $dados->rua;
        } else {
            $endereco->q205_bairronome = $dados->bairrolabel;
            $endereco->q205_ruanome = $dados->rualabel;
        }

        $endereco->q205_inscr = $dados->inscricao;
        $endereco->q205_cep = $dados->cep;
        $endereco->q205_num = $dados->numero;
        $endereco->q205_compl = $dados->complemento;
        $endereco->q205_dest = $dados->destinatario;
        $endereco->q205_municipal = $dados->municipal;
        $endereco->q205_usuario = $dados->usuario;
        $endereco->save();

        return "1";
    }

    public function updateEndereco($dados)
    {
        $endereco = CadastroEndereco::where('q205_inscr', $dados->inscricao)->first();

        if ($dados->municipal == 1) {
            $endereco->q205_bairro = $dados->bairro;
            $endereco->q205_rua = $dados->rua;
            $endereco->q205_bairronome = null;
            $endereco->q205_ruanome = null;
        } else {
            $endereco->q205_bairronome = $dados->bairrolabel;
            $endereco->q205_ruanome = $dados->rualabel;
            $endereco->q205_bairro = null;
            $endereco->q205_rua = null;
        }

        $endereco->q205_cep = $dados->cep;
        $endereco->q205_num = $dados->numero;
        $endereco->q205_compl = $dados->complemento;
        $endereco->q205_dest = $dados->destinatario;
        $endereco->q205_municipal = $dados->municipal;
        $endereco->q205_usuario = $dados->usuario;
        $endereco->q205_atualizado = date('Y-m-d h:i:s');

        $endereco->save();
    }

    public function deleteEndereco($sequencial)
    {
        $endereco = CadastroEndereco::where('q205_inscr', $sequencial)->first();
        
        $endereco->delete();
    }
}
