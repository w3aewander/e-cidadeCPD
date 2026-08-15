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

function buscaAlunos($turma){
    $sql = pg_query("SELECT aluno.ed47_v_nome, aluno.ed47_i_codigo, matricula.ed60_i_numaluno, matricula.ed60_c_situacao, matricula.ed60_c_concluida, matricula.ed60_c_rfanterior, matricula.ed60_i_turmaant, matricula.ed60_d_datamatricula, matricula.ed60_matricula, matricula.ed60_i_codigo, matricula.ed60_c_parecer, ed60_tipoingresso, serie.ed11_c_descr, to_char(alunotransfturma.ed69_d_datatransf,'DD/MM/YYYY') as datasaida, (select array_to_string(array_accum(ed336_turnoreferente), ',') from matriculaturnoreferente inner join matricula as matturno on matturno.ed60_i_codigo = matriculaturnoreferente.ed337_matricula and matturno.ed60_i_turma = turma.ed57_i_codigo inner join turmaturnoreferente on turmaturnoreferente.ed336_codigo = matriculaturnoreferente.ed337_turmaturnoreferente and turmaturnoreferente.ed336_turma = turma.ed57_i_codigo where matturno.ed60_i_codigo = matricula.ed60_i_codigo) as turnoreferente, matricula.ed60_d_datasaida from matricula inner join aluno on aluno.ed47_i_codigo = matricula.ed60_i_aluno inner join turma on turma.ed57_i_codigo = matricula.ed60_i_turma inner join matriculaserie on matriculaserie.ed221_i_matricula = matricula.ed60_i_codigo inner join serie on serie.ed11_i_codigo = matriculaserie.ed221_i_serie inner join serieregimemat on serieregimemat.ed223_i_serie = serie.ed11_i_codigo inner join turmaserieregimemat on turmaserieregimemat.ed220_i_serieregimemat = serieregimemat.ed223_i_codigo and turmaserieregimemat.ed220_i_turma = matricula.ed60_i_turma left join turma as turmaant on turmaant.ed57_i_codigo = matricula.ed60_i_turmaant left join alunoprimat on alunoprimat.ed76_i_aluno = aluno.ed47_i_codigo left join alunotransfturma on alunotransfturma.ed69_i_matricula = matricula.ed60_i_codigo where matriculaserie.ed221_c_origem = 'S' AND ed60_i_turma = {$turma} and ed60_c_situacao <> 'TROCA DE TURMA' order by matricula.ed60_i_numaluno, to_ascii(ed47_v_nome)");
    $resultado = pg_fetch_all($sql);
    return $resultado;
}



function buscaNotasRRS($aluno, $matricula, $calendario, $turma, $etapa, $disciplina, $periodo){
    $sql = "
            SELECT 
            sum(rf) as notaatual 
            FROM 
            dclanotas 
            WHERE 
            codigoaluno = {$aluno} 
            AND 
            codigomatricula = {$matricula} 
            AND 
            calendario = {$calendario} 
            AND 
            turma = {$turma} 
            AND
            periodo <= {$periodo}
            AND
            etapa = {$etapa} 
            AND 
            disciplina = {$disciplina}";

    $resultRRS = db_query($sql);
    $resultado = db_utils::fieldsMemory($resultRRS,0);
    return $resultado->notaatual;
}

function buscaNotas($aluno, $matricula, $calendario, $turma, $etapa, $periodo, $disciplina){
    $sql = pg_query("SELECT * FROM dclanotas WHERE codigoaluno = {$aluno} AND codigomatricula = {$matricula} AND calendario = {$calendario} AND turma = {$turma} AND etapa = {$etapa} AND periodo = {$periodo} AND disciplina = {$disciplina}");
    $resultado = pg_fetch_all($sql);
    return $resultado[0];
}



        function buscaRegencia($turma, $disciplina){
            $sql = pg_query("SELECT ed59_i_codigo FROM regencia WHERE ed59_i_turma = {$turma} AND ed59_i_disciplina = {$disciplina}");
            $resultado = pg_fetch_all($sql);
            return $resultado[0]["ed59_i_codigo"];
        }

        function buscaCodDiario($codaluno, $codregencia, $periodo, $disciplina,$calendario){

            $descrCalendario = pg_result(db_query("SELECT ed52_c_descr from calendario where ed52_i_codigo = {$calendario}"),0,0);
            $sql = pg_query("SELECT ed72_i_codigo FROM diario INNER JOIN diarioavaliacao ON ed95_i_codigo = ed72_i_diario INNER JOIN feriado ON ed54_i_calendario = ed95_i_calendario WHERE ed95_i_aluno = {$codaluno} AND ed95_i_regencia = {$codregencia} AND ed72_i_procavaliacao = {$periodo} LIMIT 1");            
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

        function buscaNotaDiarioAtual($codiario){            
            $sql = pg_query("SELECT ed72_i_valornota FROM diarioavaliacao WHERE ed72_i_codigo = {$codiario}");
            $resultado = pg_fetch_all($sql);            
            return ($resultado[0]["ed72_i_valornota"]) ? $resultado[0]["ed72_i_valornota"] : 0;
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

        function buscaColunas($escola){
            $sql = pg_query("SELECT colunas FROM paramcoluna WHERE escola = {$escola}");
            $resultado = pg_fetch_all($sql);
            return $resultado[0]["colunas"];
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
            //$sql = pg_query("SELECT sum(ed72_i_valornota) as nota from diarioavaliacao inner join procavaliacao on ed41_i_codigo = ed72_i_procavaliacao left join pareceraval on ed93_i_diarioavaliacao = ed72_i_codigo left join abonofalta on ed80_i_diarioavaliacao = ed72_i_codigo INNER JOIN periodoavaliacao ON ed41_i_periodoavaliacao = ed09_i_codigo where ed72_i_diario = {$coddiario} AND ed09_i_codigo in(3,4)");
            $sql = pg_query("SELECT ed72_i_valornota, ed09_i_codigo from diarioavaliacao inner join procavaliacao on ed41_i_codigo = ed72_i_procavaliacao left join pareceraval on ed93_i_diarioavaliacao = ed72_i_codigo left join abonofalta on ed80_i_diarioavaliacao = ed72_i_codigo INNER JOIN periodoavaliacao ON ed41_i_periodoavaliacao = ed09_i_codigo where ed72_i_diario = {$coddiario} AND ed09_i_codigo IN(1,2,3,4,9) ORDER BY ed09_i_codigo");
                $resultado = pg_fetch_all($sql);
                $n1 = $resultado[0]["ed72_i_valornota"];
                $n2 = $resultado[1]["ed72_i_valornota"];
                $n3 = $resultado[2]["ed72_i_valornota"];
                $n4 = $resultado[3]["ed72_i_valornota"];
                $nrec = $resultado[4]["ed72_i_valornota"];

                if($n1 > $n2){
                    $n2 = $nrec;
                }else{
                    $n1 = $nrec;
                }

                $soma = $n1 + $n2 + $n3 + $n4;
                $media = $soma / 4;
                $novamedia = sprintf("%.1f", floor($media * 10) / 10);
                $novamedia = (float)$novamedia;
                return $novamedia;
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

                if($n1 > $n2){
                    $n2 = $nrec;
                }else{
                    $n1 = $nrec;
                }

                $soma = $n1 + $n2 + $n3 + $n4;
                $media = $soma / 4;
                $mediafinal = ($media + $nrec2) / 2;

                $novamedia = sprintf("%.1f", floor($mediafinal * 10) / 10);
                $novamedia = (float)$novamedia;
                return $novamedia;
        }

        function verificaNomePeriodo($codigo){
            $sql = pg_query("SELECT ed09_c_descr FROM periodoavaliacao INNER JOIN procavaliacao ON ed09_i_codigo = ed41_i_periodoavaliacao WHERE ed41_i_codigo = {$codigo};");
            $resultado = pg_fetch_all($sql);
            return $resultado[0]["ed09_c_descr"];
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

        
        function verificaTela2($calendario, $disciplina){
            $sql = pg_query("SELECT ed52_c_descr FROM calendario WHERE ed52_i_codigo = {$calendario}");
            $resultado1 = pg_fetch_all($sql);
            $calendariox = trim($resultado1[0]["ed52_c_descr"]);

            $sql2 = pg_query("SELECT ed232_c_descr FROM caddisciplina INNER JOIN disciplina ON ed12_i_caddisciplina = ed232_i_codigo WHERE ed12_i_codigo = {$disciplina}");
            $resultado2 = pg_fetch_all($sql2);
            $disciplinax = trim($resultado2[0]["ed232_c_descr"]);

            if($calendariox == "EJA ANOS FINAIS" || $calendariox == "EJA FINAIS 2024"){
                if($disciplinax == "MATEMÁTICA"){
                    return "eja1m";
                }elseif($disciplinax == "LINGUA PORTUGUESA"){
                    return "eja1p";
                }else{
                    return "eja2";
                }
            }

            if($calendariox == "EN FUN ANOS FINAIS" || $calendariox == "ANOS FINAIS 2024"){
                if($disciplinax == "MATEMÁTICA"){
                    return "af1";
                }else{
                    return "af2";
                }
            }
            return false;
        }

testa($_GET);


$calendario = $_GET["xcalendario"];
$turma      = $_GET["xturma"];
$etapa      = $_GET["xetapa"];
$periodo    = $_GET["xperiodo"];
$disciplina = $_GET["xdisciplina"];
$nprofessor = $_GET["xregente"];



$nmateria   = buscaDisciplina($disciplina);
$nturma     = buscaTurma($turma);
$netapa     = buscaEtapa($etapa);
$nperiodo   = verificaNomePeriodo($periodo);



$recuperacao = verificaRecuperacao($periodo);
$xcamporecuperacao = ((int)$recuperacao["ed09_i_codigo"] == 9) ? "recsemestral" : "recfinal";

//$colunas = buscaColunas(db_getsession("DB_coddepto"));


$xcodregencia = buscaRegencia($turma, $disciplina);



if(isset($_POST["salvar"])){
        testa($_POST); 
        die("Confere a bagaça");
        $alunos = $_POST["codaluno"];
        $i = 0;
        $na = 1;

        foreach($alunos as $aluno){
            $codigoaluno = $aluno;
            $codigomatricula = $mataluno[$i];
            
            $ia1  = (float)$_POST["dcnota".$codigoaluno][0];
            $ia1r = (float)$_POST["dcnota".$codigoaluno][1];
            $ia2  = (float)$_POST["dcnota".$codigoaluno][2];
            $ia2r = (float)$_POST["dcnota".$codigoaluno][3];
            $ia3  = (float)$_POST["dcnota".$codigoaluno][4];
            $ia4 = (float)$_POST["dcnota".$codigoaluno][5];
            $ia5  = (float)$_POST["dcnota".$codigoaluno][6];
            $ia6 = (float)$_POST["dcnota".$codigoaluno][7];
            $ia6r  = (float)$_POST["dcnota".$codigoaluno][8];
            $ia7 = (float)$_POST["dcnota".$codigoaluno][9];
            $ia8 = (float)$_POST["dcnota".$codigoaluno][10];
            

            if((float)$ia1 >= (float)$ia1r){
                $xnota1 = (float)$ia1;
            }else{
                $xnota1 = (float)$ia1r;
            }

            if((float)$ia2 >= (float)$ia2r){
                $xnota2 = (float)$ia2;
            }else{
                $xnota2 = (float)$ia2r;
            }
            $xnota3 = (float)$ia3;
            $xnota4 = (float)$ia4;
            $xnota5 = (float)$ia5;

            if((float)$ia6 >= (float)$ia6r){
                $xnota6 = (float)$ia6;
            }else{
                $xnota6 = (float)$ia6r;
            }
            $xnota7 = (float)$ia7;
            $xnota8 = (float)$ia8;

            $notarf = $xnota1 + $xnota2 + $xnota3 + $xnota4 + $xnota5 + $xnota6 + $xnota7 + $xnota8;
            
            $rf = (float)$notarf;
            if($rf > 10){
                $rf = 10;
            }            

            $verifica = buscaNotas($codigoaluno, $codigomatricula, $calendario, $turma, $etapa, $periodo, $disciplina);
            $notaanterior = 0;

            if($verifica){
                $notaanterior = (float)$verifica['rf'];
                //echo "UPDATE dclanotas SET ia1 = {$ia1}, ia1r = {$ia1r}, ia2 = {$ia2}, ia2r = {$ia2r}, ia3 = {$ia3}, ia3r = {$ia3r}, ia4 = {$ia4}, ia4r = {$ia4r}, ia5 = {$ia5}, ia5r = {$ia5r}, rf = {$rf} WHERE id = {$verifica['id']}"; echo "<br>";
                pg_query("UPDATE dclanotas SET ia1 = {$ia1}, ia1r = {$ia1r}, ia2 = {$ia2}, ia2r = {$ia2r}, ia3 = {$ia3}, ia3r = {$ia3r}, ia4 = {$ia4}, ia4r = {$ia4r}, ia5 = {$ia5}, ia5r = {$ia5r}, rf = {$rf} WHERE id = {$verifica['id']}");
            }else{
                //echo "INSERT INTO dclanotas(codigoaluno, codigomatricula, calendario, turma, etapa, periodo, disciplina, ia1, ia1r, ia2, ia2r, ia3, ia3r, ia4, ia4r, ia5, ia5r, rf) VALUES({$codigoaluno}, {$codigomatricula}, {$calendario}, {$turma}, {$etapa}, {$periodo}, {$disciplina}, {$ia1}, {$ia1r}, {$ia2}, {$ia2r}, {$ia3}, {$ia3r}, {$ia4}, {$ia4r}, {$ia5}, {$ia5r}, {$rf})"; echo "<br>";
                pg_query("INSERT INTO dclanotas(codigoaluno, codigomatricula, calendario, turma, etapa, periodo, disciplina, ia1, ia1r, ia2, ia2r, ia3, ia3r, ia4, ia4r, ia5, ia5r, rf) VALUES({$codigoaluno}, {$codigomatricula}, {$calendario}, {$turma}, {$etapa}, {$periodo}, {$disciplina}, {$ia1}, {$ia1r}, {$ia2}, {$ia2r}, {$ia3}, {$ia3r}, {$ia4}, {$ia4r}, {$ia5}, {$ia5r}, {$rf})");
            }
            
            $codregencia = buscaRegencia($turma, $disciplina);
            // acrescentei o calendario no buscadiario, porque não estava buscando o diario para EJA ANOS INICIAIS          
            $coddiario   = buscaCodDiario($codigoaluno, $codregencia, $periodo, $disciplina, $calendario);
            $notadiarioatual = buscaNotaDiarioAtual($coddiario);
            
            if($notadiarioatual != 0){
                $novanota = $notadiarioatual - ($notaanterior - $rf);
            }else{
                $novanota = $rf;
            }
            $notaatual = buscaNotasRRS($codigoaluno, $codigomatricula, $calendario, $turma, $etapa, $disciplina,$periodo);
            
            //echo "UPDATE diarioavaliacao SET ed72_i_valornota = {$novanota} WHERE ed72_i_codigo = {$coddiario}"; echo "<br>"; //echo "<hr>";
            pg_query("UPDATE diarioavaliacao SET ed72_i_valornota = {$novanota} WHERE ed72_i_codigo = {$coddiario}");
            
            $medias            = buscaMediaAnosFinais($codigoaluno, $codregencia); //4,8 
            $cmai              = buscaCodigoMAnosFinais($codigoaluno, $disciplina, $turma);
            $totalateentao     = buscaTotalAteEntao($cmai[1]["ed73_i_codigo"]);                
            $somatorioateentao = $notaatual;            

            $novototal = $totalateentao - (float)$notadiarioatual + $novanota;
            $novamedia = buscaNotasRRS($codigoaluno, $codigomatricula, $calendario, $turma, $etapa, $disciplina,64);
            $novamedia = ajustaMedia($novamedia);
            $novosomatorio = buscaNotasRRS($codigoaluno, $codigomatricula, $calendario, $turma, $etapa, $disciplina,$periodo);
            $resultadofinal = ($novamedia >= 5) ? "A" : "R";

            pg_query("UPDATE diarioresultado SET ed73_i_valornota = {$novosomatorio}, ed73_valorreal = {$novosomatorio} where ed73_i_codigo = {$cmai[3]['ed73_i_codigo']}");

            pg_query("UPDATE diarioresultado SET ed73_i_valornota = {$novamedia}, ed73_valorreal = {$novamedia} where ed73_i_codigo = {$cmai[2]['ed73_i_codigo']}");

            $novamedia2 = buscaNotasRRS($codigoaluno, $codigomatricula, $calendario, $turma, $etapa, $disciplina,$periodo);
            pg_query("UPDATE diarioresultado SET ed73_i_valornota = {$novamedia2}, ed73_valorreal = {$novamedia2} where ed73_i_codigo = {$cmai[0]['ed73_i_codigo']}");
                    
            $novototal = buscaNotasRRS($codigoaluno, $codigomatricula, $calendario, $turma, $etapa, $disciplina,$periodo);
            pg_query("UPDATE diarioresultado SET ed73_i_valornota = {$novototal}, ed73_valorreal = {$novototal} where ed73_i_codigo = {$cmai[1]['ed73_i_codigo']}");

            $cfai = buscaCodigoRAnosFinais($codigoaluno, $disciplina, $turma);
            pg_query("UPDATE diariofinal SET ed74_c_valoraprov = {$novamedia2}, ed74_c_resultadofinal = '{$resultadofinal}' WHERE ed74_i_codigo = {$cfai}");
            
            $i++;
            
        }//foreach
        
        echo "<script>alert('Alterações salvas.')</script>";
}
$alunos = buscaAlunos($turma);

?>
<html>
  <head>
    <title>Microsist</title>
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
         input::-webkit-outer-spin-button,
            input::-webkit-inner-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }

            input[type="number"] {
                -moz-appearance: textfield;
            }
    </style>
  </head>
  <body style='margin-top: 25px' bgcolor="#cccccc">
  
<div id="msgBoardAcesso" class="DBMessageBoard">
    <form method="post" action="">
        <input type="hidden" name="xcalendario" value="<?=$calendario?>">
        <input type="hidden" name="xturma" value="<?=$turma?>">
        <input type="hidden" name="xetapa" value="<?=$etapa?>">
        <input type="hidden" name="xperiodo" value="<?=$periodo?>">
        <input type="hidden" name="xdisciplina" value="<?=$disciplina?>">

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
                        <table>
                            <div>
                                <span style="display: inline-block;font-size:30px;width:35%;margin-left:560px">Álgebra</span>
                                <span style="display: inline-block;font-size:30px">Geometria</span>
                            </div>
                        </table>
                        <table class="table-header" rel="ignore-css" id="tablegridlancheader" style="width: 1740px; table-layout: fixed;">
                            <tbody>
                                <tr>
                                    <td class="table_header cell" id="col1" nowrap="" title="Nº" gridcolnumber="0" style="width:2%">Nº</td>
                                    <td class="table_header cell" id="col2" nowrap="" title="Aluno" gridcolnumber="1" style="width:12%">Aluno</td>
                                    <td class="table_header cell" id="col3" nowrap="" title="IA 1" gridcolnumber="2" style="width:5%">IA1</td>
                                    <td class="table_header cell" id="col4" nowrap="" title="IA 1R" gridcolnumber="3" style="width:5%">IA1 R</td>
                                    <td class="table_header cell" id="col5" nowrap="" title="IA 2" gridcolnumber="4" style="width:5%">IA2</td>
                                    <td class="table_header cell" id="col6" nowrap="" title="IA 2R" gridcolnumber="5" style="width:5%">IA2 R</td>
                                    <td class="table_header cell" id="col7" nowrap="" title="IA 3" gridcolnumber="6" style="width:5%">Pesquisa</td>
                                    <td class="table_header cell" id="col8" nowrap="" title="IA 3R" gridcolnumber="7" style="width:5%">Trabalho</td>
                                    <td class="table_header cell" id="col9" nowrap="" title="IA 3" gridcolnumber="8" style="width:5%">Exercício</td>
                                    <td class="table_header cell" id="col10" nowrap="" title="Resultado Final" gridcolnumber="12" style="width:7%">Resultado Parcial 1</td>

                                    <td class="table_header cell" id="col11" nowrap="" title="IA 3R" gridcolnumber="9" style="width:5%">IA3</td>
                                    <td class="table_header cell" id="col12" nowrap="" title="IA 3" gridcolnumber="10" style="width:5%">IA3 R</td>
                                    <td class="table_header cell" id="col13" nowrap="" title="IA 3R" gridcolnumber="11" style="width:5%">IA2</td>
                                    <td class="table_header cell" id="col14" nowrap="" title="IA 3R" gridcolnumber="11" style="width:5%">Trabalho</td>
                                    <td class="table_header cell" id="col15" nowrap="" title="Resultado Final" gridcolnumber="12" style="width:7%">Resultado Parcial 2</td>

                                    <td class="table_header cell" id="col16" nowrap="" title="Resultado Final" gridcolnumber="12" style="width:5%">Resultado Final</td>
                                </tr>                            
                            </tbody>
                        </table>
                    </div>
                    
                    <div id="body-container-gridlanc" class="body-container" style="height:334.44444444444446;">
                        <table class="table-body" id="gridlancbody" style="width: 1740px; table-layout: fixed;">
                            <tbody>
<?php $i = 0; foreach($alunos as $aluno) : ?>
    <?php

        $novamatricula = matriculaTransferido($aluno["ed47_i_codigo"], $aluno["ed60_i_turmaant"]);
        if($novamatricula){
            // $aluno["ed60_i_codigo"] = $novamatricula;
            // estava acontecendo um erro na linha acima, quando o aluno era transferido o codigo nesta condição estava retornando errado
            // para o caso do aluno Bernado dos santos Andrade E.M. AMARAL PEIXOTO
            // verificar no futuro se esta alteração causou outro erro
        }
        
        $notas = buscaNotas($aluno["ed47_i_codigo"], $aluno["ed60_i_codigo"], $calendario, $turma, $etapa, $periodo, $disciplina);
        
     ?>
     <input type="hidden" name="codaluno[]" value="<?=$aluno['ed47_i_codigo']?>">

<?
     if( $aluno['ed60_matricula'] == $aluno['ed60_i_codigo'])
     {
?>       
     <input type="hidden" name="mataluno[]" value="<?=$aluno['ed60_matricula']?>">
<? 
     }else{
?>
     <input type="hidden" name="mataluno[]" value="<?=$aluno['ed60_i_codigo']?>">
<?
     }   
?>   
<tr id="gridlancrowgridlanc0" class="normal" onmouseover="js_sinalizarLinhaGrid(this, true);" onmouseout="js_sinalizarLinhaGrid(this, false);" style="height: 1em; background-color: white; color: black;">
<td class="linhagrid cell" style="width:2%;text-align:center;" id="gridlancrow0cell0"><?=$aluno['ed60_i_numaluno']?></td>
<td class="linhagrid cell" style="width:12%;text-align:left;" id="gridlancrow0cell1"><span><?=$aluno["ed47_v_nome"]?></span></td>

<!--IA1-->
<td class="linhagrid cell" style="width:5%;text-align:left;" id="gridlancrow0cell2">
<input value="<?=$notas['xia1']?>" type="number" min="0" max="2" name="dcnota<?=$aluno['ed47_i_codigo']?>[]" id="xia1<?=$aluno['ed47_i_codigo']?>" style="width:99%" text-align="left" onkeypress='valida(event)' onkeyup='maximo(this)'>
</td>

<!--IA1 R-->
<td class="linhagrid cell" title="" nowrap="" style="width:5%;text-align:left;" id="gridlancrow0cell3">
<input value="<?=$notas['xia2']?>" type="number" min="0" max="2" name="dcnota<?=$aluno['ed47_i_codigo']?>[]" id="xia2<?=$aluno['ed47_i_codigo']?>" style="width:99%" text-align="left" onkeypress='valida(event)' onkeyup='maximo(this)'>
</td>

<!--IA2-->
<td class="linhagrid cell" title="" nowrap="" style="width:5%;text-align:left;" id="gridlancrow0cell4">
<input value="<?=$notas['xia3']?>" type="number" min="0" max="1" name="dcnota<?=$aluno['ed47_i_codigo']?>[]" id="xia3<?=$aluno['ed47_i_codigo']?>" style="width:99%" text-align="left" onkeypress='valida(event)' onkeyup='maximo(this)'>
</td>

<!--IA2 R-->
<td class="linhagrid cell" title="" nowrap="" style="width:5%;text-align:left;" id="gridlancrow0cell5">
<input value="<?=$notas['xia4']?>" type="number" min="0" max="1" name="dcnota<?=$aluno['ed47_i_codigo']?>[]" id="xia4<?=$aluno['ed47_i_codigo']?>" style="width:99%" text-align="left" onkeypress='valida(event)' onkeyup='maximo(this)'>
</td>

<!--IA3-->
<td class="linhagrid cell" title="" nowrap="" style="width:5%;text-align:left;" id="gridlancrow0cell6">
<input value="<?=$notas['xia5']?>" type="number" min="0" max="1" name="dcnota<?=$aluno['ed47_i_codigo']?>[]" id="xia5<?=$aluno['ed47_i_codigo']?>" style="width:99%" text-align="left" onkeypress='valida(event)' onkeyup='maximo(this)'>
</td>

<!--IA4-->
<td class="linhagrid cell" title="" nowrap="" style="width:5%;text-align:left;" id="gridlancrow0cell7">
<input value="<?=$notas['xia6']?>" type="number" min="0" max="1" name="dcnota<?=$aluno['ed47_i_codigo']?>[]" id="xia6<?=$aluno['ed47_i_codigo']?>" style="width:99%" text-align="left" onkeypress='valida(event)' onkeyup='maximo(this)'>
</td>

<!--IA5-->
<td class="linhagrid cell" title="" nowrap="" style="width:5%;text-align:left;" id="gridlancrow0cell8">
<input value="<?=$notas['xia7']?>" type="number" min="0" max="1" name="dcnota<?=$aluno['ed47_i_codigo']?>[]" id="xia7<?=$aluno['ed47_i_codigo']?>" style="width:99%" text-align="left" onkeypress='valida(event)' onkeyup='maximo(this)'>
</td>

<!--RESULTADO PARCIAL 1-->
<td class="linhagrid cell" title="" nowrap="" style="width:7%;text-align:center;" id="gridlancrow0cell9">
    <input readonly value="<?=$notap1;?>" type="text" id="rp1<?=$aluno['ed47_i_codigo']?>" style="width:99%" text-align="left">
</td>

<!--IA6-->
<td class="linhagrid cell" title="" nowrap="" style="width:5%;text-align:left;" id="gridlancrow0cell10">
    <input value="<?=$notas['xia8']?>" type="number" min="0" max="2" name="dcnota<?=$aluno['ed47_i_codigo']?>[]" id="xia8<?=$aluno['ed47_i_codigo']?>" style="width:99%" text-align="left" onkeypress='valida(event)' onkeyup='maximo(this)'>
</td>

<!--IA6 R-->
<td class="linhagrid cell" title="" nowrap="" style="width:5%;text-align:left;" id="gridlancrow0cell11">
    <input value="<?=$notas['xia9']?>" type="number" min="0" max="2" name="dcnota<?=$aluno['ed47_i_codigo']?>[]" id="xia9<?=$aluno['ed47_i_codigo']?>" style="width:99%" text-align="left" onkeypress='valida(event)' onkeyup='maximo(this)'>
</td>

<!--IA7-->
<td class="linhagrid cell" title="" nowrap="" style="width:5%;text-align:left;" id="gridlancrow0cell12">
    <input readnoly value="<?=$notas['xia10']?>" type="number" min="0" max="1" name="dcnota<?=$aluno['ed47_i_codigo']?>[]" id="xia10<?=$aluno['ed47_i_codigo']?>" style="width:99%" text-align="left" onkeypress='valida(event)' onkeyup='maximo(this)'>
</td>

<!--IA8-->
<td class="linhagrid cell" title="" nowrap="" style="width:5%;text-align:left;" id="gridlancrow0cell13">
    <input readnoly value="<?=$notas['xia11']?>" type="number" min="0" max="1" name="dcnota<?=$aluno['ed47_i_codigo']?>[]" id="xia11<?=$aluno['ed47_i_codigo']?>" style="width:99%" text-align="left" onkeypress='valida(event)' onkeyup='maximo(this)'>
</td>


<!--RESULTADO PARCIAL 2-->
<td class="linhagrid cell" title="" nowrap="" style="width:7%;text-align:center;" id="gridlancrow0cell14">
    <input readonly value="<?=$notap2;?>" type="text" id="rp2<?=$aluno['ed47_i_codigo']?>" style="width:99%" text-align="left">
</td>


<?php 

if((float)$notas["ia1"] >= (float)$notas["ia1r"]){
        $nota1 = (float)$notas["ia1"];
    }else{
        $nota1 = (float)$notas["ia1r"];
    }


    if((float)$notas["ia2"] >= (float)$notas["ia2r"]){
        $nota2 = (float)$notas["ia2"];
    }else{
        $nota2 = (float)$notas["ia2r"];
    }

    if((float)$notas["ia3"] >= (float)$notas["ia3r"]){
        $nota3 = (float)$notas["ia3"];
    }else{
        $nota3 = (float)$notas["ia3r"];
    }

    if((float)$notas["ia4"] >= (float)$notas["ia4r"]){
        $nota4 = (float)$notas["ia4"];
    }else{
        $nota4 = (float)$notas["ia4r"];
    }

    if((float)$notas["ia5"] >= (float)$notas["ia5r"]){
        $nota5 = (float)$notas["ia5"];
    }else{
        $nota5 = (float)$notas["ia5r"];
    }

    $notaf = $nota1 + $nota2 + $nota3 + $nota4 + $nota5;
    if($notaf > 10){
        $notaf = 10;
    }

 ?>


<!--RESULTADO FINAL-->
<td class="linhagrid cell" title="" nowrap="" style="width:5%;text-align:center;;" id="gridlancrow0cell15">
    <input readonly value="<?=$notaf;?>" type="text" min="0" max="10" step="0.1" name="dcnota<?=$aluno['ed47_i_codigo']?>[]" id="rf1<?=$aluno['ed47_i_codigo']?>" style="width:99%" text-align="left">
</td>

<td class="linhagrid cell" title="" nowrap="" style="text-align:left;display: none;" id="gridlancrow0cell16">
    <input type="checkbox" value="12421" onfocus="js_sinalizarLinhaGrid(this, true)" onblur="js_sinalizarLinhaGrid(this, false)">
</td>




</tr>
<?php $i++; endforeach; ?>
                            </tbody>
                        </table>
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
<input type="submit" name="salvar" value="Salvar">
<a href="edu_lancaaval.php"><input type="button" value="Voltar" id="btnFechar"></a>
</div>
<?php /* ?><div style="text-align:center;padding-top:4px;padding-right:3px;">  <div style="float:right;clear:both;font-weight:bold">    <span style="background-color:#009600;padding:2px 8px 2px 8px;">&nbsp;</span>&nbsp;Aluno na escola    <span style="background-color:#FF0000;padding:2px 8px 2px 8px;">&nbsp;</span>&nbsp;Aluno ausente na escola  </div> <?php */ ?>     
</form>


<table>
    <tr>
        <td><b>ÁLGEBRA</b></td>
        <td> </td>
        <td> </td>
        <td> </td>
        <td>   </td>
        
        <td><b>GEOMETRIA</b></td>
        <td> </td>
        <td> </td>
        <td> </td>
    </tr>
    
    <tr>
        <td><b>IA1</b></td>
        <td>Prova</td>
        <td><b>Valor</b></td>
        <td>2,0</td>
        <td> </td>
        <td><b>IA6</b></td>
        <td>Prova</td>
        <td><b>Valor</b></td>
        <td>1,0</td>
    </tr>

    <tr>
        <td><b>IA1 R</b></td>
        <td>Recuperação</td>
        <td><b>Valor</b></td>
        <td>2,0</td>
        <td> </td>
        <td><b>IA6 R</b></td>
        <td>Recuperação</td>
        <td><b>Valor</b></td>
        <td>1,0</td>
    </tr>

    <tr>
        <td><b>IA2</b></td>
        <td>Teste</td>
        <td><b>Valor</b></td>
        <td>1,0</td>
        <td> </td>
        <td><b>IA7</b></td>
        <td>Teste</td>
        <td><b>Valor</b></td>
        <td>1,0</td>
    </tr>

    <tr>
        <td><b>IA2R</b></td>
        <td>Recuperação</td>
        <td><b>Valor</b></td>
        <td>1,0</td>
        <td> </td>
        <td><b>IA8</b></td>
        <td>Trabalho</td>
        <td><b>Valor</b></td>
        <td>1,0</td>
    </tr>

    <tr>
        <td><b>IA3</b></td>
        <td>Pesquisa</td>
        <td><b>Valor</b></td>
        <td>1,0</td>
        <td> </td>
        <td> </td>
        <td> </td>
        <td> </td>
        <td> </td>
    </tr>

    <tr>
        <td><b>IA4</b></td>
        <td>Trabalho</td>
        <td><b>Valor</b></td>
        <td>1,0</td>
        <td> </td>
        <td> </td>
        <td> </td>
        <td> </td>
        <td> </td>
    </tr>

    <tr>
        <td><b>IA5</b></td>
        <td>Exercício</td>
        <td><b>Valor</b></td>
        <td>1,0</td>
        <td> </td>
        <td> </td>
        <td> </td>
        <td> </td>
        <td> </td>
    </tr>
</table>


</div>

    
  </body>
<script>
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

  function valida(evt){
    var theEvent = evt || window.event;  
    if (theEvent.type === 'paste') {
        key = event.clipboardData.getData('text/plain');
    } else {
        var key = theEvent.keyCode || theEvent.which;
        key = String.fromCharCode(key);
    }
    //var regex = /[0-9]|\./;
    var regex = /[0-9]|\,/;
    if( !regex.test(key) ) {
        theEvent.returnValue = false;
        if(theEvent.preventDefault) theEvent.preventDefault();
    }
  }

function maximo(el) {
  if (el.value != "") {
    if (parseFloat(el.value) < parseFloat(el.min)) {
      el.value = el.min;
    }
    if (parseFloat(el.value) > parseFloat(el.max)) {
      el.value = el.max;
    }
  }
}

</script>  
</html>
<?
function buscaAluno($aluno){
    $sql      =     "SELECT 
                     aluno.ed47_v_nome, 
                     aluno.ed47_i_codigo, 
                     matricula.ed60_i_numaluno, 
                     matricula.ed60_c_situacao, 
                     matricula.ed60_c_concluida, 
                     matricula.ed60_c_rfanterior, 
                     matricula.ed60_i_turmaant, 
                     matricula.ed60_d_datamatricula, 
                     matricula.ed60_matricula, 
                     matricula.ed60_i_codigo, 
                     matricula.ed60_c_parecer, 
                     ed60_tipoingresso, 
                     serie.ed11_c_descr, 
                     to_char(alunotransfturma.ed69_d_datatransf,'DD/MM/YYYY') as datasaida, 
                     (select 
                      array_to_string(array_accum(ed336_turnoreferente), ',') 
                      from 
                      matriculaturnoreferente 
                      inner join matricula as matturno on matturno.ed60_i_codigo = matriculaturnoreferente.ed337_matricula and matturno.ed60_i_turma = turma.ed57_i_codigo 
                      inner join turmaturnoreferente on turmaturnoreferente.ed336_codigo = matriculaturnoreferente.ed337_turmaturnoreferente and turmaturnoreferente.ed336_turma = turma.ed57_i_codigo 
                      where 
                      matturno.ed60_i_codigo = matricula.ed60_i_codigo) as turnoreferente, 
                      matricula.ed60_d_datasaida 
                      from 
                      matricula 
                      inner join aluno on aluno.ed47_i_codigo = matricula.ed60_i_aluno 
                      inner join turma on turma.ed57_i_codigo = matricula.ed60_i_turma 
                      inner join matriculaserie on matriculaserie.ed221_i_matricula = matricula.ed60_i_codigo 
                      inner join serie on serie.ed11_i_codigo = matriculaserie.ed221_i_serie 
                      inner join serieregimemat on serieregimemat.ed223_i_serie = serie.ed11_i_codigo 
                      inner join turmaserieregimemat on turmaserieregimemat.ed220_i_serieregimemat = serieregimemat.ed223_i_codigo and 
                                 turmaserieregimemat.ed220_i_turma = matricula.ed60_i_turma 
                      left join turma as turmaant on turmaant.ed57_i_codigo = matricula.ed60_i_turmaant 
                      left join alunoprimat on alunoprimat.ed76_i_aluno = aluno.ed47_i_codigo 
                      left join alunotransfturma on alunotransfturma.ed69_i_matricula = matricula.ed60_i_codigo 
                      where 
                      matriculaserie.ed221_c_origem = 'S' 
                      and
                      ed60_i_aluno = {$aluno} 
                      and 
                      ed60_c_situacao <> 'TROCA DE TURMA' 
                      order by matricula.ed60_i_numaluno, to_ascii(ed47_v_nome)";
    $resultado  =     db_query($sql);
    $ed47_v_nome = db_utils::fieldsMemory($resultado, 0)->ed47_v_nome;
    return $ed47_v_nome;
}

function insereDiario($escola, $calendario, $turma, $aluno, $serie, $disciplina, $nota, $conceito, $regencia)
{
//    $diario1 =    pg_result(db_query("select max(ed95_i_codigo) as ed95_i_codigo from diario"),0,0);  
//  $arq = fopen("/dados/www/homologacao.epdvr.com.br/diario.txt","a+");
//  fwrite($arq, $diario1);
//  fwrite($arq,"\r\n");
//  fclose($arq);       
//  db_query("delete from diariofinal     where ed74_i_diario in (392387,392388,392389,392390,392391,392392,392393,392394,392395,392396,392397)");
//  db_query("delete from diarioresultado where ed73_i_diario in (392387,392388,392389,392390,392391,392392,392393,392394,392395,392396,392397)");
//  db_query("delete from diarioavaliacao where ed72_i_diario in (392387,392388,392389,392390,392391,392392,392393,392394,392395,392396,392397)");
//  db_query("delete from diario          where ed95_i_codigo in (392387,392388,392389,392390,392391,392392,392393,392394,392395,392396,392397)");

    
    $rsdiario = db_query("select nextval('diario_ed95_i_codigo_seq')"); 
    $diario   = pg_result($rsdiario,0,0); 
    

    
    $indiario  ="INSERT INTO escola.diario(ed95_i_codigo, ed95_i_escola, ed95_i_calendario, ed95_i_aluno, ed95_i_serie, ed95_i_regencia)
                 VALUES (".$diario.",".$escola.", ".$calendario.",".$aluno.",".$serie.", ".$regencia.")";
    db_query($indiario);

    db_query("insert into diarioavaliacao 
              (ed72_i_codigo, ed72_i_diario, ed72_i_procavaliacao, ed72_i_valornota, ed72_c_valorconceito, ed72_c_aprovmin, ed72_c_amparo, ed72_i_escola, ed72_c_tipo, ed72_c_convertido)
              values
              (nextval('diarioavaliacao_ed72_i_codigo_seq'),".$diario.", 60,".$nota.",".$conceito.",'N','N',".$escola.",'M','N')
             ");
              
    db_query("INSERT INTO escola.diarioclasse(ed300_sequencial, ed300_id_usuario, ed300_datalancamento, ed300_hora, ed300_auladesenvolvida)
                                      VALUES (nextval('diarioclasse_ed300_sequencial_seq'), 1287, '2024-08-06', '08:00', '')");       

    db_query("INSERT INTO escola.diariofinal(ed74_i_codigo, ed74_i_diario, ed74_i_procresultadoaprov, ed74_c_valoraprov, ed74_c_resultadoaprov, ed74_i_procresultadofreq, 
                                             ed74_i_percfreq, ed74_c_resultadofreq, ed74_c_resultadofinal, ed74_i_calcfreq, ed74_t_obs)
                                     VALUES (nextval('diariofinal_ed74_i_codigo_seq'),".$diario." , ?, ?, ?, ?, ?, ?, ?, ?, ?)");

//  db_query("");
}
?>
<? db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit")); ?>
