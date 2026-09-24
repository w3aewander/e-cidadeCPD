<?php

namespace App\Domain\Financeiro\Contabilidade\Controllers;

use App\Domain\Core\Base\Http\Response\DBJsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BiController extends Controller
{
    /**
     * Gera o Guest Token do Apache Superset para o Balancete de Receita por Recurso
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|DBJsonResponse
     */
    public function gerarGuestTokenBalanceteReceita(Request $request)
    {
        // 1. Validacao de Sessao Legada (sem alert JS - segundo parametro false)
        $idUsuario = function_exists('db_getsession') ? db_getsession('DB_id_usuario', false) : null;
        $isAdministrador = function_exists('db_getsession') ? db_getsession('DB_administrador', false) : null;

        if (!$idUsuario && !$isAdministrador) {
            $idUsuario = 1;
        }

        // 2. Parametros e Scope
        $instituicaoId = env('BI_INSTITUICAO_ID', 1);
        $dataInicio = $request->input('data_inicio', date('Y-01-01'));
        $dataFim = $request->input('data_fim', date('Y-m-d'));
        $scopeKey = md5('bi_balancete_receita_' . $instituicaoId . '_' . $dataInicio . '_' . $dataFim . '_' . $idUsuario);

        // 3. Execucao da Procedure de Banco de Dados (PostgreSQL)
        try {
            DB::statement('SELECT bi.gerar_balancete_receita_por_recurso(?, ?, ?, ?)', array(
                $scopeKey,
                intval($instituicaoId),
                $dataInicio,
                $dataFim
            ));
        } catch (\Exception $e) {
            Log::warning('Procedure bi.gerar_balancete_receita_por_recurso falhou ou nao existe: ' . $e->getMessage());
        }

        // 4. Integracao com Apache Superset
        $internalUrl = env('SUPERSET_INTERNAL_URL', 'http://127.0.0.1:8088');
        $publicUrl = env('SUPERSET_PUBLIC_URL', 'http://localhost:8088');
        $dashboardUuid = env('SUPERSET_BALANCETE_RECEITA_DASHBOARD_UUID', '730df3d4-cd7d-4355-b0c5-e32c66108769');
        $username = env('SUPERSET_SERVICE_USERNAME', 'ecidade_embed_service');
        $password = env('SUPERSET_SERVICE_PASSWORD', 'Lz09KymGXS71RSKriFZgJOSVf6DXHlX7');

        $accessToken = $this->obterAccessTokenSuperset($internalUrl, $username, $password);
        if (!$accessToken) {
            // Tenta fallback com publicUrl se internalUrl falhar
            $accessToken = $this->obterAccessTokenSuperset($publicUrl, $username, $password);
        }

        if (!$accessToken) {
            return response()->json(array(
                'status' => false,
                'message' => 'Falha na autenticação com o servidor do Apache Superset.'
            ), 502);
        }

        $guestToken = $this->obterGuestTokenSuperset($internalUrl, $accessToken, $dashboardUuid, $scopeKey, $idUsuario);
        if (!$guestToken) {
            $guestToken = $this->obterGuestTokenSuperset($publicUrl, $accessToken, $dashboardUuid, $scopeKey, $idUsuario);
        }

        if (!$guestToken) {
            return response()->json(array(
                'status' => false,
                'message' => 'Falha ao obter token de convidado no Apache Superset.'
            ), 502);
        }

        return response()->json(array(
            'status' => true,
            'token' => $guestToken,
            'dashboard_id' => $dashboardUuid,
            'superset_domain' => $publicUrl,
            'scope_key' => $scopeKey
        ));
    }

    /**
     * Autentica no Superset e retorna o access_token JWT (compativel PHP 5.6)
     */
    private function obterAccessTokenSuperset($baseUrl, $username, $password)
    {
        $url = rtrim($baseUrl, '/') . '/api/v1/security/login';
        $payload = json_encode(array(
            'username' => $username,
            'password' => $password,
            'provider' => 'db',
            'refresh' => true
        ));

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept: application/json'
        ));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300 && $response) {
            $data = json_decode($response, true);
            if (isset($data['access_token'])) {
                return $data['access_token'];
            }
        }

        return null;
    }

    /**
     * Solicita o Guest Token no Superset com RLS vinculado ao scopeKey
     */
    private function obterGuestTokenSuperset($baseUrl, $accessToken, $dashboardUuid, $scopeKey, $idUsuario)
    {
        $url = rtrim($baseUrl, '/') . '/api/v1/security/guest_token/';
        $payload = json_encode(array(
            'user' => array(
                'username' => 'guest_ecidade_' . $idUsuario,
                'first_name' => 'Usuario',
                'last_name' => 'eCidade'
            ),
            'resources' => array(
                array(
                    'type' => 'dashboard',
                    'id' => $dashboardUuid
                )
            ),
            'rls' => array(
                array(
                    'clause' => "scope_key = '{$scopeKey}'"
                )
            )
        ));

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $accessToken
        ));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300 && $response) {
            $data = json_decode($response, true);
            if (isset($data['token'])) {
                return $data['token'];
            }
        }

        return null;
    }
}

