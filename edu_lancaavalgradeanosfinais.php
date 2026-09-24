<?php

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("dbforms/db_funcoes.php"));


function testa($var){
    echo "<pre>";
    print_r($var);
    echo "</pre>";
}



function buscaDisciplina($coddisciplina){
    $sql = pg_query("SELECT ed232_c_descr FROM caddisciplina INNER JOIN disciplina ON ed12_i_caddisciplina = ed232_i_codigo WHERE ed12_i_codigo = {$coddisciplina}");
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["ed232_c_descr"];
}

function buscaTurma($cturma){
    $sql = pg_query("SELECT ed57_c_descr FROM turma WHERE ed57_i_codigo = {$cturma}");
    $resultado = pg_fetch_all($sql);
    return trim($resultado[0]["ed57_c_descr"]);
}

function buscaEtapa($et){
    $sql = pg_query("SELECT ed11_c_descr FROM serie WHERE ed11_i_codigo = {$et}");
    $resultado = pg_fetch_all($sql);
    return trim($resultado[0]["ed11_c_descr"]);
}

function verificaNomePeriodo($codigo){
    $sql = pg_query("SELECT ed09_c_descr FROM periodoavaliacao INNER JOIN procavaliacao ON ed09_i_codigo = ed41_i_periodoavaliacao WHERE ed41_i_codigo = {$codigo};");
    $resultado = pg_fetch_all($sql);
    return trim($resultado[0]["ed09_c_descr"]);
}

function buscaColunas($escola){
    $sql = pg_query("SELECT colunas FROM paramcoluna WHERE escola = {$escola}");
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["colunas"];
}

function buscaNotas($aluno, $matricula, $calendario, $turma, $etapa, $periodo, $disciplina){    
    $sql = pg_query("SELECT * FROM dclanotas WHERE codigoaluno = {$aluno} AND codigomatricula = {$matricula} AND calendario = {$calendario} AND turma = {$turma} AND etapa = {$etapa} AND periodo = {$periodo} AND disciplina = {$disciplina}");
    //echo "SELECT * FROM dclanotas WHERE codigoaluno = {$aluno} AND codigomatricula = {$matricula} AND calendario = {$calendario} AND turma = {$turma} AND etapa = {$etapa} AND periodo = {$periodo} AND disciplina = {$disciplina}"; echo "<br>";

    $resultado = pg_fetch_all($sql);
    return $resultado[0];
}

function buscaRegencia($turma, $disciplina){
    $sql = pg_query("SELECT ed59_i_codigo FROM regencia WHERE ed59_i_turma = {$turma} AND ed59_i_disciplina = {$disciplina}");
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["ed59_i_codigo"];
}

function buscaCodigoDiarioAtual($codmatricula, $codregencia, $codserie, $codturma){
    $sql = pg_query("SELECT diario.*, ed59_i_codigo from diario inner join aluno on ed47_i_codigo = ed95_i_aluno inner join matricula on ed60_i_aluno = ed47_i_codigo inner join matriculaserie on ed60_i_codigo = ed221_i_matricula inner join regencia on ed59_i_codigo = ed95_i_regencia and ed59_i_serie = ed221_i_serie where ed60_i_codigo = {$codmatricula} and ed95_i_regencia = {$codregencia} and ed95_i_serie = {$codserie} and ed59_i_turma = {$codturma} order by ed95_i_codigo");
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["ed95_i_codigo"];
}

function buscaSeqDiarioAtual($codmatricula, $codregencia, $codserie, $codturma, $nperiodo){
    
    $qtdperiodos = (str_contains($nperiodo, "TRIMESTRE")) ? 3 : 4;
    $codperiodo = substr(trim($nperiodo), 0, 1);
    
    if($qtdperiodos == 3){
        if($codperiodo == 1){
            $sequencia = 1;
        }elseif($codperiodo == 2){
            $sequencia = 2;
        }elseif($codperiodo == 3){
            $sequencia = 3;
        }
    }else{
        /*
        if($codperiodo == 1){
            $sequencia = 1;
        }elseif($codperiodo == 2){
            $sequencia = 2;
        }elseif($codperiodo == 3){
            $sequencia = 5;
        }elseif($codperiodo == 4){
            $sequencia = 6;
        }
        */
        if($codperiodo == 1){
            $sequencia = 1;
        }elseif($codperiodo == 2){
            $sequencia = 2;
        }elseif($codperiodo == 3){
            $sequencia = 3;
        }elseif($codperiodo == 4){
            $sequencia = 4;
        }
    }

    $sql = pg_query("SELECT diario.*, ed72_i_codigo, ed59_i_codigo from diario inner join aluno on ed47_i_codigo = ed95_i_aluno inner join matricula on ed60_i_aluno = ed47_i_codigo inner join matriculaserie on ed60_i_codigo = ed221_i_matricula inner join regencia on ed59_i_codigo = ed95_i_regencia and ed59_i_serie = ed221_i_serie inner join diarioavaliacao on ed72_i_diario = ed95_i_codigo inner join procavaliacao on ed41_i_codigo = ed72_i_procavaliacao where ed60_i_codigo = {$codmatricula} and ed95_i_regencia = {$codregencia} and ed95_i_serie = {$codserie} and ed59_i_turma = {$codturma} AND ed41_i_sequencia = {$sequencia} order by ed72_i_codigo");
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["ed72_i_codigo"];
}

function buscaNotaDiarioAtual($codiario){            
    $sql = pg_query("SELECT ed72_i_valornota FROM diarioavaliacao WHERE ed72_i_codigo = {$codiario}");
    $resultado = pg_fetch_all($sql);            
    return (isset($resultado[0]["ed72_i_valornota"])) ? $resultado[0]["ed72_i_valornota"] : null;
}

function buscaNotaPeriodoAtualTrimestre($coddiario, $periodo){
    $codperiodo = substr(trim($periodo), 0, 1);
    if($codperiodo == 1){
        $sequencia = 1;
    }elseif($codperiodo == 2){
        $sequencia = 2;
    }elseif($codperiodo == 3){
        $sequencia = 3;
    }
    $sql = pg_query("SELECT ed72_i_codigo, ed72_i_procavaliacao, ed72_i_numfaltas, ed72_i_valornota, ed72_c_valorconceito, ed72_t_parecer, ed72_c_aprovmin, ed72_c_amparo, ed72_c_tipo, ed72_c_convertido, ed41_i_sequencia from diarioavaliacao inner join procavaliacao on ed41_i_codigo = ed72_i_procavaliacao where ed72_i_diario = {$coddiario} AND ed41_i_sequencia = {$sequencia} ORDER BY ed41_i_sequencia");
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["ed72_i_valornota"];
}

function buscaNotaPeriodoAtualBimestre($coddiario, $periodo){
    $codperiodo = substr(trim($periodo), 0, 1);
    if($codperiodo == 1){
        $sequencia = 1;
    }elseif($codperiodo == 2){
        $sequencia = 2;
    }elseif($codperiodo == 3){
        $sequencia = 5;
    }elseif($codperiodo == 4){
        $sequencia = 6;
    }
    $sql = pg_query("SELECT ed72_i_codigo, ed72_i_procavaliacao, ed72_i_numfaltas, ed72_i_valornota, ed72_c_valorconceito, ed72_t_parecer, ed72_c_aprovmin, ed72_c_amparo, ed72_c_tipo, ed72_c_convertido, ed41_i_sequencia from diarioavaliacao inner join procavaliacao on ed41_i_codigo = ed72_i_procavaliacao where ed72_i_diario = {$coddiario} AND ed41_i_sequencia = {$sequencia} ORDER BY ed41_i_sequencia");
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["ed72_i_valornota"];
}

function buscaMediaTrimestre($ed95_i_codigo){
    $sql = pg_query("SELECT ed72_i_codigo, ed72_i_procavaliacao, ed72_i_numfaltas, ed72_i_valornota, ed72_c_valorconceito, ed72_t_parecer, ed72_c_aprovmin, ed72_c_amparo, ed72_c_tipo, ed72_c_convertido, ed41_i_sequencia from diarioavaliacao inner join procavaliacao on ed41_i_codigo = ed72_i_procavaliacao where ed72_i_diario = {$ed95_i_codigo} ORDER BY ed41_i_sequencia");
    $resultado = pg_fetch_all($sql);    
    $somatorio = 0;
    $quociente = 0;
    foreach ($resultado as $linha){
        if(isset($linha["ed72_i_valornota"])){$quociente++;}
        $somatorio += $linha["ed72_i_valornota"];
    }
    $media = $somatorio / $quociente;
    $novamedia = sprintf("%.1f", floor($media * 10) / 10);
    $novamedia = (float)$novamedia;
    return $novamedia;
}

function buscaMediaBimestre($ed95_i_codigo){
    $sql = pg_query("SELECT ed72_i_codigo, ed72_i_procavaliacao, ed72_i_numfaltas, ed72_i_valornota, ed72_c_valorconceito, ed72_t_parecer, ed72_c_aprovmin, ed72_c_amparo, ed72_c_tipo, ed72_c_convertido, ed41_i_sequencia from diarioavaliacao inner join procavaliacao on ed41_i_codigo = ed72_i_procavaliacao where ed72_i_diario = {$ed95_i_codigo} ORDER BY ed41_i_sequencia");
    $resultado = pg_fetch_all($sql);    
    $somatorio = 0;
    $quociente = 0;


    
    
    $notas["b1"] = $resultado[0]["ed72_i_valornota"];
    $notas["b2"] = $resultado[1]["ed72_i_valornota"];
    $notas["b3"] = $resultado[2]["ed72_i_valornota"];
    $notas["b4"] = $resultado[3]["ed72_i_valornota"];

    
    
    /*
    $recsem = $resultado[2]["ed72_i_valornota"];
    
    if(isset($recsem)){
        if($notas["b1"] <= $notas["b2"]){
            if($recsem > $notas["b1"]){
                $notas["b1"] = $recsem;
            }
        }else{
            if($recsem > $notas["b2"]){
                $notas["b2"] = $recsem;
            }
        }
    }
    */
    
    foreach ($notas as $linha){
        if(isset($linha)){$quociente++;}        
        $somatorio += $linha;
    }
    $media = $somatorio / $quociente;
    

    //$novamedia = sprintf("%.1f", floor($media * 10) / 10);
    //$novamedia = round($media, 1);    
    $novamedia = floor(($media * 10) + 0.00001) / 10;
    $novamedia = (float)$novamedia;
    

    $dados["novamedia"] = $novamedia;
    $dados["somatorio"] = $notas["b1"] + $notas["b2"] + $notas["b3"] + $notas["b4"];
    

    return $dados;
}

function buscaResultadosFinais($coddiario){
    $sql = pg_query("SELECT ed73_i_codigo, ed73_i_diario, ed73_i_procresultado, ed73_i_valornota, ed73_valorreal, ed73_c_valorconceito, ed43_i_sequencia from diarioresultado inner join procresultado on ed43_i_codigo = ed73_i_procresultado where ed73_i_diario = {$coddiario} order by ed43_i_sequencia");
    $resultado = pg_fetch_all($sql);    
    return $resultado;
}

function buscaResultadoFinal($coddiario){
    $sql = pg_query("SELECT * FROM diariofinal WHERE ed74_i_diario = {$coddiario}");
    $resultado = pg_fetch_all($sql);
    return $resultado[0];
}



function buscaAlunosDaTurma($turma){
    $sql = pg_query("SELECT ed47_i_codigo, ed47_v_nome, ed60_i_codigo, ed60_i_numaluno FROM aluno INNER JOIN matricula ON ed60_i_aluno = ed47_i_codigo WHERE ed60_i_turma = {$turma} AND ed60_c_situacao = 'MATRICULADO' AND ed60_c_ativa = 'S' ORDER BY ed60_i_numaluno, ed47_v_nome");
    $resultado = pg_fetch_all($sql);
    return $resultado;
}


function buscaTitulosIA($calendario, $turma, $etapa, $periodo, $disciplina){    
    $sql = pg_query("SELECT id, nomeia1, nomeia2, nomeia3, nomeia4, nomeia5 FROM titulosia WHERE calendario = {$calendario} AND turma = {$turma} AND etapa = {$etapa} AND periodo = {$periodo} AND disciplina = {$disciplina}");
    $resultado = pg_fetch_all($sql);
    
    if($resultado){
        $titulos["id"] = $resultado[0]["id"];
        $titulos["ia1"] = ($resultado[0]["nomeia1"]) ? trim($resultado[0]["nomeia1"]) : "IA 1";
        $titulos["ia2"] = ($resultado[0]["nomeia2"]) ? trim($resultado[0]["nomeia2"]) : "IA 2";
        $titulos["ia3"] = ($resultado[0]["nomeia3"]) ? trim($resultado[0]["nomeia3"]) : "IA 3";
        $titulos["ia4"] = ($resultado[0]["nomeia4"]) ? trim($resultado[0]["nomeia4"]) : "IA 4";
        $titulos["ia5"] = ($resultado[0]["nomeia5"]) ? trim($resultado[0]["nomeia5"]) : "IA 5";
    }else{
        $titulos["id"] = "";
        $titulos["ia1"] = "IA 1";
        $titulos["ia2"] = "IA 2";
        $titulos["ia3"] = "IA 3";
        $titulos["ia4"] = "IA 4";
        $titulos["ia5"] = "IA 5";
    }
    return $titulos;
}

function buscaobsIA($calendario, $turma, $etapa, $periodo, $disciplina){
    $sql = pg_query("SELECT id, observacao FROM titulosiaobs WHERE calendario = {$calendario} AND turma = {$turma} AND etapa = {$etapa} AND periodo = {$periodo} AND disciplina = {$disciplina}");
    $resultado = pg_fetch_all($sql);
    return $resultado[0];
}

//======================================================

        

        function buscaCodDiario($codaluno, $codregencia, $periodo, $disciplina,$calendario){

			$descrCalendario = pg_result(db_query("SELECT ed52_c_descr from calendario where ed52_i_codigo = {$calendario}"),0,0);
            $sql = pg_query("SELECT 
			                 ed72_i_codigo 
							 FROM diario 
							 INNER JOIN diarioavaliacao ON ed95_i_codigo = ed72_i_diario 
							 INNER JOIN feriado ON ed54_i_calendario = ed95_i_calendario 
							 WHERE ed95_i_aluno = {$codaluno} 
							 AND 
							 ed95_i_regencia = {$codregencia} 
							 AND 
							 ed72_i_procavaliacao = {$periodo} LIMIT 1");			
            $resultado = pg_fetch_all($sql);
// verificar a necessidade de acrescentar o calendario na busca, para que não pegue notas de outro calendario			
			if(pg_num_rows($resultado)==0)
			{
		        $sql = pg_query("
				                SELECT 
								ed72_i_codigo,
								ed47_i_codigo,
								ed47_v_nome
								FROM 
								diarioavaliacao
								inner join procavaliacao    on ed41_i_codigo          = ed72_i_procavaliacao
								inner join periodoavaliacao on ed09_i_codigo          = ed41_i_periodoavaliacao
								inner join diario           on ed95_i_codigo          = ed72_i_diario
								inner join regencia         on ed59_i_codigo          = ed95_i_regencia
								inner join disciplina       on ed12_i_codigo          = ed59_i_disciplina
								inner join aluno            on ed47_i_codigo          = ed95_i_aluno
								WHERE 
								ed95_i_aluno = {$codaluno} 
								AND 
								ed59_i_disciplina = {$disciplina}
								AND
                                ed72_i_escola = ".db_getsession("DB_coddepto")."								 
								and
								ed72_i_procavaliacao = {$periodo} LIMIT 1
								 ");
				$resultado = pg_fetch_all($sql);
			}	
            return $resultado[0]["ed72_i_codigo"];
        }

        

        

        

        function verificaRecuperacao($codigo){            
            $sql = pg_query("SELECT ed09_i_codigo, ed09_c_descr FROM periodoavaliacao INNER JOIN procavaliacao ON ed09_i_codigo = ed41_i_periodoavaliacao WHERE ed41_i_codigo = {$codigo};");
            $resultado = pg_fetch_all($sql);
            if($resultado[0]["ed09_i_codigo"] == 9 || $resultado[0]["ed09_i_codigo"] == 10){
                return $resultado[0];    
            }
            return false;
        }

        function buscaCodDiarioAluno($codregencia, $codaluno){
            $sql = pg_query("SELECT  ed95_i_codigo FROM diario WHERE ed95_i_regencia = {$codregencia} AND ed95_i_aluno = {$codaluno}");
            $resultado = pg_fetch_all($sql);
            return $resultado[0]["ed95_i_codigo"];
        }

        function retornaPeriodoRec($codigo){            
            $sql = pg_query("SELECT ed09_i_codigo FROM periodoavaliacao INNER JOIN procavaliacao ON ed09_i_codigo = ed41_i_periodoavaliacao WHERE ed41_i_codigo = {$codigo};");
            $resultado = pg_fetch_all($sql);
            return $resultado[0]["ed09_i_codigo"];
        }

        function verificaRecSemestral($coddiario){            
            $sql = pg_query("SELECT sum(ed72_i_valornota) as nota from diarioavaliacao inner join procavaliacao on ed41_i_codigo = ed72_i_procavaliacao left join pareceraval on ed93_i_diarioavaliacao = ed72_i_codigo left join abonofalta on ed80_i_diarioavaliacao = ed72_i_codigo INNER JOIN periodoavaliacao ON ed41_i_periodoavaliacao = ed09_i_codigo where ed72_i_diario = {$coddiario} AND ed09_i_codigo in(1,2)");
            $resultado = pg_fetch_all($sql);
            return $resultado[0]["nota"];
        }

        function verificaRecFinal($coddiario){
            //var_dump($coddiario);
            //$sql = pg_query("SELECT sum(ed72_i_valornota) as nota from diarioavaliacao inner join procavaliacao on ed41_i_codigo = ed72_i_procavaliacao left join pareceraval on ed93_i_diarioavaliacao = ed72_i_codigo left join abonofalta on ed80_i_diarioavaliacao = ed72_i_codigo INNER JOIN periodoavaliacao ON ed41_i_periodoavaliacao = ed09_i_codigo where ed72_i_diario = {$coddiario} AND ed09_i_codigo in(3,4)");
            $sql = pg_query("SELECT ed72_i_valornota, ed09_i_codigo from diarioavaliacao inner join procavaliacao on ed41_i_codigo = ed72_i_procavaliacao left join pareceraval on ed93_i_diarioavaliacao = ed72_i_codigo left join abonofalta on ed80_i_diarioavaliacao = ed72_i_codigo INNER JOIN periodoavaliacao ON ed41_i_periodoavaliacao = ed09_i_codigo where ed72_i_diario = {$coddiario} AND ed09_i_codigo IN(1,2,3,4,9) ORDER BY ed09_i_codigo");
                $resultado = pg_fetch_all($sql);
                echo "<pre>";
                print_r($resultado);
                echo "</pre>";
                $n1 = $resultado[0]["ed72_i_valornota"];
                $n2 = $resultado[1]["ed72_i_valornota"];
                $n3 = $resultado[2]["ed72_i_valornota"];
                $n4 = $resultado[3]["ed72_i_valornota"];
                $nrec = $resultado[4]["ed72_i_valornota"];

                if(isset($nrec) && $nrec != "" && $nrec != 0){
                    if($n1 > $n2){
                        $n2 = $nrec;
                    }else{
                        $n1 = $nrec;
                    }    
                }
                

                $soma = $n1 + $n2 + $n3 + $n4;
                $media = $soma / 4;
                $novamedia = sprintf("%.1f", floor($media * 10) / 10);
                $novamedia = (float)$novamedia;
                return $novamedia;
        }

        function verificaNotaFinal($coddiario){
            $sql = pg_query("SELECT ed74_c_valoraprov FROM diariofinal WHERE ed74_i_diario = {$coddiario}");
            $resultado = pg_fetch_all($sql);
            return $resultado[0]["ed74_c_valoraprov"];
        }

        function verificaRecFinal2($coddiario){
            //$sql = pg_query("SELECT sum(ed72_i_valornota) as nota from diarioavaliacao inner join procavaliacao on ed41_i_codigo = ed72_i_procavaliacao left join pareceraval on ed93_i_diarioavaliacao = ed72_i_codigo left join abonofalta on ed80_i_diarioavaliacao = ed72_i_codigo INNER JOIN periodoavaliacao ON ed41_i_periodoavaliacao = ed09_i_codigo where ed72_i_diario = {$coddiario} AND ed09_i_codigo in(3,4)");
			

            $sql = pg_query("SELECT ed72_i_valornota, ed09_i_codigo from diarioavaliacao inner join procavaliacao on ed41_i_codigo = ed72_i_procavaliacao left join pareceraval on ed93_i_diarioavaliacao = ed72_i_codigo left join abonofalta on ed80_i_diarioavaliacao = ed72_i_codigo INNER JOIN periodoavaliacao ON ed41_i_periodoavaliacao = ed09_i_codigo where ed72_i_diario = {$coddiario} AND ed09_i_codigo IN(1,2,3,4,9,10) ORDER BY ed09_i_codigo");
                $resultado = pg_fetch_all($sql);
                

                $n1 = $resultado[0]["ed72_i_valornota"];
                $n2 = $resultado[1]["ed72_i_valornota"];
                $n3 = $resultado[2]["ed72_i_valornota"];
                $n4 = $resultado[3]["ed72_i_valornota"];
                $nrec = $resultado[4]["ed72_i_valornota"];
                $nrec2 = $resultado[5]["ed72_i_valornota"];

                if(isset($nrec) && $nrec != "" && $nrec != 0){
                    if($n1 > $n2){
                        $n2 = $nrec;
                    }else{
                        $n1 = $nrec;
                    }
                }

                $soma = $n1 + $n2 + $n3 + $n4;
                $media = $soma / 4;
                $mediafinal = ($media + $nrec2) / 2;

                $novamedia = sprintf("%.1f", floor($mediafinal * 10) / 10);
                $novamedia = (float)$novamedia;
                return $novamedia;
        }

        

        function buscaMediaAnosIniciais($aluno, $regencia){
            $sql = pg_query("SELECT distinct ed72_i_codigo, ed72_i_procavaliacao FROM diario INNER JOIN diarioavaliacao ON ed95_i_codigo = ed72_i_diario INNER JOIN feriado ON ed54_i_calendario = ed95_i_calendario WHERE ed95_i_aluno = {$aluno} AND ed95_i_regencia = {$regencia} ORDER BY ed72_i_procavaliacao");
            $resultado = pg_fetch_all($sql);
            
            $coddiario = "";
            foreach ($resultado as $linha) {
                $coddiario .= $linha["ed72_i_codigo"] . ", ";
            }
            $coddiario = rtrim($coddiario, ", ");            
            
            $sql2 = pg_query("SELECT ed72_i_valornota FROM diarioavaliacao WHERE ed72_i_codigo in({$coddiario})");
            $resultado2 = pg_fetch_all($sql2);
            $media = 0;
            foreach ($resultado2 as $linha2) {
                $media += $linha2["ed72_i_valornota"];
            }
            $media = $media / 3;            
            $novamedia = sprintf("%.1f", floor($media * 10) / 10);
            $novamedia = (float)$novamedia;
            
            return $novamedia;            
        }

        function buscaTotalAteEntaoAnosIniciais($aluno, $regencia){
            $sql = pg_query("SELECT distinct ed72_i_codigo, ed72_i_procavaliacao FROM diario INNER JOIN diarioavaliacao ON ed95_i_codigo = ed72_i_diario INNER JOIN feriado ON ed54_i_calendario = ed95_i_calendario WHERE ed95_i_aluno = {$aluno} AND ed95_i_regencia = {$regencia} ORDER BY ed72_i_procavaliacao");
            $resultado = pg_fetch_all($sql);
            
            $coddiario = "";
            foreach ($resultado as $linha) {
                $coddiario .= $linha["ed72_i_codigo"] . ", ";
            }
            $coddiario = rtrim($coddiario, ", ");            
            
            $sql2 = pg_query("SELECT ed72_i_valornota, ed72_i_codigo FROM diarioavaliacao WHERE ed72_i_codigo in({$coddiario})");
            $resultado2 = pg_fetch_all($sql2);
            return $resultado2;
            /*$media = 0;
            foreach ($resultado2 as $linha2) {
                $media += $linha2["ed72_i_valornota"];
            }
            $media = $media / 3;            
            $novamedia = sprintf("%.1f", floor($media * 10) / 10);
            $novamedia = (float)$novamedia;
            
            return $novamedia;            
            */
        }

        function buscaCodigoMAnosIniciais($aluno, $disciplina, $turma){
            $sql = pg_query("SELECT ed73_i_codigo FROM diarioresultado INNER JOIN diario ON ed73_i_diario = ed95_i_codigo INNER JOIN regencia ON ed95_i_regencia = ed59_i_codigo WHERE ed95_i_aluno = {$aluno} AND ed59_i_disciplina = {$disciplina} AND ed59_i_turma = {$turma} ORDER BY ed95_i_regencia");
            $resultado = pg_fetch_all($sql);
            return $resultado[0]["ed73_i_codigo"];
        }

        function buscaCodigoRAnosIniciais($aluno, $disciplina, $turma){
            $sql = pg_query("SELECT ed73_i_diario FROM diarioresultado INNER JOIN diario ON ed73_i_diario = ed95_i_codigo INNER JOIN regencia ON ed95_i_regencia = ed59_i_codigo WHERE ed95_i_aluno = {$aluno} AND ed59_i_disciplina = {$disciplina} AND ed59_i_turma = {$turma} ORDER BY ed95_i_regencia");
            $resultado = pg_fetch_all($sql);
            $resultado = $resultado[0]["ed73_i_diario"];

            $sql2 = pg_query("SELECT ed74_i_codigo FROM diariofinal WHERE ed74_i_diario = {$resultado}");
            $resultado2 = pg_fetch_all($sql2);
            return $resultado2[0]["ed74_i_codigo"];
        }

        function buscaAnos($calendario){
            $sql = pg_query("SELECT ed52_c_descr from calendario where ed52_i_codigo = {$calendario}");
            $resultado = pg_fetch_all($sql);
            $periodo = $resultado[0]["ed52_c_descr"];
            

            if(str_contains($periodo, "INICIAIS")){
                return "P1";
            }

            if(str_contains($periodo, "FINAIS")){
                return "P2";
            }

            return false;
        }

        function buscaMediaAnosFinais($aluno, $regencia){
            $sql = pg_query("SELECT distinct ed72_i_codigo, ed72_i_procavaliacao FROM diario INNER JOIN diarioavaliacao ON ed95_i_codigo = ed72_i_diario INNER JOIN feriado ON ed54_i_calendario = ed95_i_calendario WHERE ed95_i_aluno = {$aluno} AND ed95_i_regencia = {$regencia} ORDER BY ed72_i_procavaliacao");
            $resultado = pg_fetch_all($sql);
            
            $coddiario = "";
            foreach ($resultado as $linha) {
                $coddiario .= $linha["ed72_i_codigo"] . ", ";
            }
            $coddiario = rtrim($coddiario, ", ");            
            
            $sql2 = pg_query("SELECT ed72_i_valornota FROM diarioavaliacao WHERE ed72_i_codigo in({$coddiario})");
            $resultado2 = pg_fetch_all($sql2);
            $media = 0;
            foreach ($resultado2 as $linha2) {
                $media += $linha2["ed72_i_valornota"];
            }
            $media = $media / 4;
            $novamedia = sprintf("%.1f", floor($media * 10) / 10);
            $novamedia = (float)$novamedia;
            
            return $novamedia;            
        }

        function buscaCodigoMAnosFinais($aluno, $disciplina, $turma){
            $sql = pg_query("SELECT ed73_i_codigo FROM diarioresultado INNER JOIN diario ON ed73_i_diario = ed95_i_codigo INNER JOIN regencia ON ed95_i_regencia = ed59_i_codigo WHERE ed95_i_aluno = {$aluno} AND ed59_i_disciplina = {$disciplina} AND ed59_i_turma = {$turma} ORDER BY ed95_i_regencia");
            $resultado = pg_fetch_all($sql);
            //echo "<pre>";
            //print_r($resultado);
            //echo "</pre>";
            return $resultado;
            //return $resultado[0]["ed73_i_codigo"];
        }

        function buscaCodigoRAnosFinais($aluno, $disciplina, $turma){
            $sql = pg_query("SELECT ed73_i_diario FROM diarioresultado INNER JOIN diario ON ed73_i_diario = ed95_i_codigo INNER JOIN regencia ON ed95_i_regencia = ed59_i_codigo WHERE ed95_i_aluno = {$aluno} AND ed59_i_disciplina = {$disciplina} AND ed59_i_turma = {$turma} ORDER BY ed95_i_regencia");
            $resultado = pg_fetch_all($sql);
            $resultado = $resultado[0]["ed73_i_diario"];

            $sql2 = pg_query("SELECT ed74_i_codigo FROM diariofinal WHERE ed74_i_diario = {$resultado}");
            $resultado2 = pg_fetch_all($sql2);
            return $resultado2[0]["ed74_i_codigo"];
        }

        function buscaTotalAteEntao($codigo){            
            $sql = pg_query("SELECT ed73_i_valornota FROM diarioresultado WHERE ed73_i_codigo = {$codigo}");
            $resultado = pg_fetch_all($sql);
            return $resultado[0]["ed73_i_valornota"];
        }

        function buscaNovoSomatorio($codigo){
			
			//$sql = "SELECT * FROM diarioresultado WHERE ed73_i_codigo = {$codigo}";
			
            $sql = pg_query("SELECT ed73_i_valornota FROM diarioresultado WHERE ed73_i_codigo = {$codigo}");
            $resultado = pg_fetch_all($sql);
            return (float)$resultado[0]["ed73_i_valornota"];
        }

        function ajustaMedia($media){
            $novamedia = sprintf("%.1f", floor($media * 10) / 10);
            $novamedia = (float)$novamedia;
            
            return $novamedia;            
        }

        function buscaNotaRecSemestral($coddiario){
            $sql = pg_query("SELECT ed72_i_valornota from diarioavaliacao inner join procavaliacao on ed41_i_codigo = ed72_i_procavaliacao left join pareceraval on ed93_i_diarioavaliacao = ed72_i_codigo left join abonofalta on ed80_i_diarioavaliacao = ed72_i_codigo INNER JOIN periodoavaliacao ON ed41_i_periodoavaliacao = ed09_i_codigo where ed72_i_diario = {$coddiario} AND ed09_i_codigo in(1,2)");
            $resultado = pg_fetch_all($sql);
            return $resultado;
        }

        function proximoSequencial(){
                            $sql = pg_query("SELECT nextval('diarioresultadorecuperacao_ed116_sequencial_seq')");
                            $resultado = pg_fetch_all($sql);
                            return $resultado[0]["nextval"];
        }

        function matriculaTransferido($aluno, $turmaanterior){

            $sql = pg_query("SELECT aluno.ed47_v_nome, aluno.ed47_i_codigo, matricula.ed60_i_numaluno, matricula.ed60_c_situacao, matricula.ed60_c_concluida, matricula.ed60_c_rfanterior, matricula.ed60_i_turmaant, matricula.ed60_d_datamatricula, matricula.ed60_matricula, matricula.ed60_i_codigo, matricula.ed60_c_parecer, ed60_tipoingresso, serie.ed11_c_descr, to_char(alunotransfturma.ed69_d_datatransf,'DD/MM/YYYY') as datasaida, (select array_to_string(array_accum(ed336_turnoreferente), ',') from matriculaturnoreferente inner join matricula as matturno on matturno.ed60_i_codigo = matriculaturnoreferente.ed337_matricula and matturno.ed60_i_turma = turma.ed57_i_codigo inner join turmaturnoreferente on turmaturnoreferente.ed336_codigo = matriculaturnoreferente.ed337_turmaturnoreferente and turmaturnoreferente.ed336_turma = turma.ed57_i_codigo where matturno.ed60_i_codigo = matricula.ed60_i_codigo) as turnoreferente, matricula.ed60_d_datasaida from matricula inner join aluno on aluno.ed47_i_codigo = matricula.ed60_i_aluno inner join turma on turma.ed57_i_codigo = matricula.ed60_i_turma inner join matriculaserie on matriculaserie.ed221_i_matricula = matricula.ed60_i_codigo inner join serie on serie.ed11_i_codigo = matriculaserie.ed221_i_serie inner join serieregimemat on serieregimemat.ed223_i_serie = serie.ed11_i_codigo inner join turmaserieregimemat on turmaserieregimemat.ed220_i_serieregimemat = serieregimemat.ed223_i_codigo and turmaserieregimemat.ed220_i_turma = matricula.ed60_i_turma left join turma as turmaant on turmaant.ed57_i_codigo = matricula.ed60_i_turmaant left join alunoprimat on alunoprimat.ed76_i_aluno = aluno.ed47_i_codigo left join alunotransfturma on alunotransfturma.ed69_i_matricula = matricula.ed60_i_codigo where ed47_i_codigo = {$aluno} AND matriculaserie.ed221_c_origem = 'S' AND ed60_i_turma = {$turmaanterior} and ed60_c_situacao = 'TROCA DE TURMA' order by matricula.ed60_i_numaluno, to_ascii(ed47_v_nome)");
            $resultado = pg_fetch_all($sql);
            return $resultado[0]["ed60_matricula"];
        }

        

        function voltaCodigoAvalFinal($coddiario){
            $sql = pg_query("SELECT ed72_i_codigo, ed72_i_valornota, ed41_i_sequencia from diarioavaliacao inner join procavaliacao on ed41_i_codigo = ed72_i_procavaliacao left join pareceraval on ed93_i_diarioavaliacao = ed72_i_codigo left join abonofalta on ed80_i_diarioavaliacao = ed72_i_codigo where ed72_i_diario = {$coddiario} AND ed41_i_sequencia = 6");
            $resultado = pg_fetch_all($sql);
            return $resultado[0]["ed72_i_codigo"];
        }

        function voltaCodigoNF($coddiario){
            $sql = pg_query("SELECT ed73_i_codigo, ed73_i_valornota, ed73_valorreal, ed43_i_sequencia from diarioresultado inner join procresultado on ed43_i_codigo = ed73_i_procresultado left join parecerresult on ed63_i_diarioresultado = ed73_i_codigo left join diarioresultadorecuperacao on ed116_diarioresultado = ed73_i_codigo where ed73_i_diario = {$coddiario} AND ed43_i_sequencia = 7");
            $resultado = pg_fetch_all($sql);
            return $resultado[0]["ed73_i_codigo"];
        }

        function voltaMediaRec($coddiario){
            $sql1 = pg_query("SELECT ed72_i_codigo, ed72_i_valornota, ed41_i_sequencia from diarioavaliacao inner join procavaliacao on ed41_i_codigo = ed72_i_procavaliacao left join pareceraval on ed93_i_diarioavaliacao = ed72_i_codigo left join abonofalta on ed80_i_diarioavaliacao = ed72_i_codigo where ed72_i_diario = {$coddiario} AND ed41_i_sequencia = 6");
            $resultado1 = pg_fetch_all($sql1);            
            $notarec = $resultado1[0]["ed72_i_valornota"];                
            

            $sql2 = pg_query("SELECT ed73_i_codigo, ed73_i_valornota, ed73_valorreal, ed43_i_sequencia from diarioresultado inner join procresultado on ed43_i_codigo = ed73_i_procresultado left join parecerresult on ed63_i_diarioresultado = ed73_i_codigo left join diarioresultadorecuperacao on ed116_diarioresultado = ed73_i_codigo where ed73_i_diario = {$coddiario} AND ed43_i_sequencia = 5"); //antes estava no 7
            $resultado2 = pg_fetch_all($sql2);
            $notafinal = $resultado2[0]["ed73_i_valornota"];
            

            $media = ((float)$notarec + (float)$notafinal) / 2;

            $novamedia = floor(($media * 10) + 0.00001) / 10;
            $novamedia = (float)$novamedia;

            return $novamedia;
        }

        function notaCorte($coddiario){
            $sql = pg_query("SELECT ed73_i_codigo, ed73_i_valornota, ed73_valorreal, ed43_i_sequencia from diarioresultado inner join procresultado on ed43_i_codigo = ed73_i_procresultado left join parecerresult on ed63_i_diarioresultado = ed73_i_codigo left join diarioresultadorecuperacao on ed116_diarioresultado = ed73_i_codigo where ed73_i_diario = {$coddiario} AND ed43_i_sequencia = 5");
            $resultado = pg_fetch_all($sql);
            return $resultado[0]["ed73_i_valornota"];
        }




//===========================VERSÃO FINAL
//testa($_POST);

$calendario = $_POST["xcalendario"];
$turma      = $_POST["xturma"];
$etapa      = $_POST["xetapa"];
$periodo    = $_POST["xperiodo"];
$disciplina = $_POST["xdisciplina"];

$nprofessor = $_POST["xregente"];
$nmateria   = buscaDisciplina($disciplina);
$nturma     = buscaTurma($turma);
$netapa     = buscaEtapa($etapa);
$nperiodo   = verificaNomePeriodo($periodo);
$codregencia = buscaRegencia($turma, $disciplina);

$colunas = buscaColunas(db_getsession("DB_coddepto"));
$qtdperiodos = (str_contains($nperiodo, "TRIMESTRE")) ? 3 : 4;


//var_dump($nperiodo);
//testa($_POST);
//============================





$recuperacao = verificaRecuperacao($periodo);
$xcamporecuperacao = ((int)$recuperacao["ed09_i_codigo"] == 10) ? true : false;
//var_dump($xcamporecuperacao);








$sql = "SELECT ed09_i_codigo FROM periodoavaliacao INNER JOIN procavaliacao ON ed09_i_codigo = ed41_i_periodoavaliacao WHERE ed41_i_codigo = {$periodo}";
$resultado = db_query($sql);
$nuperiodo = db_utils::fieldsMemory($resultado, 0)->ed09_i_codigo;

$sql          = "select  ed12_i_caddisciplina  from disciplina    where ed12_i_codigo = ".$disciplina;
$result       = db_query($sql);
$nudisciplina = db_utils::fieldsMemory($result, 0)->ed12_i_caddisciplina;





if(isset($_POST["salvar"])){
    //testa($_POST); die("Confere normal");
    $alunos = $_POST["codaluno"];
    $i = 0;

    foreach($alunos as $aluno){
        $codigoaluno = $aluno;
        $codigomatricula = $mataluno[$i];
			
        $ia1  = ($_POST["dcnota".$codigoaluno][0] == "") ? null : (float)$_POST["dcnota".$codigoaluno][0];
        $ia1r = ($_POST["dcnota".$codigoaluno][1] == "") ? null : (float)$_POST["dcnota".$codigoaluno][1];
        $ia2  = ($_POST["dcnota".$codigoaluno][2] == "") ? null : (float)$_POST["dcnota".$codigoaluno][2];
        $ia2r = ($_POST["dcnota".$codigoaluno][3] == "") ? null : (float)$_POST["dcnota".$codigoaluno][3];
        $ia3  = ($_POST["dcnota".$codigoaluno][4] == "") ? null : (float)$_POST["dcnota".$codigoaluno][4];
        $ia3r = ($_POST["dcnota".$codigoaluno][5] == "") ? null : (float)$_POST["dcnota".$codigoaluno][5];
        $ia4  = ($_POST["dcnota".$codigoaluno][6] == "") ? null : (float)$_POST["dcnota".$codigoaluno][6];
        $ia4r = ($_POST["dcnota".$codigoaluno][7] == "") ? null : (float)$_POST["dcnota".$codigoaluno][7];
        $ia5  = ($_POST["dcnota".$codigoaluno][8] == "") ? null : (float)$_POST["dcnota".$codigoaluno][8];
        $ia5r = ($_POST["dcnota".$codigoaluno][9] == "") ? null : (float)$_POST["dcnota".$codigoaluno][9];
        $recbimestre  = ($_POST["dcnota".$codigoaluno][11] == "") ? null : (float)$_POST["dcnota".$codigoaluno][11];
        $xmediafinal = null;
                                    
        if(!isset($ia1) || !isset($ia1r)){
            $xnota1 = null;
            if(isset($ia1)){
                $xnota1 = (float)$ia1;
            }
            if(isset($ia1r)){
                $xnota1 = (float)$ia1r;
            }
        }else{
            if((float)$ia1 >= (float)$ia1r){
                $xnota1 = (float)$ia1;
            }else{
                $xnota1 = (float)$ia1r;
            }    
        }
            
        if(!isset($ia2) || !isset($ia2r)){
            $xnota2 = null;
            if(isset($ia2)){
                $xnota2 = (float)$ia2;
            }
            if(isset($ia2r)){
                $xnota2 = (float)$ia2r;
            }
        }else{
            if((float)$ia2 >= (float)$ia2r){
                $xnota2 = (float)$ia2;
            }else{
                $xnota2 = (float)$ia2r;
            }
        }

        if(!isset($ia3) || !isset($ia3r)){
            $xnota3 = null;
            if(isset($ia3)){
                $xnota3 = (float)$ia3;
            }
            if(isset($ia3r)){
                $xnota3 = (float)$ia3r;
            }
        }else{
            if((float)$ia3 >= (float)$ia3r){
                $xnota3 = (float)$ia3;
            }else{
                $xnota3 = (float)$ia3r;
            }
        }

        if(!isset($ia4) || !isset($ia4r)){
            $xnota4 = null;
            if(isset($ia4)){
                $xnota4 = (float)$ia4;
            }
            if(isset($ia4r)){
                $xnota4 = (float)$ia4r;
            }
        }else{
            if((float)$ia4 >= (float)$ia4r){
                $xnota4 = (float)$ia4;
            }else{
                $xnota4 = (float)$ia4r;
            }
        }

        if(!isset($ia5) || !isset($ia5r)){
            $xnota5 = null;
            if(isset($ia5)){
                $xnota5 = (float)$ia5;
            }
            if(isset($ia15)){
                $xnota5 = (float)$ia5r;
            }
        }else{
            if((float)$ia5 >= (float)$ia5r){
                $xnota5 = (float)$ia5;
            }else{
                $xnota5 = (float)$ia5r;
            }
        }

        if(!isset($recbimestre)){
            $recbimestre = null;
        }else{
            $recbimestre = (float)$recbimestre;
        }

        
        
        if(!isset($xnota1) && !isset($xnota2) && !isset($xnota3) && !isset($xnota4) && !isset($xnota5)){            
            $notarf = null;
        }else{
            $notarf = $xnota1 + $xnota2 + $xnota3 + $xnota4 + $xnota5;    
        }
			
        if(isset($notarf)){
            $rf = (float)$notarf;
            if($rf > 10){
                $rf = 10;
            }
            if($rf < 0 ){
                $rf = 0;
            }    
        }else{
            $rf = null;
        }
        
        



        $ia1 = (!isset($ia1)) ? "null" : $ia1;
        $ia1r = (!isset($ia1r)) ? "null" : $ia1r;
        $ia2 = (!isset($ia2)) ? "null" : $ia2;
        $ia2r =(!isset($ia2r)) ? "null" : $ia2r;
        $ia3 = (!isset($ia3)) ? "null" : $ia3;
        $ia3r = (!isset($ia3r)) ? "null" : $ia3r;
        $ia4 = (!isset($ia4)) ? "null" : $ia4;
        $ia4r = (!isset($ia4r)) ? "null" : $ia4r;
        $ia5 = (!isset($ia5)) ? "null" : $ia5;
        $ia5r = (!isset($ia5r)) ? "null" : $ia5r;
        $rf = (!isset($rf)) ? "null" : $rf;
        $recbimestre = (!isset($recbimestre)) ? "null" : $recbimestre;

        if(isset($recbimestre) && $recbimestre > 0){
            if($recbimestre > $rf){
                $xmediafinal = $recbimestre;
            }else{
                $xmediafinal = $rf;
            }
        }else{
            $xmediafinal = $rf;
        }

        $xmediafinal = (!isset($xmediafinal)) ? "null" : $xmediafinal;
        

        $verifica = buscaNotas($codigoaluno, $codigomatricula, $calendario, $turma, $etapa, $periodo, $disciplina);        
        $seqdiario = buscaSeqDiarioAtual($codigomatricula, $codregencia, $etapa, $turma, $nperiodo);
        $hoje = date("Y-m-d");
         
        if($verifica){
            //DESCOMENTAR AQUI
            pg_query("UPDATE dclanotas SET ia1 = {$ia1}, ia1r = {$ia1r}, ia2 = {$ia2}, ia2r = {$ia2r}, ia3 = {$ia3}, ia3r = {$ia3r}, ia4 = {$ia4}, ia4r = {$ia4r}, ia5 = {$ia5}, ia5r = {$ia5r}, rf = {$rf}, recbimestre = {$recbimestre}, mediafinal = {$xmediafinal} WHERE id = {$verifica['id']}");
            //echo "UPDATE dclanotas SET ia1 = {$ia1}, ia1r = {$ia1r}, ia2 = {$ia2}, ia2r = {$ia2r}, ia3 = {$ia3}, ia3r = {$ia3r}, ia4 = {$ia4}, ia4r = {$ia4r}, ia5 = {$ia5}, ia5r = {$ia5r}, rf = {$rf}, recbimestre = {$recbimestre}, mediafinal = {$xmediafinal} WHERE id = {$verifica['id']}"; echo "<br>";

            //Auditoria
            //pg_query("INSERT INTO auditorialancamentos(regencia, seqdiario, notaanterior, notaatual, data, professor, acao) VALUES({$codregencia}, {$seqdiario}, {$ultimanota}, {$rf}, '{$hoje}', '{$nprofessor}', 'atualiza')");
            
            

        }else{
            if(isset($rf)){
                //DESCOMENTAR AQUI
                pg_query("INSERT INTO dclanotas(codigoaluno, codigomatricula, calendario, turma, etapa, periodo, disciplina, ia1, ia1r, ia2, ia2r, ia3, ia3r, ia4, ia4r, ia5, ia5r, rf, recbimestre, mediafinal) VALUES({$codigoaluno}, {$codigomatricula}, {$calendario}, {$turma}, {$etapa}, {$periodo}, {$disciplina}, {$ia1}, {$ia1r}, {$ia2}, {$ia2r}, {$ia3}, {$ia3r}, {$ia4}, {$ia4r}, {$ia5}, {$ia5r}, {$rf}, {$recbimestre}, {$xmediafinal})");
                //echo "INSERT INTO dclanotas(codigoaluno, codigomatricula, calendario, turma, etapa, periodo, disciplina, ia1, ia1r, ia2, ia2r, ia3, ia3r, ia4, ia4r, ia5, ia5r, rf, recbimestre, mediafinal) VALUES({$codigoaluno}, {$codigomatricula}, {$calendario}, {$turma}, {$etapa}, {$periodo}, {$disciplina}, {$ia1}, {$ia1r}, {$ia2}, {$ia2r}, {$ia3}, {$ia3r}, {$ia4}, {$ia4r}, {$ia5}, {$ia5r}, {$rf}, {$recbimestre}, {$xmediafinal})"; echo "<br>";

                //pg_query("INSERT INTO auditorialancamentos(regencia, seqdiario, notaanterior, notaatual, data, professor, acao) VALUES({$codregencia}, {$seqdiario}, {$ultimanota}, {$rf}, '{$hoje}', '{$nprofessor}', 'insere' )");
            
            }
        }
        //echo "<hr>";
            
        $i++;
    }//foreach    
    echo "<script>alert('Notas salvas.')</script>";
}//if do Salvar Notas de Avaliações


    
if($_POST["lancadiario"]){
    //var_dump($nperiodo);
    //testa($_POST);
    //die("Confere diário");
    $alunos = $_POST["codaluno"];
    $i = 0;
    
    if(isset($_POST["recuperacao"]) && $_POST["recuperacao"]){
        
        foreach($alunos as $aluno){            
            $codigoaluno = $aluno;
            $codigomatricula = $mataluno[$i];
        
            $notasdc = buscaNotas($codigoaluno, $codigomatricula, $calendario, $turma, $etapa, $periodo, $disciplina);            
            $iddcnota = $notasdc["id"];
            $seqdiario = buscaSeqDiarioAtual($codigomatricula, $codregencia, $etapa, $turma, $nperiodo);
        
            $coddiario = buscaCodigoDiarioAtual($codigomatricula, $codregencia, $etapa, $turma);            
            $notaperiodoatual = ($qtdperiodos == 3) ? buscaNotaPeriodoAtualTrimestre($coddiario, $nperiodo) : buscaNotaPeriodoAtualBimestre($coddiario, $nperiodo);
            $notalancamento = (isset($notasdc["mediafinal"])) ? $notasdc["mediafinal"] : null;
            

            $codigoaval = voltaCodigoAvalFinal($coddiario);
            $codigonf = voltaCodigoNF($coddiario);

            /*if(isset($notalancamento)){                
                    $ultimanota = (float)$notasdc["ultimanota"];
                    $diferenca = $ultimanota - (float)$notaperiodoatual;
                    $novanota = (float)$notalancamento - $diferenca;
                    if($novanota > 10){$novanota = 10;}
                    if($novanota < 0){$novanota = 0;}
                    //pg_query("UPDATE diarioavaliacao SET ed72_i_valornota = {$novanota} WHERE ed72_i_codigo = {$codigoaval}");
                    echo "3 - UPDATE diarioavaliacao SET ed72_i_valornota = {$novanota} WHERE ed72_i_codigo = {$codigoaval}"; echo "<br>";
                
                //pg_query("UPDATE dclanotas SET ultimanota = {$notalancamento} WHERE id = {$iddcnota}");
                echo "4 - UPDATE dclanotas SET ultimanota = {$notalancamento} WHERE id = {$iddcnota}"; echo "<br>";
            }*/
            
            pg_query("UPDATE diarioavaliacao SET ed72_i_valornota = {$notalancamento} WHERE ed72_i_codigo = {$codigoaval}");
            //echo "UPDATE diarioavaliacao SET ed72_i_valornota = {$notalancamento} WHERE ed72_i_codigo = {$codigoaval}"; echo "<br>";
            
            pg_query("UPDATE dclanotas SET ultimanota = {$notalancamento} WHERE id = {$iddcnota}");
            //echo "UPDATE dclanotas SET ultimanota = {$notalancamento} WHERE id = {$iddcnota}"; echo "<br>";

            //Pega média: nota avalfinal + nf   divide por 2
            $mediarec = voltaMediaRec($coddiario);
            
            
            
            //Atualiza média
            pg_query("UPDATE diarioresultado SET ed73_i_valornota = {$mediarec}, ed73_valorreal = {$mediarec} WHERE ed73_i_codigo = {$codigonf}");
            //echo "5UPDATE diarioresultado SET ed73_i_valornota = {$mediarec}, ed73_valorreal = {$mediarec} WHERE ed73_i_codigo = {$codigonf}";

            if($mediarec >= 5){
                pg_query("UPDATE diariofinal SET ed74_c_valoraprov = {$mediarec}, ed74_c_resultadofinal = 'A' WHERE ed74_i_diario = {$coddiario}");
                //echo "6UPDATE diariofinal SET ed74_c_valoraprov = {$mediarec}, ed74_c_resultadofinal = 'A' WHERE ed74_i_diario = {$coddiario}";
            }else{
                pg_query("UPDATE diariofinal SET ed74_c_valoraprov = {$mediarec}, ed74_c_resultadofinal = 'F' WHERE ed74_i_diario = {$coddiario}");
                //echo "7UPDATE diariofinal SET ed74_c_valoraprov = {$mediarec}, ed74_c_resultadofinal = 'F' WHERE ed74_i_diario = {$coddiario}";
            }
            $i++;
            
        } //fim do foreach
        
        
        

    }else{
        foreach($alunos as $aluno){
        //if($aluno != 122655){continue;}
            $codigoaluno = $aluno;
            $codigomatricula = $mataluno[$i];
        
            $notasdc = buscaNotas($codigoaluno, $codigomatricula, $calendario, $turma, $etapa, $periodo, $disciplina);
            $iddcnota = $notasdc["id"];
            $seqdiario = buscaSeqDiarioAtual($codigomatricula, $codregencia, $etapa, $turma, $nperiodo);
        
            $coddiario = buscaCodigoDiarioAtual($codigomatricula, $codregencia, $etapa, $turma);
            $notaperiodoatual = ($qtdperiodos == 3) ? buscaNotaPeriodoAtualTrimestre($coddiario, $nperiodo) : buscaNotaPeriodoAtualBimestre($coddiario, $nperiodo);
            $notalancamento = (isset($notasdc["mediafinal"])) ? $notasdc["mediafinal"] : null;
        
            //Lança nota das avaliações no diário de turma        
            //echo $codigoaluno; echo " - "; echo $seqdiario; echo " - "; echo $coddiario; echo "<br>";
            if(isset($notalancamento)){
                if(!isset($notaperiodoatual) || $notaperiodoatual == 0){
                    pg_query("UPDATE diarioavaliacao SET ed72_i_valornota = {$notalancamento} WHERE ed72_i_codigo = {$seqdiario}");
                    //echo "1 - UPDATE diarioavaliacao SET ed72_i_valornota = {$notalancamento} WHERE ed72_i_codigo = {$seqdiario}"; echo "<br>";
                }elseif((float)$notasdc["ultimanota"] == (float)$notaperiodoatual){
                    pg_query("UPDATE diarioavaliacao SET ed72_i_valornota = {$notalancamento} WHERE ed72_i_codigo = {$seqdiario}");
                    //echo "2 - UPDATE diarioavaliacao SET ed72_i_valornota = {$notalancamento} WHERE ed72_i_codigo = {$seqdiario}"; echo "<br>";
                }else{
                    $ultimanota = (float)$notasdc["ultimanota"];
                    $diferenca = $ultimanota - (float)$notaperiodoatual;
                    $novanota = (float)$notalancamento - $diferenca;
                    if($novanota > 10){$novanota = 10;}
                    if($novanota < 0){$novanota = 0;}
                    pg_query("UPDATE diarioavaliacao SET ed72_i_valornota = {$novanota} WHERE ed72_i_codigo = {$seqdiario}");
                    //echo "3 - UPDATE diarioavaliacao SET ed72_i_valornota = {$novanota} WHERE ed72_i_codigo = {$seqdiario}"; echo "<br>";
                }
                pg_query("UPDATE dclanotas SET ultimanota = {$notalancamento} WHERE id = {$iddcnota}");
                //echo "4 - UPDATE dclanotas SET ultimanota = {$notalancamento} WHERE id = {$iddcnota}"; echo "<br>";

                
                //pg_query("INSERT INTO auditorialancamentos(regencia, seqdiario, notaanterior, notaatual, data, professor, acao) VALUES({$codregencia}, {$seqdiario}, {$ultimanota}, {$rf}, '{$hoje}', '{$nprofessor}', 'diario' )");
                //echo "INSERT INTO auditorialancamentos(regencia, seqdiario, notaanterior, notaatual, data, professor, acao) VALUES({$codregencia}, {$seqdiario}, {$ultimanota}, {$rf}, '{$hoje}', '{$nprofessor}', 'diario' )"; echo "<br>";
                
            }

            //Ajusta Média Final
            if($qtdperiodos == 3){
                $mediadisciplina = buscaMediaTrimestre($coddiario);
                pg_query("UPDATE diarioresultado SET ed73_i_valornota = {$mediadisciplina}, ed73_valorreal = {$mediadisciplina} WHERE ed73_i_diario = {$coddiario}");

                if($nperiodo == "3º TRIMESTRE"){
                    $resultadofinal = buscaResultadoFinal($coddiario);
                
                    if($mediadisciplina >= 5 && trim($resultadofinal["ed74_c_resultadofreq"]) == "A"){
                        pg_query("UPDATE diariofinal SET ed74_c_valoraprov = {$mediadisciplina}, ed74_c_resultadofinal = 'A' WHERE ed74_i_diario = {$coddiario}");
                    }else{
                        pg_query("UPDATE diariofinal SET ed74_c_valoraprov = {$mediadisciplina}, ed74_c_resultadofinal = 'R' WHERE ed74_i_diario = {$coddiario}");
                    }
                }
            }else{
                $dadosmedia = buscaMediaBimestre($coddiario);
            
                $mediadisciplina = $dadosmedia["novamedia"];
                $somatoriodisciplina = $dadosmedia["somatorio"];

                $dadosfinais = buscaResultadosFinais($coddiario);                
            
                $coluna_ma_seq = $dadosfinais[1]["ed73_i_codigo"];
                $coluna_ma2_seq = $dadosfinais[0]["ed73_i_codigo"];
                $coluna_ma_nota = $dadosfinais[1]["ed73_i_valornota"];
                pg_query("UPDATE diarioresultado SET ed73_i_valornota = {$mediadisciplina}, ed73_valorreal = {$mediadisciplina} WHERE ed73_i_codigo = {$coluna_ma_seq}");
                pg_query("UPDATE diarioresultado SET ed73_i_valornota = {$mediadisciplina}, ed73_valorreal = {$mediadisciplina} WHERE ed73_i_codigo = {$coluna_ma2_seq}");
                //echo "1UPDATE diarioresultado SET ed73_i_valornota = {$mediadisciplina}, ed73_valorreal = {$mediadisciplina} WHERE ed73_i_codigo = {$coluna_ma_seq}"; echo "<br>";
                //echo "11UPDATE diarioresultado SET ed73_i_valornota = {$mediadisciplina}, ed73_valorreal = {$mediadisciplina} WHERE ed73_i_codigo = {$coluna_ma2_seq}"; echo "<br>";

                $coluna_rrf_seq = $dadosfinais[2]["ed73_i_codigo"];
                $coluna_rrf_nota = $dadosfinais[2]["ed73_i_valornota"];
                if(isset($coluna_rrf_seq)){
                    pg_query("UPDATE diarioresultado SET ed73_i_valornota = {$somatoriodisciplina}, ed73_valorreal = {$somatoriodisciplina} WHERE ed73_i_codigo = {$coluna_rrf_seq}");
                    //echo "2UPDATE diarioresultado SET ed73_i_valornota = {$somatoriodisciplina}, ed73_valorreal = {$somatoriodisciplina} WHERE ed73_i_codigo = {$coluna_rrf_seq}"; echo "<br>";    
                }

                $coluna_nf_seq = $dadosfinais[3]["ed73_i_codigo"];
                $coluna_nf_nota = $dadosfinais[32]["ed73_i_valornota"];
                if(isset($coluna_nf_seq)){
                    pg_query("UPDATE diarioresultado SET ed73_i_valornota = {$mediadisciplina}, ed73_valorreal = {$mediadisciplina} WHERE ed73_i_codigo = {$coluna_nf_seq}");
                    //echo "3UPDATE diarioresultado SET ed73_i_valornota = {$mediadisciplina}, ed73_valorreal = {$mediadisciplina} WHERE ed73_i_codigo = {$coluna_nf_seq}"; echo "<br>";
                }

                if($nperiodo == "4º BIMESTRE"){
                    $resultadofinal = buscaResultadoFinal($coddiario);
                
                    if($mediadisciplina >= 5 && trim($resultadofinal["ed74_c_resultadofreq"]) == "A"){
                        pg_query("UPDATE diariofinal SET ed74_c_valoraprov = {$mediadisciplina}, ed74_c_resultadofinal = 'A' WHERE ed74_i_diario = {$coddiario}");
                        //echo "4UPDATE diariofinal SET ed74_c_valoraprov = {$mediadisciplina}, ed74_c_resultadofinal = 'A' WHERE ed74_i_diario = {$coddiario}"; echo "<br>";
                    }else{
                        pg_query("UPDATE diariofinal SET ed74_c_valoraprov = {$mediadisciplina}, ed74_c_resultadofinal = 'R' WHERE ed74_i_diario = {$coddiario}");
                        //echo "5UPDATE diariofinal SET ed74_c_valoraprov = {$mediadisciplina}, ed74_c_resultadofinal = 'R' WHERE ed74_i_diario = {$coddiario}"; echo "<br>";
                    }
                }
                //echo "<hr>";
            }//bloco do cálculo de média final
            $i++;
        }//fim do foreach dos alunos
    }//fim do else da recuperação
    //die("Confere o diário avaliação - ed72_i_codigo = 1774965");

    
    //die("Parada aqui dos alunos.");
    echo "<script>alert('Notas lançadas no diário.')</script>";
}//if do Lançar Avaliações no Diário



        
        
        
    




//===========================VERSÃO FINAL
$alunos = buscaAlunosDaTurma($turma);
$nomesia = buscaTitulosIA($calendario, $turma, $etapa, $periodo, $disciplina);
$obsia = buscaobsIA($calendario, $turma, $etapa, $periodo, $disciplina);


//===========================VERSÃO FINAL



?>
<html>
  <head>
    <title>e-Cidade</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta http-equiv="Expires" CONTENT="0">
    <?
    db_app::load("scripts.js,
                  prototype.js,
                  strings.js,
                  arrays.js,
                  windowAux.widget.js,
                  datagrid.widget.js,
                  dbmessageBoard.widget.js,
                  dbcomboBox.widget.js,
                  dbtextField.widget.js,
                  datagrid/plugins/DBOrderRows.plugin.js,
                  datagrid/plugins/DBHint.plugin.js,
                  AjaxRequest.js");

    db_app::load("estilos.css,
                  grid.style.css"
                );
    ?>
    <script language='JavaScript' type='text/javascript' src='scripts/widgets/DBToggleList.widget.js'></script>
    <script language='JavaScript' type='text/javascript' src='scripts/classes/educacao/escola/ListaCalendario.classe.js'></script>
    <script language='JavaScript' type='text/javascript' src='scripts/classes/educacao/escola/ListaTurma.classe.js'></script>
    <script language='JavaScript' type='text/javascript' src='scripts/classes/educacao/escola/ListaPeriodoAvaliacao.classe.js'></script>
    <script language='JavaScript' type='text/javascript' src='scripts/classes/educacao/escola/ListaDisciplinas.classe.js'></script>

    <style>
        input[type="number"]::-webkit-outer-spin-button, input[type="number"]::-webkit-inner-spin-button {-webkit-appearance: none; margin: 0;}
        input[type="number"]{-moz-appearance: textfield;}

        .tooltip {
            display: none;
            position: absolute;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 8px;
            border-radius: 4px;
            font-size: 14px;
            z-index: 1000;
            pointer-events: none;
        }

    </style>
  </head>
  <body style='margin-top: 25px' bgcolor="#cccccc">

    
  
<div id="msgBoardAcesso" class="DBMessageBoard">
    <form method="post" action="">
        <input type="hidden" name="xcalendario" id="xcalendario" value="<?=$calendario?>">
        <input type="hidden" name="xturma" id="xturma" value="<?=$turma?>">
        <input type="hidden" name="xetapa" id="xetapa" value="<?=$etapa?>">
        <input type="hidden" name="xperiodo" id="xperiodo" value="<?=$periodo?>">
        <input type="hidden" name="xdisciplina" id="xdisciplina" value="<?=$disciplina?>">
        <input type="hidden" name="xregente" id="xregente" value="<?=$nprofessor?>">


    <table width="100%">
        <tr>
            <td id="msgBoardAcesso_title" style="font-weight: bold;">Lançamento de Avaliações - <?=$nprofessor . " - " . $nmateria?></td>
            <td style="text-align: right;" rowspan="2"></td>
        </tr>
        <tr>
            <td id="msgBoardAcesso_help" style="text-indent: 15px;">Lançamento de Avaliações da Turma <b><?=$nturma . " - " . $netapa . " - " . $nperiodo;?></b>
            </td>
        </tr>
    </table>
</div>

<div style="width: 99% !important;">
    <fieldset>
        <legend><b>Lançamento de Avaliações</b></legend>
        <div id="ctnDataGridDiarioClasse">
            <div class="gridcontainer" id="gridgridlanc">
                <div class="grid-container">
                    <div class="header-container">
                        <div class="grid-resize"><img src="imagens/espaco.gif" border="0" onclick="return false">
                            <div style="clear: both;"></div>
                        </div>
                        <table class="table-header" rel="ignore-css" id="tablegridlancheader" style="width: 1740px; table-layout: fixed;">
                            <tbody>                                
                                <tr>
                                    <td class="table_header cell" id="col1" nowrap="" title="Nº" gridcolnumber="0" style="width:2%">Nº</td>
                                    <td class="table_header cell" id="col2" nowrap="" title="Aluno" gridcolnumber="1" style="width:21%">Aluno</td>
                                    <td class="table_header cell" id="col3" nowrap="" title="IA 1" gridcolnumber="2" style="width:5%"><?=$nomesia["ia1"]?></td>
                                    <td class="table_header cell" id="col4" nowrap="" title="IA 1R" gridcolnumber="3" style="width:5%"><?=$nomesia["ia1"]?>R</td>
                                    <td class="table_header cell" id="col5" nowrap="" title="IA 2" gridcolnumber="4" style="width:5%"><?=$nomesia["ia2"]?></td>
                                    <td class="table_header cell" id="col6" nowrap="" title="IA 2R" gridcolnumber="5" style="width:5%"><?=$nomesia["ia2"]?>R</td>
                                    <td class="table_header cell" id="col7" nowrap="" title="IA 3" gridcolnumber="6" style="width:5%"><?=$nomesia["ia3"]?></td>
                                    <td class="table_header cell" id="col8" nowrap="" title="IA 3R" gridcolnumber="7" style="width:5%"><?=$nomesia["ia3"]?>R</td>
                                    <td class="table_header cell" id="col9" nowrap="" title="IA 3" gridcolnumber="8" style="width:5%"><?=$nomesia["ia4"]?></td>
                                    <td class="table_header cell" id="col10" nowrap="" title="IA 3R" gridcolnumber="9" style="width:5%"><?=$nomesia["ia4"]?>R</td>
                                    <td class="table_header cell" id="col11" nowrap="" title="IA 3" gridcolnumber="10" style="width:5%"><?=$nomesia["ia5"]?></td>
                                    <td class="table_header cell" id="col12" nowrap="" title="IA 3R" gridcolnumber="11" style="width:5%"><?=$nomesia["ia5"]?>R</td>
                                    <td class="table_header cell" id="col13" nowrap="" title="Resultado Final" gridcolnumber="12" style="width:5%">Média Parcial</td>

                                    <td class="table_header cell" id="col13" nowrap="" title="Resultado Final" gridcolnumber="13" style="width:5%">Rec. do Bimestre</td>
                                    <td class="table_header cell" id="col13" nowrap="" title="Resultado Final" gridcolnumber="14" style="width:5%">Média Final</td>
                                    <input type="hidden" name="nid" id="nid" value="<?=$nomesia["id"]?>">
                                    <input type="hidden" name="nidobs" id="nidobs" value="<?=$obsia["id"]?>">
                                </tr>                            
                            </tbody>
                        </table>
                    </div>
                    
                    <div id="body-container-gridlanc" class="body-container" style="height:334.44444444444446;">
                        <table class="table-body" id="gridlancbody" style="width: 1740px; table-layout: fixed;">
                            <tbody>

<?php $i = 0; foreach($alunos as $aluno) : ?>

<?php 
//var_dump($aluno["ed47_v_nome"]); var_dump($aluno["ed47_i_codigo"]); echo "<br>";
//if($aluno["ed47_i_codigo"] != 161907){continue;}

?>
    <?php 

    
        if($xcamporecuperacao){
            //if($aluno["ed47_i_codigo"] != 161907){continue;}
            $xcodiarioaluno = buscaCodDiarioAluno($codregencia, $aluno["ed47_i_codigo"]);
            //$somatorio = verificaRecFinal($xcodiarioaluno);
            //$somatorio = verificaNotaFinal($xcodiarioaluno);
            $somatorio = notaCorte($xcodiarioaluno);
            if($somatorio >= 5 || !$somatorio){continue;}
            ?>
            <input type="hidden" name="recuperacao" value="recuperacao">
            <?php 
            //testa($xcodiarioaluno); //450030
            //testa($somatorio); //4.7
            //if($somatorio >= 10 || !$somatorio){continue;}
        }
		
				        
		
        $notas = buscaNotas($aluno["ed47_i_codigo"], $aluno["ed60_i_codigo"], $calendario, $turma, $etapa, $periodo, $disciplina);
        //testa($notas);

        
     ?>

    <input type="hidden" name="codaluno[]" value="<?=$aluno['ed47_i_codigo']?>">
    <input type="hidden" name="mataluno[]" value="<?=$aluno['ed60_i_codigo']?>">

    <tr id="gridlancrowgridlanc0" class="normal tooltip-row" data-tooltip="<?=$aluno["ed47_v_nome"]?>" onmouseover="js_sinalizarLinhaGrid(this, true);" onmouseout="js_sinalizarLinhaGrid(this, false);" style="height: 1em; background-color: white; color: black;">
    <td class="linhagrid cell" style="width:2%;text-align:center;" id="gridlancrow0cell0"><?=$aluno['ed60_i_numaluno']?></td>
    <td class="linhagrid cell tdnome" style="width:21%;text-align:left;" id="gridlancrow0cell1"><span><?=$aluno["ed47_v_nome"]?></span></td>
	<td class="linhagrid cell" style="width:5%;text-align:left;" id="gridlancrow0cell2">
	   <input value="<?=$notas['ia1']?>" type="number" min="0" max="10" step="0.1" name="dcnota<?=$aluno['ed47_i_codigo']?>[]" id="ia11<?=$aluno['ed47_i_codigo']?>" style="width:99%" text-align="left" onkeydown='valida(event)' onkeyup='maximo(this)'>
	</td>
	<td class="linhagrid cell" title="" nowrap="" style="width:5%;text-align:left;" id="gridlancrow0cell3">
		<input value="<?=$notas['ia1r']?>" type="number" min="0" max="10" step="0.1" name="dcnota<?=$aluno['ed47_i_codigo']?>[]" id="ia1r1<?=$aluno['ed47_i_codigo']?>" style="width:99%" text-align="left" onkeydown='valida(event)' onkeyup='maximo(this)'>
	</td>


	<?php if($colunas < 2) : ?>
	<td class="linhagrid cell" title="" nowrap="" style="width:5%;text-align:left;" id="gridlancrow0cell4">
		<input value="<?=$notas['ia2']?>" type="number" min="0" max="10" step="0.1" name="dcnota<?=$aluno['ed47_i_codigo']?>[]" id="ia21<?=$aluno['ed47_i_codigo']?>" style="width:99%;background-color:rgb(222, 184, 135)" text-align="left" onkeydown='valida(event)' onkeyup='maximo(this)' readonly>
	</td>
	<td class="linhagrid cell" title="" nowrap="" style="width:5%;text-align:left;" id="gridlancrow0cell5">
		<input value="<?=$notas['ia2r']?>" type="number" min="0" max="10" step="0.1" name="dcnota<?=$aluno['ed47_i_codigo']?>[]" id="ia2r1<?=$aluno['ed47_i_codigo']?>" style="width:99%;background-color:rgb(222, 184, 135)" text-align="left" onkeydown='valida(event)' onkeyup='maximo(this)' readonly>
	</td>
	<?php else : ?>
		<td class="linhagrid cell" title="" nowrap="" style="width:5%;text-align:left;" id="gridlancrow0cell4">
		<input value="<?=$notas['ia2']?>" type="number" min="0" max="10" step="0.1" name="dcnota<?=$aluno['ed47_i_codigo']?>[]" id="ia21<?=$aluno['ed47_i_codigo']?>" style="width:99%" text-align="left" onkeydown='valida(event)' onkeyup='maximo(this)'>
	</td>
	<td class="linhagrid cell" title="" nowrap="" style="width:5%;text-align:left;" id="gridlancrow0cell5">
		<input value="<?=$notas['ia2r']?>" type="number" min="0" max="10" step="0.1" name="dcnota<?=$aluno['ed47_i_codigo']?>[]" id="ia2r1<?=$aluno['ed47_i_codigo']?>" style="width:99%" text-align="left" onkeydown='valida(event)' onkeyup='maximo(this)'>
	</td>
	<?php endif; ?>


	<?php if($colunas < 3) : ?>
	<td class="linhagrid cell" title="" nowrap="" style="width:5%;text-align:left;" id="gridlancrow0cell6">
		<input value="<?=$notas['ia3']?>" type="number" min="0" max="10" step="0.1" name="dcnota<?=$aluno['ed47_i_codigo']?>[]" id="ia31<?=$aluno['ed47_i_codigo']?>" style="width:99%;background-color:rgb(222, 184, 135)" text-align="left" onkeydown='valida(event)' onkeyup='maximo(this)' readonly>
	</td>
	<td class="linhagrid cell" title="" nowrap="" style="width:5%;text-align:left;" id="gridlancrow0cell7">
		<input value="<?=$notas['ia3r']?>" type="number" min="0" max="10" step="0.1" name="dcnota<?=$aluno['ed47_i_codigo']?>[]" id="ia3r1<?=$aluno['ed47_i_codigo']?>" style="width:99%;background-color:rgb(222, 184, 135)" text-align="left" onkeydown='valida(event)' onkeyup='maximo(this)' readonly>
	</td>
	<?php else : ?>
		<td class="linhagrid cell" title="" nowrap="" style="width:5%;text-align:left;" id="gridlancrow0cell6">
		<input value="<?=$notas['ia3']?>" type="number" min="0" max="10" step="0.1" name="dcnota<?=$aluno['ed47_i_codigo']?>[]" id="ia31<?=$aluno['ed47_i_codigo']?>" style="width:99%" text-align="left" onkeydown='valida(event)' onkeyup='maximo(this)'>
	</td>
	<td class="linhagrid cell" title="" nowrap="" style="width:5%;text-align:left;" id="gridlancrow0cell7">
		<input value="<?=$notas['ia3r']?>" type="number" min="0" max="10" step="0.1" name="dcnota<?=$aluno['ed47_i_codigo']?>[]" id="ia3r1<?=$aluno['ed47_i_codigo']?>" style="width:99%" text-align="left" onkeydown='valida(event)' onkeyup='maximo(this)'>
	</td>
	<?php endif; ?>

	<?php if($colunas < 4) : ?>
	<td class="linhagrid cell" title="" nowrap="" style="width:5%;text-align:left;" id="gridlancrow0cell8">
		<input value="<?=$notas['ia4']?>" type="number" min="0" max="10" step="0.1" name="dcnota<?=$aluno['ed47_i_codigo']?>[]" id="ia41<?=$aluno['ed47_i_codigo']?>" style="width:99%;background-color:rgb(222, 184, 135)" text-align="left" onkeydown='valida(event)' onkeyup='maximo(this)' readonly>
	</td>
	<td class="linhagrid cell" title="" nowrap="" style="width:5%;text-align:left;" id="gridlancrow0cell9">
		<input value="<?=$notas['ia4r']?>" type="number" min="0" max="10" step="0.1" name="dcnota<?=$aluno['ed47_i_codigo']?>[]" id="ia4r1<?=$aluno['ed47_i_codigo']?>" style="width:99%;background-color:rgb(222, 184, 135)" text-align="left" onkeydown='valida(event)' onkeyup='maximo(this)' readonly>
	</td>
	<?php else : ?>
	<td class="linhagrid cell" title="" nowrap="" style="width:5%;text-align:left;" id="gridlancrow0cell8">
		<input value="<?=$notas['ia4']?>" type="number" min="0" max="10" step="0.1" name="dcnota<?=$aluno['ed47_i_codigo']?>[]" id="ia41<?=$aluno['ed47_i_codigo']?>" style="width:99%" text-align="left" onkeydown='valida(event)' onkeyup='maximo(this)'>
	</td>
	<td class="linhagrid cell" title="" nowrap="" style="width:5%;text-align:left;" id="gridlancrow0cell9">
		<input value="<?=$notas['ia4r']?>" type="number" min="0" max="10" step="0.1" name="dcnota<?=$aluno['ed47_i_codigo']?>[]" id="ia4r1<?=$aluno['ed47_i_codigo']?>" style="width:99%" text-align="left" onkeydown='valida(event)' onkeyup='maximo(this)'>
	</td>
	<?php endif; ?>


    <?php if($colunas < 5) : ?>
    <td class="linhagrid cell" title="" nowrap="" style="width:5%;text-align:left;" id="gridlancrow0cell10">
        <input value="<?=$notas['ia5']?>" type="number" min="0" max="10" name="dcnota<?=$aluno['ed47_i_codigo']?>[]" id="ia51<?=$aluno['ed47_i_codigo']?>" style="width:99%;background-color:rgb(222, 184, 135)" text-align="left" onkeydown='valida(event)' onkeyup='maximo(this)' readonly>
    </td>
    <td class="linhagrid cell" title="" nowrap="" style="width:5%;text-align:left;" id="gridlancrow0cell11">
        <input value="<?=$notas['ia5r']?>" type="number" min="0" max="10" step="0.1" name="dcnota<?=$aluno['ed47_i_codigo']?>[]" id="ia5r1<?=$aluno['ed47_i_codigo']?>" style="width:99%;background-color:rgb(222, 184, 135)" text-align="left" onkeydown='valida(event)' onkeyup='maximo(this)' readonly>
    </td>
    <?php else : ?>
    <td class="linhagrid cell" title="" nowrap="" style="width:5%;text-align:left;" id="gridlancrow0cell10">
        <input readnoly value="<?=$notas['ia5']?>" type="number" min="0" max="10" step="0.1" name="dcnota<?=$aluno['ed47_i_codigo']?>[]" id="ia51<?=$aluno['ed47_i_codigo']?>" style="width:99%" text-align="left" onkeydown='valida(event)' onkeyup='maximo(this)'>
    </td>
    <td class="linhagrid cell" title="" nowrap="" style="width:5%;text-align:left;" id="gridlancrow0cell11">
        <input readnoly value="<?=$notas['ia5r']?>" type="number" min="0" max="10" step="0.1" name="dcnota<?=$aluno['ed47_i_codigo']?>[]" id="ia5r1<?=$aluno['ed47_i_codigo']?>" style="width:99%" text-align="left" onkeydown='valida(event)' onkeyup='maximo(this)'>
    </td>
<?php endif; ?>


<?php 




if(!isset($notas["ia1"]) || !isset($notas["ia1r"])){
    $nota1 = null;
    if(isset($notas["ia1"])){
        $nota1 = (float)$notas["ia1"];
    }
    if(isset($notas["ia1r"])){
        $nota1 = (float)$notas["ia1r"];
    }
}else{
    if((float)$notas["ia1"] >= (float)$notas["ia1r"]){
        $nota1 = (float)$notas["ia1"];
    }else{
        $nota1 = (float)$notas["ia1r"];
    }    
}

if(!isset($notas["ia2"]) || !isset($notas["ia2r"])){
    $nota2 = null;
    if(isset($notas["ia2"])){
        $nota2 = (float)$notas["ia2"];
    }
    if(isset($notas["ia2r"])){
        $nota2 = (float)$notas["ia2r"];
    }
}else{
    if((float)$notas["ia2"] >= (float)$notas["ia2r"]){
        $nota2 = (float)$notas["ia2"];
    }else{
        $nota2 = (float)$notas["ia2r"];
    }    
}

if(!isset($notas["ia3"]) || !isset($notas["ia3r"])){
    $nota3 = null;
    if(isset($notas["ia3"])){
        $nota3 = (float)$notas["ia3"];
    }
    if(isset($notas["ia3r"])){
        $nota3 = (float)$notas["ia3r"];
    }
}else{
    if((float)$notas["ia3"] >= (float)$notas["ia3r"]){
        $nota3 = (float)$notas["ia3"];
    }else{
        $nota3 = (float)$notas["ia3r"];
    }
}

if(!isset($notas["ia4"]) || !isset($notas["ia4r"])){
    $nota4 = null;
    if(isset($notas["ia4"])){
        $nota4 = (float)$notas["ia4"];
    }
    if(isset($notas["ia4r"])){
        $nota4 = (float)$notas["ia4r"];
    }
}else{
    if((float)$notas["ia4"] >= (float)$notas["ia4r"]){
        $nota4 = (float)$notas["ia4"];
    }else{
        $nota4 = (float)$notas["ia4r"];
    }
}

if(!isset($notas["ia5"]) || !isset($notas["ia5r"])){
    $nota5 = null;
    if(isset($notas["ia5"])){
        $nota5 = (float)$notas["ia5"];
    }
    if(isset($notas["ia5r"])){
        $nota5 = (float)$notas["ia5r"];
    }
}else{
    if((float)$notas["ia5"] >= (float)$notas["ia5r"]){
        $nota5 = (float)$notas["ia5"];
    }else{
        $nota5 = (float)$notas["ia5r"];
    }    
}



    if(!isset($nota1) && !isset($nota2) && !isset($nota3) && !isset($nota4) && !isset($nota5)){
        $notaf = null;
    }else{
        $notaf = $nota1 + $nota2 + $nota3 + $nota4 + $nota5;
    }
            
    if(isset($notaf)){
        if($notaf > 10){
            $notaf = 10;
        }
        if($notaf < 0){
            $notaf = 0;
        }
        $notaf = str_replace(".", ",", $notaf);
        //$notaf = (float)$notaf;
    }else{
        $notaf = null;
    }
    

    

 ?>


    <td class="linhagrid cell" title="" nowrap="" style="width:5%;text-align:center;" id="gridlancrow0cell8">
        <input readonly value="<?=$notaf;?>" type="text" min="0" max="10" step="0.1" name="dcnota<?=$aluno['ed47_i_codigo']?>[]" id="rf1<?=$aluno['ed47_i_codigo']?>" style="width:99%" text-align="left">
    </td>

    <?php if($notaf < 5 && !empty($notaf)) : ?>
        <td class="linhagrid cell" title="" nowrap="" style="width:5%;text-align:center;" id="gridlancrow0cell9">
        <input value="<?=$notas["recbimestre"];?>" type="number" min="0" max="10" step="0.1" name="dcnota<?=$aluno['ed47_i_codigo']?>[]" id="rb<?=$aluno['ed47_i_codigo']?>" style="width:99%" text-align="left">
    </td>    

    <?php else: ?>
        <td class="linhagrid cell" title="" nowrap="" style="width:5%;text-align:center;" id="gridlancrow0cell9">
        <input readonly value="<?=$notas["recbimestre"];?>" type="number" min="0" max="10" step="0.1" name="dcnota<?=$aluno['ed47_i_codigo']?>[]" id="rb<?=$aluno['ed47_i_codigo']?>" style="width:99%" text-align="left">
    </td>    

    <?php endif; ?>

    


    <td class="linhagrid cell" title="" nowrap="" style="width:5%;text-align:center;" id="gridlancrow0cell10">
        <input readonly value="<?=$notas["mediafinal"];?>" type="text" min="0" max="10" step="0.1" name="dcnota<?=$aluno['ed47_i_codigo']?>[]" id="nmf<?=$aluno['ed47_i_codigo']?>" style="width:99%" text-align="left">
    </td>
    <td class="linhagrid cell" title="" nowrap="" style="text-align:left;display: none;" id="gridlancrow0cell11">
        <input type="checkbox" value="12421" onfocus="js_sinalizarLinhaGrid(this, true)" onblur="js_sinalizarLinhaGrid(this, false)">
    </td>
</tr>
<?php $i++; endforeach; ?>
                            </tbody>
                        </table>
                        <div id="tooltip" class="tooltip" style="width: 200px;"></div>
                    </div>
                


                </div>
                <div class="footer-container">
                    <table class="table-footer" id="tablegridlancfooter" style="width: 1740px;">
                        <tbody>
                            <tr style="text-align:left;">
                                <td colspan="11">
                                    <div style="border:1px inset white;height:100%;padding:2px">
                                        <span> Total de Registros:</span>
                                        <span style="color:blue;padding:3px" id="gridlancnumrows"><?=$i;?></span>
                                        <span style="border-left:1px inset #eeeee2" id="gridlancstatus">&nbsp;</span>&nbsp;
                                        <span id="spanPersonalizado_gridlanc">&nbsp;</span>&nbsp;
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </fieldset>
</div>

<div style="text-align:center;padding-top:4px;padding-right:3px;">
<input type="submit" name="salvar" value="Salvar Notas de Avaliações">
<a style="margin: 0 10px" href="edu_lancaavalanosfinais.php"><input type="button" value="Voltar" id="btnFechar"></a>
<fieldset style="margin-top:25px"> 
<?php 
    $fimperiodo = (str_contains($nperiodo, "TRIMESTRE")) ? "TRIMESTRE" : "BIMESTRE";
?>
<legend align="center">SOMENTE APÓS A FINALIZAÇÃO DO LANÇAMENTO DE TODOS OS INSTRUMENTOS - FINAL DO <?=$fimperiodo?></legend> 
<input type="submit" name="lancadiario" value="Lançar Avaliações no Diário" id="btnlancar">
</fieldset>
</div>
<?php /* ?><div style="text-align:center;padding-top:4px;padding-right:3px;">  <div style="float:right;clear:both;font-weight:bold">    <span style="background-color:#009600;padding:2px 8px 2px 8px;">&nbsp;</span>&nbsp;Aluno na escola    <span style="background-color:#FF0000;padding:2px 8px 2px 8px;">&nbsp;</span>&nbsp;Aluno ausente na escola  </div> <?php */ ?>     
</form>
</div>
    
    <div style="width:320px;display: inline-block;">
        <fieldset>
            <legend>Nomear avaliações</legend>
            <div id="znomes" style="width:300px;">
                <p><span>IA 1:</span> <input type="text" maxlength="15" name="nia1" id="nia1" value="<?=$nomesia["ia1"]?>"></p>
                <p><span>IA 2:</span> <input type="text" maxlength="15" name="nia2" id="nia2" value="<?=$nomesia["ia2"]?>"></p>
                <p><span>IA 3:</span> <input type="text" maxlength="15" name="nia3" id="nia3" value="<?=$nomesia["ia3"]?>"></p>
                <p><span>IA 4:</span> <input type="text" maxlength="15" name="nia4" id="nia4" value="<?=$nomesia["ia4"]?>"></p>
                <p><span>IA 5:</span> <input type="text" maxlength="15" name="nia5" id="nia5" value="<?=$nomesia["ia5"]?>"></p>
                <p><span> </span></p>
            </div>
        <input type="button" name="btnznome" id="btnznome" value="Salvar nomes">
        </fieldset>
    </div>

    <div style="width:400px;display: inline-block;">
        <fieldset>
            <legend>Observações</legend>
                <?php /* ?><div style="display: inline-block;margin-left:206px;margin-top:-146px;"><?php */ ?>
                <div id="zobs">
                    <textarea name="nobs" id="nobs" rows="4" cols="50"><?=$obsia["observacao"]?></textarea>
                </div>
        <input type="button" name="btnzobs" id="btnzobs" value="Salvar Observação">
        </fieldset>
    </div>
    

    
  </body>
<script>

    
    
var btn = document.getElementById("btnznome");
var btnobs = document.getElementById("btnzobs");

btn.addEventListener("click", function(){
    var nia1 = document.getElementById("nia1").value;
    var nia2 = document.getElementById("nia2").value;
    var nia3 = document.getElementById("nia3").value;
    var nia4 = document.getElementById("nia4").value;
    var nia5 = document.getElementById("nia5").value;
    
    
    var nid = document.getElementById("nid").value;

    var nxcalendario = document.getElementById("xcalendario").value;
    var nxturma = document.getElementById("xturma").value;
    var nxetapa = document.getElementById("xetapa").value;
    var nxperiodo = document.getElementById("xperiodo").value;
    var nxdisciplina = document.getElementById("xdisciplina").value;



    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function(){
        if((xhr.readyState === 4) && (xhr.status === 200)){
            document.getElementById("znomes").innerHTML = xhr.responseText;
        }
    }
    xhr.open("GET", "edu_lancaavalgradenome.php?t1="+nia1+"&t2="+nia2+"&t3="+nia3+"&t4="+nia4+"&t5="+nia5+"&id="+nid+"&calendario="+nxcalendario+"&turma="+nxturma+"&etapa="+nxetapa+"&periodo="+nxperiodo+"&disciplina="+nxdisciplina, true);
    xhr.send(null);
});


btnobs.addEventListener("click", function(){
    var nobs = document.getElementById("nobs").value;
    nobs = nobs.split(/\r?\n|\r/).join(' ');
    
    var nid = document.getElementById("nidobs").value;

    var nxcalendario = document.getElementById("xcalendario").value;
    var nxturma = document.getElementById("xturma").value;
    var nxetapa = document.getElementById("xetapa").value;
    var nxperiodo = document.getElementById("xperiodo").value;
    var nxdisciplina = document.getElementById("xdisciplina").value;



    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function(){
        if((xhr.readyState === 4) && (xhr.status === 200)){
            document.getElementById("zobs").innerHTML = xhr.responseText;
        }
    }
    xhr.open("GET", "edu_lancaavalgradeobs.php?id="+nid+"&calendario="+nxcalendario+"&turma="+nxturma+"&etapa="+nxetapa+"&periodo="+nxperiodo+"&disciplina="+nxdisciplina+"&nobs="+nobs, true);
    xhr.send(null);
});

    function js_sinalizarLinhaGrid(oObjeto, lPintar) {

    if (oObjeto.nodeName == 'TR') {
      oLinha = oObjeto;
    }
    if (oObjeto.nodeName == 'INPUT') {
      oLinha = oObjeto.parentNode.parentNode;
    }
    var sCor      = 'white';
    var sCorFonte = 'black';
    if (lPintar) {

       sCor      = '#2C7AFE';
       sCorFonte = 'white';
    }
    oLinha.style.backgroundColor = sCor;
    oLinha.style.color           = sCorFonte;
  }

  function valida(event){
    const key = event.key;  
    const valor = event.target.value;

    if (key === "Backspace" || key === "Delete" || key === "ArrowLeft" || key === "ArrowRight" || key === "Tab") {
        return;  // Não faz nada e permite o comportamento padrão
      }

     if (key === '.') {
        alert("Não é permitido o ponto (.). Use vírgula (,)!");
        event.preventDefault();  // Impede que o ponto seja inserido
      }

      if (!/^[0-9,]$/.test(key)) {
        event.preventDefault();  // Impede a digitação de algo que não seja número ou vírgula
      }
      /*
    if (valor.includes('.')) {
        alert("Não é permitido o ponto (.) neste campo!");
        event.target.value = valor.replace('.', ',');
    }*/
    /*
    if (!/^[0-9,]$/.test(key)) {
        event.preventDefault();
    }*/
  }

function maximo(el) {
  if (el.value != "") {
    if (parseInt(el.value) < parseInt(el.min)) {
      el.value = el.min;
    }
    if (parseInt(el.value) > parseInt(el.max)) {
      el.value = el.max;
    }
  }
}


const rows = document.querySelectorAll('.tooltip-row');
const tooltip = document.getElementById('tooltip');

rows.forEach(row => {
  row.addEventListener('mouseover', function(event) {
    const tooltipText = event.target.closest('tr').getAttribute('data-tooltip');
    tooltip.textContent = tooltipText;
    tooltip.style.display = 'block';
    tooltip.style.left = `${event.pageX + 10}px`;
    tooltip.style.top = `${event.pageY + 10}px`;
  });

  row.addEventListener('mouseout', function() {
    tooltip.style.display = 'none';
  });
});

</script>  
</html>

<? db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit")); ?>