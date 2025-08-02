<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $user['username'];
            header('Location: index.php');
            exit;
        }
    }
    
    $error = "Usuário ou senha inválidos!";
}

if (isset($_SESSION['loggedin'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Painel Ghost</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #e74c3c;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), 
                        url('https://wallpapers.com/images/hd/1920x1080-hd-movie-1920-x-1080-gz4tb89aora60d2b.jpg') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
        }
        
        .login-container {
            width: 100%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }
        
        .login-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h2 {
            color: #fff;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 30px;
            color: #fff;
            padding: 12px 20px;
            margin-bottom: 20px;
        }
        
        .form-control:focus {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            box-shadow: none;
            border: none;
        }
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        
        .btn-login {
            background: var(--primary-color);
            border: none;
            border-radius: 30px;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 1px;
            width: 100%;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }
        
        .input-group-text {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 30px 0 0 30px;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .alert {
            border-radius: 30px;
            text-align: center;
        }
        
        .footer-links {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        
        .footer-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .footer-links a:hover {
            color: #fff;
            text-decoration: underline;
        }
        
        /* Efeito de vidro */
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .input-group {
        position: relative;
        display: flex;
        flex-wrap: wrap;
        align-items: stretch;
        width: 100%;
        margin-bottom: 1.5rem;
    }
    
    .input-group-text {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: rgba(255, 255, 255, 0.7);
        text-align: center;
        white-space: nowrap;
        background-color: rgba(255, 255, 255, 0.1);
        border: 1px solid transparent;
        border-right: none;
        border-radius: 30px 0 0 30px;
        height: 45px;
        min-width: 45px;
    }
    
    .form-control {
        display: block;
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #fff;
        background-color: rgba(255, 255, 255, 0.1);
        background-clip: padding-box;
        border: 1px solid transparent;
        border-left: none;
        appearance: none;
        border-radius: 0 30px 30px 0;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        height: 45px;
    }
    
    .form-control:focus {
        color: #fff;
        background-color: rgba(255, 255, 255, 0.2);
        outline: 0;
        box-shadow: none;
    }
    
    /* Ajuste fino para alinhamento vertical */
    .input-group-text i {
        line-height: 1;
    }
    </style>
</head>
<body>
    <div class="login-container glass-effect">
        <div class="login-header">
            <h2><i class="fas fa-film"></i> Painel de Controle</h2>
            <p>Acesse sua conta para gerenciar addon GHOST</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                <input type="text" class="form-control" id="username" name="username" placeholder="Usuário" required>
            </div>
            
            <div class="input-group mb-4">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" class="form-control" id="password" name="password" placeholder="Senha" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-login mb-3">
                <i class="fas fa-sign-in-alt"></i> Entrar
            </button>
            
            <div class="footer-links">
                <a href="#"><i class="fas fa-question-circle"></i> Esqueceu a senha?</a>
                <span class="mx-2">•</span>
                <a href="#"><i class="fas fa-envelope"></i> Suporte</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>