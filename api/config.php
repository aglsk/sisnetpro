<?php
session_start();

// Configurações do banco MySQL para login
define('DB_HOST', 'sql210.infinityfree.com');
define('DB_USER', 'if0_38682162');
define('DB_PASS', 'PRVn8fkkqRx');
define('DB_NAME', 'if0_38682162_sisnetpro');

// Conexão com o banco
function getDbConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}

// Criar tabela de usuários se não existir
function createUsersTable() {
    $conn = getDbConnection();
    
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $conn->query($sql);
    
    // Inserir admin padrão se não existir
    $result = $conn->query("SELECT id FROM users WHERE username = 'bressynickolas2007@gmail.com'");
    if ($result->num_rows == 0) {
        $password = password_hash('bressy124.?', PASSWORD_DEFAULT);
        $conn->query("INSERT INTO users (username, password) VALUES ('bressynickolas2007@gmail.com', '$password')");
    }
    
    $conn->close();
}

createUsersTable();

// Verificar login
function isLoggedIn() {
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}

// Redirecionar se não logado
if (!isLoggedIn() && basename($_SERVER['PHP_SELF']) != 'login.php') {
    header('Location: login.php');
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
    
    if($http_code == 200) {
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
    
    foreach($units as $unit => $text) {
        if($time < $unit) continue;
        $number = floor($time / $unit);
        return $number.' '.$text.($number > 1 ? 's' : '');
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
if(isset($popular_movies['results'])) {
    foreach(array_slice($popular_movies['results'], 0, 3) as $movie) {
        $ultimos_adicionados[] = [
            'titulo' => $movie['title'],
            'tipo' => 'filme',
            'data' => $movie['release_date'] ?? date('Y-m-d'),
            'imagem' => $movie['poster_path'] ? 'https://image.tmdb.org/t/p/w200'.$movie['poster_path'] : 'https://via.placeholder.com/200x300?text=No+Image',
            'id' => $movie['id']
        ];
    }
}
if(isset($popular_tv['results'])) {
    foreach(array_slice($popular_tv['results'], 0, 2) as $tv) {
        $ultimos_adicionados[] = [
            'titulo' => $tv['name'],
            'tipo' => 'série',
            'data' => $tv['first_air_date'] ?? date('Y-m-d'),
            'imagem' => $tv['poster_path'] ? 'https://image.tmdb.org/t/p/w200'.$tv['poster_path'] : 'https://via.placeholder.com/200x300?text=No+Image',
            'id' => $tv['id']
        ];
    }
}

// Ordenar por data
usort($ultimos_adicionados, function($a, $b) {
    return strtotime($b['data']) <=> strtotime($a['data']);
});
?>