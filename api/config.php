<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// URL do seu Web App do Google Apps Script
const APPSCRIPT_ENDPOINT = 'https://script.google.com/macros/s/AKfycbzUUXotRZ-8uZWFxFqnaJQ4XUIH_x1Jh0Yq78lXqX7qVuj7v1irWVvpFpIkcI4KN-5i/exec';

// Função para chamar o Apps Script
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) {
        return ['success' => false, 'error' => "Erro CURL: {$err}"];
    }
    $decoded = json_decode($resp, true);
    if (!is_array($decoded)) {
        return ['success' => false, 'error' => 'Resposta inválida da API'];
    }
    return $decoded;
}

// Verificar login com token salvo na sessão
function isLoggedIn(): bool {
    if (empty($_SESSION['token'])) {
        return false;
    }
    $verify = callAppsScript([
        'action' => 'verify',
        'token'  => $_SESSION['token']
    ]);
    return !empty($verify['success']) && $verify['success'] === true && isset($verify['data']['user_id']);
}

// Redirecionar se não logado
if (!isLoggedIn() && basename($_SERVER['PHP_SELF']) != 'login.php') {
    header('Location: /login');
    exit;
}

// Configurações da API TMDB
define('TMDB_API_KEY', 'fa2db16fd76770b1408ef23538da6695');
define('TMDB_LANG', 'pt-BR');

// Função para buscar dados da API TMDB
function getTmdbData($endpoint, $page = 1) {
    $url = "https://api.themoviedb.org/3/$endpoint?api_key=" . TMDB_API_KEY . "&language=" . TMDB_LANG . "&page=$page";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($http_code == 200) {
        return json_decode($response, true);
    }
    return null;
}

// Função para calcular "há x tempo"
function time_ago($time) {
    $time = time() - $time;
    $units = [
        31536000 => 'ano',
        2592000 => 'mês',
        604800 => 'semana',
        86400 => 'dia',
        3600 => 'hora',
        60 => 'minuto',
        1 => 'segundo'
    ];
    foreach ($units as $unit => $text) {
        if ($time < $unit) continue;
        $number = floor($time / $unit);
        return $number . ' ' . $text . ($number > 1 ? 's' : '');
    }
    return 'agora';
}

// Buscar dados da TMDB
$movie_news = getTmdbData('movie/now_playing');
$tv_news = getTmdbData('tv/on_the_air');
$popular_movies = getTmdbData('movie/popular');
$popular_tv = getTmdbData('tv/popular');

// Processar últimos adicionados
$ultimos_adicionados = [];
if (isset($popular_movies['results'])) {
    foreach (array_slice($popular_movies['results'], 0, 3) as $movie) {
        $ultimos_adicionados[] = [
            'titulo' => $movie['title'],
            'tipo' => 'filme',
            'data' => $movie['release_date'] ?? date('Y-m-d'),
            'imagem' => $movie['poster_path'] ? 'https://image.tmdb.org/t/p/w200' . $movie['poster_path'] : 'https://via.placeholder.com/200x300?text=No+Image',
            'id' => $movie['id']
        ];
    }
}
if (isset($popular_tv['results'])) {
    foreach (array_slice($popular_tv['results'], 0, 2) as $tv) {
        $ultimos_adicionados[] = [
            'titulo' => $tv['name'],
            'tipo' => 'série',
            'data' => $tv['first_air_date'] ?? date('Y-m-d'),
            'imagem' => $tv['poster_path'] ? 'https://image.tmdb.org/t/p/w200' . $tv['poster_path'] : 'https://via.placeholder.com/200x300?text=No+Image',
            'id' => $tv['id']
        ];
    }
}

// Ordenar por data
usort($ultimos_adicionados, function ($a, $b) {
    return strtotime($b['data']) <=> strtotime($a['data']);
});
