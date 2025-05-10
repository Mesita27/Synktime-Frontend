<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SynkTime - Login</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0072ff;
            --primary-light: #00c6ff;
            --primary-gradient: linear-gradient(135deg, #0072ff, #00c6ff);
            --primary-hover: linear-gradient(135deg, #005bcf, #00a1d6);
        }
        
        body {
            background-color: #f5f7fa;
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Roboto', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            display: flex;
            width: 900px;
            height: 600px;
            background-color: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .login-banner {
            width: 50%;
            background: var(--primary-gradient);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            padding: 3rem;
        }
        
        .login-banner h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .login-banner p {
            font-size: 1.1rem;
            text-align: center;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .login-form {
            width: 50%;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-form h2 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }
        
        .login-welcome {
            color: #6c757d;
            margin-bottom: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #2c3e50;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 114, 255, 0.1);
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
        }
        
        .remember-me input {
            margin-right: 0.5rem;
        }
        
        .forgot-password {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .forgot-password:hover {
            text-decoration: underline;
        }
        
        .login-btn {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .login-btn:hover {
            background: var(--primary-hover);
        }
        
        .login-footer {
            text-align: center;
            margin-top: 2rem;
            color: #6c757d;
        }
        
        .banner-img {
            max-width: 80%;
            margin-bottom: 2rem;
        }
        
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                width: 90%;
                height: auto;
            }
            
            .login-banner, .login-form {
                width: 100%;
                padding: 2rem;
            }
            
            .login-banner {
                order: -1;
                padding: 2rem;
            }
            
            .login-banner h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-banner">
            <h1>SynkTime</h1>
            <p>Sistema de Control de Asistencia</p>
            <img src="assets/img/time-illustration.svg" alt="Time Management" class="banner-img">
            <p>Simplifica el control de asistencia de tu empresa con nuestra solución integral</p>
        </div>
        <div class="login-form">
            <h2>Bienvenido</h2>
            <p class="login-welcome">Ingresa tus credenciales para continuar</p>
            
            <form action="index.php" method="POST">
                <div class="form-group">
                    <label for="username">Usuario</label>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" class="form-control" placeholder="Ingresa tu usuario" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Ingresa tu contraseña" required>
                    </div>
                </div>
                
                <div class="remember-forgot">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Recordarme</label>
                    </div>
                    <a href="#" class="forgot-password">¿Olvidaste tu contraseña?</a>
                </div>
                
                <button type="submit" class="login-btn">Iniciar Sesión</button>
            </form>
            
            <div class="login-footer">
                <p>&copy; 2025 SynkTime. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            // Demo: Submit form on button click
            $('.login-btn').click(function(e) {
                e.preventDefault();
                window.location.href = 'dashboard.php';
            });
        });
    </script>
</body>
</html>