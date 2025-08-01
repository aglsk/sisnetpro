<?php
declare(strict_types=1);
session_start();
ini_set('display_errors', '0');
error_reporting(E_ALL);
header('Content-Type: text/html; charset=utf-8');

// URL do seu Web App do Google Apps Script
const APPSCRIPT_ENDPOINT = 'https://script.google.com/macros/s/AKfycbzUUXotRZ-8uZWFxFqnaJQ4XUIH_x1Jh0Yq78lXqX7qVuj7v1irWVvpFpIkcI4KN-5i/exec';

// TMDB
const TMDB_API_KEY = 'fa2db16fd76770b1408ef23538da6695';
const TMDB_LANG = 'pt-BR';

// --- Helpers de chamada à API do Apps Script ---
function callAppsScript(array $payload): array {
    $ch = curl_init(APPSCRIPT_ENDPOINT);
    $json = json_encode($payload);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($json)
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        throw new RuntimeException("Erro na requisição Apps Script: {$err}");
    }
    $decoded = json_decode($resp, true);
    if (!is_array($decoded)) {
        throw new RuntimeException("Resposta inválida da API: {$resp}");
    }
    return $decoded;
}

// Checa se está logado (valida token via Apps Script)
function isLoggedIn(): bool {
    if (empty($_SESSION['token'])) {
        return false;
    }
    try {
        $verify = callAppsScript([
            'action' => 'verify',
            'token' => $_SESSION['token']
        ]);
        if (!empty($verify['success']) && $verify['success'] === true && isset($verify['data']['user_id'])) {
            // opcional: atualizar expiration local ou outras informações
            return true;
        }
    } catch (Throwable $e) {
        // falha na verificação, considerar como não logado
    }
    return false;
}

// Redireciona se não logado (usar no topo das páginas protegidas)
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// Função para buscar dados da TMDB
function getTmdbData(string $endpoint, int $page = 1): ?array {
    $url = "https://api.themoviedb.org/3/{$endpoint}?api_key=" . TMDB_API_KEY . "&language=" . TMDB_LANG . "&page={$page}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200) {
        return json_decode($response, true);
    }
    return null;
}

// Calcula "há x tempo"
function time_ago(int $time): string {
    $delta = time() - $time;
    $units = [
        31536000 => 'ano',
        2592000 => 'mês',
        604800 => 'semana',
        86400 => 'dia',
        3600 => 'hora',
        60 => 'minuto',
        1 => 'segundo'
    ];
    foreach ($units as $secs => $text) {
        if ($delta < $secs) continue;
        $n = floor($delta / $secs);
        return $n . ' ' . $text . ($n > 1 ? 's' : '');
    }
    return 'agora';
}
