<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class M30646NovoCampoConvenioPoscpf extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = <<<SQL

        ALTER TABLE pessoal.convenio ADD COLUMN r56_poscpf varchar(6) default null;

        ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl ENABLE;
        ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl_drop ENABLE;

    COMMENT ON TABLE pessoal.convenio IS '{
        "sigla": "r56  ",
        "rotulo": "Definicao do convenio, local de leitura e toda a e",
        "dataincl": "2003-11-24",
        "descricao": "Definicao do convenio, local de leitura e toda a especificacao das posicoes do arquivo texto para leitura.                                                                                              ",
        "naolibfor": false,
        "naolibfunc": false,
        "naolibprog": false,
        "tipotabela": 0,
        "naolibclass": false
        }';
        
    COMMENT ON COLUMN pessoal.convenio.r56_vq01 IS '{
        "rotulo": "define se e valor ou quantidad",
        "tipoob": "",
        "tamanho": null,
        "autocompl": false,
        "descricao": "define se e valor ou quantidad",
        "maiusculo": false,
        "rotulorel": "define se e valor ou quantidad",
        "aceitatipo": 0
    }';
        
    COMMENT ON COLUMN pessoal.convenio.r56_posq03 IS '{
        "rotulo": "rubrica 3",
        "tipoob": "text",
        "tamanho": 6,
        "autocompl": false,
        "descricao": "Posição da rubrica 3",
        "maiusculo": false,
        "rotulorel": "rubrica 3",
        "aceitatipo": 1
    }';

    COMMENT ON COLUMN pessoal.convenio.r56_poseve IS '{
        "rotulo": " relacionamento",
        "tipoob": "text",
        "tamanho": 6,
        "autocompl": false,
        "descricao": "Posição código relacionamento",
        "maiusculo": false,
        "rotulorel": " relacionamento",
        "aceitatipo": 1
    }';

    COMMENT ON COLUMN pessoal.convenio.r56_posrubrica IS '{
        "rotulo": "Posição da Rubrica",
        "tipoob": "text",
        "tamanho": 6,
        "autocompl": false,
        "descricao": "Posição da Rubrica1",
        "maiusculo": false,
        "rotulorel": "Posição da Rubrica1",
        "aceitatipo": 1
    }';
        
    COMMENT ON COLUMN pessoal.convenio.r56_posq01 IS '{
        "rotulo": "rubrica 1",
        "tipoob": "text",
        "tamanho": 6,
        "autocompl": false,
        "descricao": "Posição rubrica 1",
        "maiusculo": false,
        "rotulorel": "rubrica 1",
        "aceitatipo": 1
    }';
        
    COMMENT ON COLUMN pessoal.convenio.r56_posmes IS '{
        "rotulo": "leitura do mês",
        "tipoob": "text",
        "tamanho": 6,
        "autocompl": false,
        "descricao": "Posição leitura do mês",
        "maiusculo": false,
        "rotulorel": "leitura do mês",
        "aceitatipo": 1
    }';
        
    COMMENT ON COLUMN pessoal.convenio.r56_linhastrailler IS '{
        "rotulo": "Linhas trailler",
        "tipoob": "text",
        "tamanho": null,
        "autocompl": false,
        "descricao": "Quantidade de linhas no trailler",
        "maiusculo": false,
        "rotulorel": "Linhas trailler",
        "aceitatipo": 1
    }';
        
    COMMENT ON COLUMN pessoal.convenio.r56_posq02 IS '{
        "rotulo": "rubrica 02",
        "tipoob": "text",
        "tamanho": 6,
        "autocompl": false,
        "descricao": "Posição da rubrica 02",
        "maiusculo": false,
        "rotulorel": " rubrica 02",
        "aceitatipo": 1
    }';
        
    COMMENT ON COLUMN pessoal.convenio.r56_vq03 IS '{
        "rotulo": "def.se rubr.3 e valor ou qtd.",
        "tipoob": "text",
        "tamanho": null,
        "autocompl": false,
        "descricao": "def.se rubr.3 e valor ou qtd.",
        "maiusculo": false,
        "rotulorel": "def.se rubr.3 e valor ou qtd.",
        "aceitatipo": 0
    }';
        
    COMMENT ON COLUMN pessoal.convenio.r56_codrel IS '{
        "rotulo": "convênio",
        "tipoob": "text",
        "tamanho": 4,
        "autocompl": false,
        "descricao": "Código convênio",
        "maiusculo": true,
        "rotulorel": "convênio",
        "aceitatipo": 0
    }';
        
    COMMENT ON COLUMN pessoal.convenio.r56_posano IS '{
        "rotulo": "leitura do ano",
        "tipoob": "text",
        "tamanho": 6,
        "autocompl": false,
        "descricao": "Posição de leitura do ano",
        "maiusculo": false,
        "rotulorel": "leitura do ano",
        "aceitatipo": 1
    }';
        
    COMMENT ON COLUMN pessoal.convenio.r56_linhasheader IS '{
        "rotulo": "Linhas header",
        "tipoob": "text",
        "tamanho": null,
        "autocompl": false,
        "descricao": "Quantidade de linhas no header",
        "maiusculo": false,
        "rotulorel": "Linhas header",
        "aceitatipo": 1
    }';
        
    COMMENT ON COLUMN pessoal.convenio.r56_descr IS '{
        "rotulo": "convênio",
        "tipoob": "text",
        "tamanho": 40,
        "autocompl": false,
        "descricao": "Descrição do convênio",
        "maiusculo": true,
        "rotulorel": "convênio",
        "aceitatipo": 0
    }';
        
    COMMENT ON COLUMN pessoal.convenio.r56_dirarq IS '{
        "rotulo": "Caminho",
        "tipoob": "text",
        "tamanho": 40,
        "autocompl": false,
        "descricao": "Caminho para leitura do arquivo a ser importado.",
        "maiusculo": true,
        "rotulorel": "Caminho",
        "aceitatipo": 0
    }';
        
    COMMENT ON COLUMN pessoal.convenio.r56_vq02 IS '{
        "rotulo": "def.se quant02 e valor ou quan",
        "tipoob": "text",
        "tamanho": null,
        "autocompl": false,
        "descricao": "def.se quant02 e valor ou quan",
        "maiusculo": false,
        "rotulorel": "def.se quant02 e valor ou quan",
        "aceitatipo": 0
    }';
        
    COMMENT ON COLUMN pessoal.convenio.r56_instit IS '{
        "rotulo": "Cod. Instituição",
        "tipoob": "text",
        "tamanho": null,
        "autocompl": false,
        "descricao": "Código da Instituição",
        "maiusculo": false,
        "rotulorel": "Cod. Instituição",
        "aceitatipo": 0
    }';
        
    COMMENT ON COLUMN pessoal.convenio.r56_local IS '{
        "rotulo": "leitura",
        "tipoob": "text",
        "tamanho": 40,
        "autocompl": false,
        "descricao": "Local de leitura",
        "maiusculo": true,
        "rotulorel": "leitura",
        "aceitatipo": 0
    }';
        
    COMMENT ON COLUMN pessoal.convenio.r56_posreg IS '{
        "rotulo": "Servidor",
        "tipoob": "text",
        "tamanho": 6,
        "autocompl": false,
        "descricao": "Posição de leitura do código do Servidor",
        "maiusculo": false,
        "rotulorel": "Servidor",
        "aceitatipo": 1
    }';

    COMMENT ON COLUMN pessoal.convenio.r56_poscpf IS '{
        "rotulo": "Busca por CPF",
        "tipoob": "text",
        "tamanho": 6,
        "autocompl": false,
        "descricao": "Posição de leitura do código do Servidor pelo cpf",
        "maiusculo": false,
        "rotulorel": "Servidor",
        "aceitatipo": 1
    }';

       SELECT fc_gera_dicionario_apartir_tabela('pessoal', 'convenio');

       ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl DISABLE;
       ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl_drop DISABLE;
SQL;
        DB::connection()->getPdo()->exec($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $sql = <<<SQL

        ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl ENABLE;
        ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl_drop ENABLE;

        ALTER TABLE pessoal.convenio DROP COLUMN r56_poscpf;

        ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl DISABLE;
        ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl_drop DISABLE;
SQL;
        DB::connection()->getPdo()->exec($sql);

    }
}
