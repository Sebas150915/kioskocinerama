<?php
// Módulo de login simple para kiosko: un botón que inicia sesión y redirige a peliculas
// Si se accede por GET y ya existe una sesión kiosko, cerrarla (vuelve a la pantalla inicial)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  if (isset($_SESSION['iniciarSesion']) && $_SESSION['iniciarSesion'] == 'cinemapos') {
    session_unset();
    session_destroy();
    setcookie(session_name(), '', time() - 3600, '/');
    // crear una sesión nueva limpia para esta petición
    session_start();
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['iniciar'])) {
  // iniciar nueva sesión kiosko
  $_SESSION['iniciarSesion'] = 'cinemapos';
  $_SESSION['usuario'] = 'kiosko';
  $_SESSION['local_id'] = 1; // local fijo para kiosko
  header('Location: peliculas');
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>CINERAMA - Kiosko de Autoservicio</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      position: relative;
    }
    
    /* Elementos decorativos de fondo */
    body::before {
      content: '';
      position: fixed;
      top: -50%;
      right: -10%;
      width: 500px;
      height: 500px;
      background: radial-gradient(circle, rgba(8, 91, 214, 0.57) 0%, transparent 70%);
      border-radius: 50%;
      pointer-events: none;
      animation: float 6s ease-in-out infinite;
    }
    
    body::after {
      content: '';
      position: fixed;
      bottom: -30%;
      left: -5%;
      width: 400px;
      height: 400px;
      background: radial-gradient(circle, rgba(192, 108, 132, 0.15) 0%, transparent 70%);
      border-radius: 50%;
      pointer-events: none;
      animation: float 8s ease-in-out infinite reverse;
    }
    
    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(20px); }
    }
    
    .container {
      position: relative;
      z-index: 10;
      text-align: center;
    }
    
    .logo-section {
      margin-bottom: 40px;
      animation: slideDown 0.8s ease-out;
    }
    
    .logo-icon {
      font-size: 80px;
      margin-bottom: 20px;
      animation: pulse 2s ease-in-out infinite;
    }
    
    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.1); }
    }
    
    .logo-text {
      font-size: 48px;
      font-weight: 700;
      background: linear-gradient(135deg, #ff6b9d 0%, #c06c84 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 10px;
      letter-spacing: 2px;
    }
    
    .tagline {
      font-size: 16px;
      color: #b0b0b0;
      margin-bottom: 30px;
      font-weight: 300;
      letter-spacing: 1px;
    }
    
    .welcome-box {
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 20px;
      padding: 50px 40px;
      max-width: 500px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
      animation: slideUp 0.8s ease-out;
    }
    
    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .welcome-box h1 {
      font-size: 36px;
      color: #ffffff;
      margin-bottom: 15px;
      font-weight: 600;
    }
    
    .welcome-box p {
      font-size: 14px;
      color: #c0c0c0;
      margin-bottom: 40px;
      line-height: 1.6;
    }
    
    form {
      width: 100%;
    }
    
    .btn-start {
      width: 100%;
      padding: 18px 40px;
      font-size: 20px;
      font-weight: 700;
      background: linear-gradient(135deg, #ff6b9d 0%, #c06c84 100%);
      color: white;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(255, 107, 157, 0.4);
      text-transform: uppercase;
      letter-spacing: 1.5px;
      position: relative;
      overflow: hidden;
    }
    
    .btn-start::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: left 0.5s ease;
    }
    
    .btn-start:hover::before {
      left: 100%;
    }
    
    .btn-start:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 25px rgba(255, 107, 157, 0.6);
    }
    
    .btn-start:active {
      transform: translateY(-1px);
      box-shadow: 0 3px 12px rgba(255, 107, 157, 0.4);
    }
    
    .features {
      margin-top: 50px;
      display: flex;
      justify-content: space-around;
      gap: 20px;
      color: #b0b0b0;
      font-size: 12px;
    }
    
    .feature {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 8px;
    }
    
    .feature-icon {
      font-size: 32px;
      opacity: 0.8;
    }
    
    @media (max-width: 768px) {
      .logo-text {
        font-size: 36px;
      }
      
      .welcome-box {
        padding: 40px 30px;
        max-width: 90%;
      }
      
      .welcome-box h1 {
        font-size: 28px;
      }
      
      .btn-start {
        padding: 16px 30px;
        font-size: 18px;
      }
      
      .features {
        flex-direction: column;
        gap: 12px;
      }
    }
    
    @media (max-width: 480px) {
      .logo-icon {
        font-size: 60px;
      }
      
      .logo-text {
        font-size: 28px;
      }
      
      .tagline {
        font-size: 14px;
      }
      
      .welcome-box {
        padding: 30px 20px;
        border-radius: 15px;
      }
      
      .welcome-box h1 {
        font-size: 24px;
        margin-bottom: 10px;
      }
      
      .btn-start {
        padding: 14px 25px;
        font-size: 16px;
        border-radius: 10px;
      }
      
      .features {
        display: none;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="logo-section">
      <div class="logo-icon">🎬</div>
      <div class="logo-text">CINERAMA EL PACIFICO</div>
      <div class="tagline">Autoservicio de Entradas</div>
    </div>
    
    <div class="welcome-box">
      <h1>¡Bienvenido!</h1>
      <p>Compra tus entradas de forma rápida y segura. Selecciona tu película, horario y disfruta del cine.</p>
      
      <form method="post">
        <button type="submit" name="iniciar" class="btn-start">
          → Iniciar Compra
        </button>
      </form>
    </div>
    
    <div class="features">
      <div class="feature">
        <div class="feature-icon">🎫</div>
        <div>Entradas en línea</div>
      </div>
      <div class="feature">
        <div class="feature-icon">⚡</div>
        <div>Compra rápida</div>
      </div>
      <div class="feature">
        <div class="feature-icon">🔒</div>
        <div>100% seguro</div>
      </div>
    </div>
