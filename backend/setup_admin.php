<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/admin_auth.php';

DB::connect();

if (PHP_SAPI === 'cli') {
    if ($argc < 3) {
        echo "Uso: php setup_admin.php <username> <password> [role]\n";
        echo "Ex:   php setup_admin.php admin senha123 super_admin\n";
        exit(1);
    }
    $username = $argv[1];
    $password = $argv[2];
    $role = $argv[3] ?? 'super_admin';

    $result = adminCreateFirst($username, $password, $role);
    if ($result['success']) {
        echo "Admin criado com sucesso!\n";
        echo "  ID: {$result['data']['id']}\n";
        echo "  Username: {$result['data']['username']}\n";
        echo "  Role: {$result['data']['role']}\n";
        echo "\nAcesse: " . APP_URL . "/admin/login\n";
    } else {
        echo "ERRO: {$result['message']}\n";
        exit(1);
    }
} else {
    echo "Este script deve ser executado via linha de comando (CLI).\n";
    echo "Uso: php setup_admin.php <username> <password> [role]\n";
}
