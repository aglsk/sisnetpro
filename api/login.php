<?php
require_once __DIR__ . '/config.php'; // assume que config.php já cuida de session_start() com checagem

// Se já está logado, vai direto
if (!empty($_SESSION['loggedin'])) {
    header('Location: /index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Usuário e senha são obrigatórios!";
    } else {
        $conn = getDbConnection();
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    // login bem sucedido
                    $_SESSION['loggedin'] = true;
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_id'] = $user['id'];
                    header('Location: /index.php');
                    exit;
                }
            }
            // falha genérica de credenciais
            $error = "Usuário ou senha inválidos!";
            $stmt->close();
        } else {
            $error = "Erro interno. Tente novamente.";
        }
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login - Painel Ghost</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"/>
  <link rel="icon" href="/assets/favicon.ico" type="image/x-icon"/>
  <style>
    :root {
      --primary-color: #3498db;
      --secondary-color: #2c3e50;
      --accent-color: #e74c3c;
    }
    *{box-sizing:border-box;}
    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)),
                  url('https://wallpapers.com/images/hd/1920x1080-hd-movie-1920-x-1080-gz4tb89aora60d2b.jpg') no-repeat center center fixed;
      background-size: cover;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      color: #fff;
    }
    .login-container {
      width: 100%;
      max-width: 400px;
      background: rgba(255,255,255,0.1);
      backdrop-filter: blur(10px);
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 15px 35px rgba(0,0,0,0.5);
      border: 1px solid rgba(255,255,255,0.1);
      transition: all .3s ease;
    }
    .login-container:hover {
      transform: translateY(-5px);
      box-shadow: 0 20px 40px rgba(0,0,0,0.6);
    }
    .login-header {text-align:center;margin-bottom:30px;}
    .login-header h2 {margin:0;font-weight:600;}
    .form-control {
      background: rgba(255,255,255,0.1);
      border: none;
      border-radius: 0 30px 30px 0;
      color: #fff;
      padding: .75rem 1rem;
      height:45px;
    }
    .input-group-text {
      background: rgba(255,255,255,0.1);
      border: none;
      border-radius:30px 0 0 30px;
      color: rgba(255,255,255,0.7);
      min-width:45px;
      display:flex;
      align-items:center;
      justify-content:center;
    }
    .btn-login {
      background: var(--primary-color);
      border: none;
      border-radius: 30px;
      padding: 12px;
      font-weight: 600;
      letter-spacing: 1px;
      width: 100%;
      transition: all .3s;
      color:#fff;
    }
    .btn-login:hover {
      background: #2980b9;
      transform: translateY(-2px);
    }
    .alert {border-radius:30px;text-align:center;}
    .footer-links {text-align:center;margin-top:20px;font-size:14px;}
    .footer-links a {color: rgba(255,255,255,0.7);text-decoration:none;transition:.2s;}
    .footer-links a:hover {color:#fff;text-decoration:underline;}
  </style>
</head>
<body>
  <div class="login-container glass-effect">
    <div class="login-header">
      <h2><i class="fas fa-film"></i> Painel de Controle</h2>
      <p>Acesse sua conta para gerenciar addon GHOST</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <div class="input-group mb-3">
        <span class="input-group-text"><i class="fas fa-user"></i></span>
        <input type="text" name="username" class="form-control" placeholder="Usuário" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
      </div>

      <div class="input-group mb-4">
        <span class="input-group-text"><i class="fas fa-lock"></i></span>
        <input type="password" name="password" class="form-control" placeholder="Senha" required>
      </div>

      <button type="submit" class="btn btn-login mb-3">
        <i class="fas fa-sign-in-alt"></i> Entrar
      </button>

      <div class="footer-links">
        <a href="#"><i class="fas fa-question-circle"></i> Esqueceu a senha?</a>
        <span class="mx-2">•</span>
        <a href="#"><i class="fas fa-envelope"></i> Suporte</a>
      </div>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="" crossorigin="anonymous"></script>
</body>
</html>
