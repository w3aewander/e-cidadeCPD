<?php

namespace App\Domain\BI\Controllers;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupersetEmbedController extends Controller
{
    /**
     * BI settings intentionally live outside the e-Cidade .env.  This keeps
     * Superset secrets isolated from the legacy application's configuration.
     */
    private function biSetting($name, $default = null)
    {
        static $settings = null;

        if ($settings === null) {
            $settings = array();
            $file = getenv('ECIDADE_BI_RUNTIME_FILE') ?: '/etc/ecidade-bi/runtime.env';

            if (is_readable($file)) {
                foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                    $line = trim($line);
                    if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
                        continue;
                    }

                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    if (preg_match('/^[A-Z0-9_]+$/', $key)) {
                        $settings[$key] = trim($value);
                    }
                }
            }
        }

        return array_key_exists($name, $settings) ? $settings[$name] : env($name, $default);
    }

    public function guestToken(Request $request)
    {
        $userId = function_exists('db_getsession') ? (int) \db_getsession('DB_id_usuario', false) : (int) session('DB_id_usuario');
        if (!$userId && isset($_SESSION['DB_id_usuario'])) {
            $userId = (int) $_SESSION['DB_id_usuario'];
        }
        $administrator = $userId === 1
            ? 1
            : (function_exists('db_getsession') ? (int) \db_getsession('DB_administrador', false) : (int) session('DB_administrador'));
        if (!$administrator && isset($_SESSION['DB_administrador'])) {
            $administrator = (int) $_SESSION['DB_administrador'];
        }
        $institution = function_exists('db_getsession') ? (int) \db_getsession('DB_instit', false) : (int) session('DB_instit', 1);
        if (!$institution && isset($_SESSION['DB_instit'])) {
            $institution = (int) $_SESSION['DB_instit'];
        }
        $sessionExercise = function_exists('db_getsession') ? (int) \db_getsession('DB_anousu', false) : (int) session('DB_anousu', date('Y'));
        if (!$sessionExercise && isset($_SESSION['DB_anousu'])) {
            $sessionExercise = (int) $_SESSION['DB_anousu'];
        }
        $exercise = (int) $request->input('exercicio', $sessionExercise ?: date('Y'));

        abort_unless($userId && ($administrator === 1 || $userId === 1), 403);
        abort_unless($institution === (int) $this->biSetting('BI_INSTITUICAO_ID', 1), 403);

        abort_unless($exercise >= 2000 && $exercise <= ((int) date('Y') + 1), 422, 'Exercício inválido.');

        $startDate = $request->input('periodoInicial', $exercise . '-01-01');
        $endDate = $request->input('periodoFinal', $exercise . '-12-31');

        $start = \DateTime::createFromFormat('!Y-m-d', $startDate);
        $end = \DateTime::createFromFormat('!Y-m-d', $endDate);
        abort_unless(
            $start && $end
            && $start->format('Y-m-d') === $startDate
            && $end->format('Y-m-d') === $endDate
            && (int) $start->format('Y') === $exercise
            && (int) $end->format('Y') === $exercise
            && $start <= $end,
            422,
            'Período inválido para o exercício ativo.'
        );

        $scopeKey = substr(hash_hmac(
            'sha256',
            implode('|', [$userId, $institution, $exercise, $startDate, $endDate]),
            (string) config('app.key')
        ), 0, 32);
        DB::select(
            'SELECT bi.gerar_balancete_receita_por_recurso(?, ?, ?, ?::date, ?::date)',
            [$scopeKey, $institution, $exercise, $startDate, $endDate]
        );

        $dashboardUuid = $this->biSetting('SUPERSET_BALANCETE_RECEITA_DASHBOARD_UUID');
        abort_unless($dashboardUuid, 503, 'Dashboard BI ainda não foi publicado.');

        $baseUrl = rtrim($this->biSetting('SUPERSET_INTERNAL_URL', 'http://superset:8088'), '/');
        $apiPrefix = '/' . trim($this->biSetting('SUPERSET_API_PREFIX', ''), '/');
        if ($apiPrefix === '/') {
            $apiPrefix = '';
        }
        $cookies = new CookieJar();
        $client = new Client(['base_uri' => $baseUrl, 'cookies' => $cookies, 'timeout' => 10]);

        try {
            $login = $client->post($apiPrefix . '/api/v1/security/login', ['json' => [
                'username' => $this->biSetting('SUPERSET_SERVICE_USERNAME'),
                'password' => $this->biSetting('SUPERSET_SERVICE_PASSWORD'),
                'provider' => 'db',
                'refresh' => false,
            ]]);
            $accessToken = json_decode((string) $login->getBody(), true)['access_token'];
            $csrf = $client->get($apiPrefix . '/api/v1/security/csrf_token/', [
                'headers' => ['Authorization' => 'Bearer ' . $accessToken],
            ]);
            $csrfToken = json_decode((string) $csrf->getBody(), true)['result'];
            $guest = $client->post($apiPrefix . '/api/v1/security/guest_token/', [
                'headers' => ['Authorization' => 'Bearer ' . $accessToken, 'X-CSRFToken' => $csrfToken],
                'json' => [
                    'user' => [
                        'username' => 'ecidade-' . $userId,
                        'first_name' => (string) \db_getsession('DB_login'),
                        'last_name' => ''
                    ],
                    'resources' => [['type' => 'dashboard', 'id' => $dashboardUuid]],
                    'rls' => [['clause' => "scope_key = '" . $scopeKey . "'"]],
                ],
            ]);
        } catch (\Throwable $exception) {
            report($exception);
            abort(502, 'Não foi possível autenticar o painel BI.');
        }

        return response()->json([
            'token' => json_decode((string) $guest->getBody(), true)['token'],
            'dashboardUuid' => $dashboardUuid,
            'supersetUrl' => rtrim($this->biSetting('SUPERSET_PUBLIC_URL', 'http://localhost:8088'), '/'),
            'instituicao' => $institution,
            'exercicio' => $exercise,
            'periodoInicial' => $startDate,
            'periodoFinal' => $endDate,
        ]);
    }
}
