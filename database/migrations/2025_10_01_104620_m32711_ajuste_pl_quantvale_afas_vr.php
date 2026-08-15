<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class M32711AjustePlQuantvaleAfasVr extends Migration
{
    public function up()
 {
    DB::connection()->getPdo()->exec(<<<SQL

drop function if exists pessoal.quantvale_afas_vr(character,integer,integer,integer,integer,boolean,boolean,integer);
drop function if exists pessoal.quantvale_afas_vr(character,integer,integer,integer,integer,boolean,boolean,integer,integer);
CREATE OR REPLACE FUNCTION pessoal.quantvale_afas_vr(character,integer,integer,integer,integer,boolean,boolean,integer,integer) RETURNS integer AS '

DECLARE
    VALE         ALIAS FOR $1;
    REGISTRO     ALIAS FOR $2;
    ANO          ALIAS FOR $3;
    MES          ALIAS FOR $4;
    QUANT        ALIAS FOR $5;
    DIFER        ALIAS FOR $6;
    FERIAS       ALIAS FOR $7;
    --NDIAS        ALIAS FOR $8;
    V_ULTIMO_DIA ALIAS FOR $8;
    INSTIT       ALIAS FOR $9;

    TOTALDOVALE INTEGER := 0;
    DTINI       DATE;
    DTFIM       DATE;
    TOTAL       INTEGER := 0;
    RECOS       INTEGER := 0;   

    iAnoAnterior  integer := ANO;
    iMesAnterior  integer := MES - 1;

BEGIN
    -- regra do mês anterior
    IF iMesAnterior = 0 THEN
        iMesAnterior := 12;
        iAnoAnterior := ANO - 1;
    END IF; 

SELECT COUNT(R63_REGIST) 
INTO RECOS  
FROM VTFDIAS 
WHERE R63_ANOUSU = iAnoAnterior
  AND R63_MESUSU = iMesAnterior;

IF RECOS > 0 THEN

    DTINI := TO_DATE( TO_CHAR( iAnoAnterior,''9999'') 
                     || TO_CHAR( iMesAnterior,''99'') 
                     || TO_CHAR( 1,''99'' )
                    ,''YYYY-MM-DD'' ); 
                    
    SELECT EXTRACT(DAY FROM (DATE_TRUNC(''month'', TO_DATE(TO_CHAR(iAnoAnterior,''9999'') || ''-'' || TO_CHAR(iMesAnterior,''99'') || ''-01'', ''YYYY-MM-DD'')) + INTERVAL ''1 month'' - INTERVAL ''1 day''))
    INTO V_ULTIMO_DIA;

    DTFIM := TO_DATE( TO_CHAR( iAnoAnterior,''9999'') 
                     || TO_CHAR( iMesAnterior,''99'') 
                     || TO_CHAR( V_ULTIMO_DIA,''99'' )
                    ,''YYYY-MM-DD'' );
       
    SELECT SUM( R63_QUANT ) 
    INTO  TOTAL 
    FROM RHPESSOALMOV 
         INNER JOIN RHPESSOAL    ON RH01_REGIST = RH02_REGIST               
         LEFT OUTER JOIN VTFDIAS ON R63_ANOUSU = iAnoAnterior
                                AND R63_MESUSU = iMesAnterior
                                AND R63_REGIST = RH02_REGIST
                                AND R63_VALE   = VALE
                                AND R63_DIFERE = DIFER
         LEFT JOIN RHLOTACALEND  ON RH64_LOTA  = RH02_LOTA
         LEFT OUTER JOIN CALENDF ON R62_CALEND::BIGINT = RH64_CALEND
                                AND R62_DATA = R63_DIA 
         LEFT OUTER JOIN (
                          SELECT DISTINCT ON( R30_REGIST ) R30_REGIST , R30_PERAI,
                                (CASE WHEN R30_PER1I < DTINI  THEN  DTINI  ELSE R30_PER1I  END ) as r30_PER1I,
                                (CASE WHEN R30_PER1F > DTFIM  THEN  DTFIM  ELSE R30_PER1F  END ) AS r30_PER1F,
                                (CASE WHEN R30_PER2I < DTINI  THEN  DTINI  ELSE R30_PER2I  END ) as r30_PER2I,
                                (CASE WHEN R30_PER2F > DTFIM  THEN  DTFIM  ELSE R30_PER2F  END ) AS r30_PER2F
                          FROM CADFERIA 
                          WHERE R30_ANOUSU= iAnoAnterior
                            AND R30_MESUSU= iMesAnterior
                            AND (
                                 (
                                  ( R30_PER1I >= DTINI AND R30_PER1I <= DTFIM ) 
                               OR ( R30_PER1F >= DTINI AND R30_PER1F <= DTFIM )
                               OR ( R30_PER1I <  DTINI AND R30_PER1F >  DTFIM )
                                 )
                              OR (
                                 ( R30_PER2I >= DTINI AND R30_PER2I <= DTFIM ) 
                               OR ( R30_PER2F >= DTINI AND R30_PER2F <= DTFIM )
                               OR ( R30_PER2I <  DTINI AND R30_PER2F >  DTFIM )
                                 )
                                )
                          ORDER BY R30_REGIST, R30_PERAI DESC 
                         ) AS XCADFERIA ON R30_REGIST = RH02_REGIST
                                       AND R63_DIA >= R30_PER1I 
                                       AND R63_DIA <= R30_PER1F
                                       AND R63_DIA >= Rh01_ADMISS
                                       AND FERIAS = ''t''
         LEFT OUTER JOIN (
                          SELECT R45_REGIST, 
                                 (CASE WHEN R45_DTAFAS < DTINI THEN DTINI ELSE R45_DTAFAS END) AS R45_DTAFAS,
                                 (CASE WHEN R45_DTRETO > DTFIM OR R45_DTRETO IS NULL THEN DTFIM ELSE R45_DTRETO END) AS R45_DTRETO 
                          FROM AFASTA 
                          WHERE R45_ANOUSU = iAnoAnterior
                            AND R45_MESUSU = iMesAnterior
                            AND R45_REGIST = REGISTRO
                            AND (
                                 (R45_DTAFAS <= DTFIM AND R45_DTRETO IS NULL) 
                                    OR (R45_DTAFAS >= DTINI AND R45_DTRETO <= DTFIM)
                                    OR (R45_DTRETO > DTFIM AND R45_DTAFAS >= DTINI AND R45_DTAFAS <= DTFIM)
                                    OR (R45_DTAFAS < DTINI AND R45_DTRETO >= DTINI)
                                    OR (R45_DTRETO > DTFIM AND R45_DTAFAS <  DTINI)
                                )
                          ORDER BY R45_REGIST, R45_DTAFAS DESC 
                         ) AS XAFASTA ON R45_REGIST = R63_REGIST 
                                     AND R63_DIA >= R45_DTAFAS
                                     AND (R63_DIA <= R45_DTRETO OR R45_DTRETO IS NULL)
         LEFT OUTER JOIN (
                          SELECT R69_REGIST, R68_CODIGO, 
                                 (CASE WHEN R69_DTAFAST < DTINI THEN DTINI ELSE R69_DTAFAST END) AS R69_DTAFAST,
                                 (CASE WHEN R69_DTRETORNO > DTFIM OR R69_DTRETORNO IS NULL THEN DTFIM ELSE R69_DTRETORNO END) AS R69_DTRETORNO 
                          FROM AFASTAMENTO
                               LEFT OUTER JOIN CAD_AFAST ON R68_ANOUSU = iAnoAnterior
                                                        AND R68_MESUSU = iMesAnterior
                                                        AND R68_CODIGO = R69_CODIGO
                                                        AND R68_VT = ''f''
                          WHERE R69_ANOUSU = iAnoAnterior
                            AND R69_MESUSU = iMesAnterior
                            AND R69_REGIST = REGISTRO
                            AND (
                                 (R69_DTAFAST <= DTFIM AND R69_DTRETORNO IS NULL) 
                              OR (R69_DTAFAST >= DTINI AND R69_DTRETORNO <= DTFIM)
                              OR (R69_DTRETORNO > DTFIM AND R69_DTAFAST >= DTINI AND R69_DTAFAST <= DTFIM)
                              OR (R69_DTAFAST < DTINI AND R69_DTRETORNO >= DTINI)
                              OR (R69_DTRETORNO > DTFIM AND R69_DTAFAST < DTINI)
                                )
                            AND R68_CODIGO IS NOT NULL
                          ORDER BY R69_REGIST, R69_DTAFAST DESC ) AS XXAFASTA ON R69_REGIST = R63_REGIST 
                                                                             AND ((R63_DIA >= R69_DTAFAST) 
                                                                             AND (R63_DIA <= R69_DTRETORNO OR R69_DTRETORNO IS NULL)
                         )
    INNER JOIN rhteutri ON rhteutri.rh67_anousu = RH02_ANOUSU
                   AND rhteutri.rh67_mesusu = RH02_MESUSU
                   AND rhteutri.rh67_regist = RH02_REGIST
                    AND rhteutri.rh67_valor > 0
    WHERE RH02_ANOUSU = iAnoAnterior
      AND RH02_MESUSU = iMesAnterior
      AND RH02_INSTIT = INSTIT
      AND RH02_REGIST = REGISTRO 
      AND RH02_REGIST IS NOT NULL 
      AND R63_REGIST  IS NOT NULL 
      AND R30_REGIST  IS NULL
      AND R63_DIA >= RH01_ADMISS
      AND (
           R45_REGIST IS NULL 
       AND R69_REGIST IS NULL 
       AND (
            R62_CALEND IS NULL 
         OR (
             R62_CALEND IS NOT NULL 
         AND R63_OBRIG=''t''
            )
           )
          );

   --Novo SELECT para pegar o valor de rh67_vale
    --SELECT COALESCE(SUM(rh67_vales), 0)
    --INTO TOTALDOVALE
    --FROM rhteutri
    --WHERE rh67_anousu = iAnoAnterior
    --  AND rh67_mesusu = iMesAnterior
    --  AND rh67_regist = REGISTRO
    --  AND rh67_valor > 0;

    --Se nao encontrar dias, usa o TOTAL original
    IF TOTALDOVALE = 0 THEN
       TOTALDOVALE = TOTAL;
    END IF;

ELSE
    TOTALDOVALE = QUANT;
END IF;

RETURN TOTALDOVALE;
END;

' language plpgsql;

SQL
        );
    }

    public function down()
    {
        DB::connection()->getPdo()->exec(<<<SQL
            DROP FUNCTION IF EXISTS pessoal.quantvale_afas_vr(
                                                            character,
                                                            integer,
                                                            integer,
                                                            integer,
                                                            integer,
                                                            boolean,
                                                            boolean,
                                                            integer,
                                                            integer
            );
SQL
        );
    }
}

