<?php

if (!function_exists('isNiteroi')) {
    function isNiteroi()
    {
        return getMunicipio() == "NITEROI";
    }
}

if (!function_exists('isVoltaRedonda')) {
    function isVoltaRedonda()
    {
        return getMunicipio() == "VOLTA REDONDA";
    }
}

if (!function_exists('isSantanaDoLivramento')) {
    function isSantanaDoLivramento()
    {
        return getMunicipio() == "SANTANA DO LIVRAMENTO";
    }
}

if (!function_exists('isValenca')) {
    function isValenca()
    {
        return getMunicipio() == "VALENCA";
    }
}

if (!function_exists('isCapivari')) {
    function isCapivari()
    {
        return getMunicipio() == "CAPIVARI DO SUL";
    }
}

if (!function_exists('isSapiranga')) {
    function isSapiranga()
    {
        return getMunicipio() == "SAPIRANGA";
    }
}

if (!function_exists('isPatyDoAlferes')) {
    function isPatyDoAlferes()
    {
        return getMunicipio() == "PATY DO ALFERES";
    }
}

if (!function_exists('isPortoVelho')) {
    function isPortoVelho()
    {

        return getMunicipio() == "PORTO VELHO";
    }
}

if (!function_exists('isSaoBorja')) {
    function isSaoBorja()
    {
        return getMunicipio() == "SAO BORJA";
    }
}

if (!function_exists('isItaqui')) {
    function isItaqui()
    {
        return getMunicipio() == "ITAQUI";
    }
}

if (!function_exists('getMunicipio')) {
    function getMunicipio()
    {
        $municipio = InstituicaoRepository::getInstituicaoPrefeitura()->getMunicipio();
        $municipio = \DBString::upperCaseRemoveCaracteresEspeciais($municipio);

        return \DBString::removerAcentuacao($municipio);
    }
}
