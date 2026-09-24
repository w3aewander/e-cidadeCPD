<?php
require_once('bootstrap/autoload.php');
$app = require_once('bootstrap/app.php');
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;

try {
    $client = DB::table('oauth_clients')->where('id', 9999999)->first();
    if (!$client) {
        DB::table('oauth_clients')->insert(array(
            'id' => 9999999,
            'name' => 'e-Cidade Password Grant Client',
            'secret' => '4ed0NUlmQI20AbQZCrzeXxOOlDfjB73ib6juqqpS',
            'redirect' => 'http://localhost',
            'personal_access_client' => false,
            'password_client' => true,
            'revoked' => false,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ));
        echo "CLIENT_INSERTED_SUCCESSFULLY\n";
    } else {
        DB::table('oauth_clients')->where('id', 9999999)->update(array(
            'secret' => '4ed0NUlmQI20AbQZCrzeXxOOlDfjB73ib6juqqpS',
            'password_client' => true,
            'revoked' => false
        ));
        echo "CLIENT_UPDATED_SUCCESSFULLY\n";
    }

    $all = DB::table('oauth_clients')->get();
    echo "TOTAL_CLIENTS: " . count($all) . "\n";
    foreach ($all as $c) {
        echo "ID: " . $c->id . " | NAME: " . $c->name . " | PASS_CLIENT: " . ($c->password_client ? 'TRUE' : 'FALSE') . "\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

