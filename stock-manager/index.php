<?php
// index.php - Página principal profesional de Stock Manager - VERSIÓN VIBRANTE
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Manager - Sistema de Inventario para PYMES</title>
    
    <!-- Meta tags para SEO -->
    <meta name="description" content="Stock Manager: El sistema de gestión de inventario más intuitivo para pequeñas y medianas empresas. Controla tu stock, ventas y compras en tiempo real.">
    <meta name="keywords" content="inventario, stock, pymes, gestión, software, facturación">
    <meta name="author" content="Stock Manager S.L.">
    
    <!-- Open Graph / Redes Sociales -->
    <meta property="og:title" content="Stock Manager - Inventario inteligente para PYMES">
    <meta property="og:description" content="Optimiza tu inventario, reduce pérdidas y aumenta tus ventas. Prueba gratis 30 días.">
    <meta property="og:image" content="assets/img/Logo_Stock_Manager.png">
    <meta property="og:url" content="https://www.stockmanager.com">
    
    <!-- Fuentes y estilos -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* ===== VARIABLES ===== */
        :root {
            --navy-matte: #1e3a8a;
            --navy-dark: #0f172a;
            --lime-green: #84cc16;
            --lime-hover: #65a30d;
            --bg-light: #f0f9ff;
            --bg-white: #ffffff;
            --card-bg: #ffffff;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --accent: #2563eb;
            --accent-light: #60a5fa;
            --gradient-primary: linear-gradient(135deg, #2563eb 0%, #1e3a8a 100%);
            --gradient-green: linear-gradient(135deg, #84cc16 0%, #4d7c0f 100%);
        }

        /* ===== RESET Y BASE ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--bg-white) 0%, var(--bg-light) 100%);
            color: var(--text-dark);
            line-height: 1.6;
            overflow-x: hidden;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* ===== UTILIDADES ===== */
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .section {
            padding: 80px 0;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 800;
            text-align: center;
            margin-bottom: 1rem;
            color: var(--text-dark);
        }

        .section-subtitle {
            font-size: 1.2rem;
            color: var(--text-muted);
            text-align: center;
            max-width: 700px;
            margin: 0 auto 3rem;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            font-size: 1rem;
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 10px 20px -5px rgba(30, 58, 138, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 30px -5px rgba(30, 58, 138, 0.4);
        }

        .btn-outline {
            background: transparent;
            color: var(--navy-dark);
            border: 2px solid var(--navy-matte);
        }

        .btn-outline:hover {
            background: var(--navy-matte);
            color: white;
        }

        /* ===== NAVBAR ===== */
        .navbar {
            position: sticky;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            padding: 20px 0;
            background-color: var(--navy-matte);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo img {
            height: 50px;
            width: auto;
        }

        .logo-text {
            font-size: 1.8rem;
            font-weight: 800;
        }

        .logo-stock {
            color: var(--navy-dark);
        }

        .logo-manager {
            color: var(--lime-green);
        }

        .nav-menu {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .nav-menu a {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-menu a:hover {
            color: var(--lime-green);
        }

        .nav-buttons {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .login-btn {
            color: white;
            font-weight: 600;
            padding: 8px 20px;
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s;
        }

        .login-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--lime-green);
        }

        .signup-btn {
            background: var(--lime-green);
            color: var(--navy-dark);
            padding: 8px 20px;
            border-radius: 6px;
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(132, 204, 22, 0.3);
        }

        .signup-btn:hover {
            background: var(--lime-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(132, 204, 22, 0.4);
        }

        .menu-toggle {
            display: none;
            font-size: 24px;
            color: white;
            cursor: pointer;
        }

        /* ===== HERO SECTION ===== */
        .hero {
            min-height: 90vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #ffffff 0%, #e0f2fe 100%);
        }

        .hero .container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .hero-content {
            color: var(--text-dark);
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 20px;
        }

        .hero-title .stock-part {
            color: var(--navy-dark);
        }

        .hero-title .manager-part {
            color: var(--lime-green);
        }

        .hero-description {
            font-size: 1.2rem;
            color: var(--text-muted);
            margin-bottom: 30px;
            max-width: 500px;
        }

        .hero-buttons {
            display: flex;
            gap: 20px;
            margin-bottom: 40px;
        }

        .hero-stats {
            display: flex;
            gap: 50px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            color: var(--navy-matte);
            display: block;
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .hero-image {
            position: relative;
        }

        /* ===== MOCKUP PROFESIONAL ===== */
        .dashboard-mockup {
            background: white;
            border-radius: 20px;
            padding: 25px 20px 20px 20px;
            box-shadow: 0 30px 60px -20px rgba(30, 58, 138, 0.4);
            border: 1px solid rgba(30, 58, 138, 0.2);
            position: relative;
            transform: perspective(1000px) rotateY(-5deg);
            transition: all 0.5s ease;
        }

        .dashboard-mockup:hover {
            transform: perspective(1000px) rotateY(-2deg) translateY(-10px);
            box-shadow: 0 40px 70px -20px rgba(132, 204, 22, 0.4);
        }

        .mockup-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e2e8f0;
        }

        .mockup-logo {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .mockup-logo i {
            color: var(--navy-matte);
            font-size: 24px;
        }

        .mockup-logo span {
            font-weight: 700;
            color: var(--navy-dark);
        }

        .mockup-date {
            color: var(--text-muted);
            font-size: 12px;
            background: #f1f5f9;
            padding: 5px 10px;
            border-radius: 20px;
        }

        .mockup-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .mockup-stat {
            background: #f8fafc;
            padding: 12px;
            border-radius: 12px;
            text-align: center;
        }

        .mockup-stat .value {
            font-size: 20px;
            font-weight: 700;
            color: var(--navy-matte);
        }

        .mockup-stat .label {
            font-size: 11px;
            color: var(--text-muted);
        }

        .mockup-chart {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            height: 120px;
            border-radius: 12px;
            margin-bottom: 15px;
            display: flex;
            align-items: flex-end;
            padding: 10px;
            gap: 8px;
        }

        .chart-bar {
            flex: 1;
            height: 60px;
            background: var(--lime-green);
            border-radius: 6px 6px 0 0;
            opacity: 0.7;
            transition: all 0.3s;
        }

        .chart-bar:hover {
            opacity: 1;
            transform: scale(1.05);
        }

        .chart-bar:nth-child(1) { height: 40px; }
        .chart-bar:nth-child(2) { height: 70px; }
        .chart-bar:nth-child(3) { height: 55px; }
        .chart-bar:nth-child(4) { height: 85px; }
        .chart-bar:nth-child(5) { height: 30px; }
        .chart-bar:nth-child(6) { height: 65px; }

        .mockup-table {
            margin-top: 15px;
        }

        .mockup-row {
            display: grid;
            grid-template-columns: 3fr 1fr 1fr;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
            font-size: 13px;
        }

        .mockup-row.header {
            color: var(--navy-dark);
            font-weight: 600;
            border-bottom: 2px solid var(--navy-matte);
        }

        .mockup-row .status {
            background: var(--lime-green);
            color: white;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-align: center;
        }

        /* ===== FEATURES SECTION ===== */
        .features {
            background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 100%);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-top: 50px;
        }

        .feature-card {
            background: var(--card-bg);
            padding: 40px 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px -10px rgba(30, 58, 138, 0.1);
            transition: all 0.3s ease;
            border: 1px solid rgba(30, 58, 138, 0.1);
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 0;
            background: var(--gradient-green);
            transition: height 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 30px 40px -20px rgba(132, 204, 22, 0.3);
        }

        .feature-card:hover::before {
            height: 100%;
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, rgba(30, 58, 138, 0.1) 0%, rgba(132, 204, 22, 0.1) 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
            color: var(--navy-matte);
            font-size: 28px;
            transition: all 0.3s ease;
        }

        .feature-card:hover .feature-icon {
            background: var(--gradient-green);
            color: white;
        }

        .feature-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: var(--text-dark);
        }

        .feature-description {
            color: var(--text-muted);
            line-height: 1.7;
        }

        /* ===== BENEFITS SECTION ===== */
        .benefits {
            background: #ffffff;
        }

        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 40px;
            align-items: center;
        }

        .benefits-content {
            padding-right: 40px;
        }

        .benefits-title {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 20px;
            color: var(--text-dark);
        }

        .benefits-list {
            list-style: none;
            margin-top: 30px;
        }

        .benefits-list li {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            font-size: 1.1rem;
            color: var(--text-muted);
        }

        .benefits-list li i {
            width: 30px;
            height: 30px;
            background: var(--lime-green);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        .benefits-mockup {
            background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 100%);
            border-radius: 30px;
            padding: 30px;
            box-shadow: 0 30px 60px -20px rgba(30, 58, 138, 0.2);
            border: 1px solid rgba(132, 204, 22, 0.2);
        }

        .benefits-mockup h4 {
            font-size: 18px;
            margin-bottom: 20px;
            color: var(--navy-dark);
        }

        .progress-item {
            margin-bottom: 20px;
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .progress-bar {
            height: 10px;
            background: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: var(--gradient-green);
            border-radius: 10px;
            width: 0%;
            transition: width 1s ease;
        }

        /* ===== PRICING SECTION ===== */
        .pricing {
            background: linear-gradient(135deg, #f0f9ff 0%, #ffffff 100%);
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-top: 50px;
        }

        .pricing-card {
            background: white;
            border-radius: 20px;
            padding: 40px 30px;
            box-shadow: 0 10px 30px -10px rgba(30, 58, 138, 0.1);
            border: 1px solid rgba(30, 58, 138, 0.1);
            transition: all 0.3s ease;
            position: relative;
        }

        .pricing-card.popular {
            transform: scale(1.05);
            border: 2px solid var(--lime-green);
            box-shadow: 0 30px 40px -20px rgba(132, 204, 22, 0.3);
        }

        .popular-badge {
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--lime-green);
            color: white;
            padding: 5px 20px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            white-space: nowrap;
        }

        .pricing-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .pricing-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--text-dark);
        }

        .pricing-price {
            font-size: 3rem;
            font-weight: 800;
            color: var(--navy-matte);
        }

        .pricing-price span {
            font-size: 1rem;
            font-weight: 400;
            color: var(--text-muted);
        }

        .pricing-features {
            list-style: none;
            margin: 30px 0;
        }

        .pricing-features li {
            padding: 10px 0;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #e2e8f0;
            color: var(--text-muted);
        }

        .pricing-features li i {
            color: var(--lime-green);
            font-size: 14px;
        }

        .pricing-features li i.fa-xmark {
            color: #cbd5e1;
        }

        .pricing-btn {
            width: 100%;
            padding: 15px;
            border-radius: 10px;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s;
            display: block;
        }

        .pricing-card.popular .pricing-btn {
            background: var(--lime-green);
            color: var(--navy-dark);
            font-weight: 700;
        }

        .pricing-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(132, 204, 22, 0.3);
        }

        /* ===== CTA SECTION ===== */
        .cta {
            background: linear-gradient(135deg, var(--navy-matte) 0%, #1e40af 100%);
            color: white;
            text-align: center;
            padding: 80px 0;
            position: relative;
            overflow: hidden;
        }

        .cta::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHBhdHRlcm4gaWQ9ImdyaWQiIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PHBhdGggZD0iTSA2MCAwIEwgMCAwIDAgNjAiIGZpbGw9Im5vbmUiIHN0cm9rZT0icmdiYSgyNTUsMjU1LDI1NSwwLjEpIiBzdHJva2Utd2lkdGg9IjEiLz48L3BhdHRlcm4+PC9kZWZzPjxyZWN0IHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIGZpbGw9InVybCgjZ3JpZCkiLz48L3N2Zz4=');
            opacity: 0.1;
        }

        .cta-content {
            position: relative;
            z-index: 2;
            max-width: 700px;
            margin: 0 auto;
        }

        .cta-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 20px;
        }

        .cta-text {
            font-size: 1.2rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .cta-btn {
            background: var(--lime-green);
            color: var(--navy-dark);
            padding: 15px 40px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1.2rem;
            display: inline-block;
            transition: all 0.3s;
            box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.3);
        }

        .cta-btn:hover {
            background: white;
            color: var(--navy-matte);
            transform: translateY(-3px);
            box-shadow: 0 30px 40px -10px rgba(132, 204, 22, 0.4);
        }

        /* ===== FOOTER ===== */
        .footer {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: white;
            padding: 60px 0 30px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 40px;
            margin-bottom: 50px;
        }

        .footer-about p {
            color: rgba(255, 255, 255, 0.7);
            margin: 20px 0;
            line-height: 1.7;
        }

        .footer-logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .footer-logo img {
            height: 40px;
            width: auto;
        }

        .footer-logo-text {
            font-size: 1.5rem;
            font-weight: 800;
        }

        .footer-logo-text .stock {
            color: white;
        }

        .footer-logo-text .manager {
            color: var(--lime-green);
        }

        .footer-links h4 {
            font-size: 1.1rem;
            margin-bottom: 20px;
            color: white;
        }

        .footer-links ul {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.7);
            transition: color 0.3s;
        }

        .footer-links a:hover {
            color: var(--lime-green);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 30px;
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1024px) {
            .hero-title {
                font-size: 3rem;
            }

            .features-grid,
            .pricing-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .footer-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }

            .nav-menu {
                position: fixed;
                top: 90px;
                left: -100%;
                width: 100%;
                background-color: var(--navy-matte);
                flex-direction: column;
                padding: 40px 20px;
                transition: left 0.3s ease;
                box-shadow: 0 20px 30px rgba(0, 0, 0, 0.2);
            }

            .nav-menu.active {
                left: 0;
            }

            .nav-buttons {
                flex-direction: column;
                width: 100%;
            }

            .login-btn, .signup-btn {
                width: 100%;
                text-align: center;
            }

            .hero .container {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .hero-content {
                order: 2;
            }

            .hero-image {
                order: 1;
            }

            .hero-description {
                margin-left: auto;
                margin-right: auto;
            }

            .hero-buttons {
                justify-content: center;
            }

            .hero-stats {
                justify-content: center;
            }

            .features-grid,
            .pricing-grid,
            .benefits-grid {
                grid-template-columns: 1fr;
            }

            .benefits-content {
                padding-right: 0;
                text-align: center;
            }

            .benefits-list li {
                justify-content: center;
            }

            .pricing-card.popular {
                transform: scale(1);
            }
        }
    </style>
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="container">
            <div class="logo">
                <!-- Aquí va tu logo PNG -->
                <img src="assets/img/Logo_Stock_Manager.png" alt="Stock Manager Logo">
                <div class="logo-text">
                    <span class="logo-stock">Stock</span>
                    <span class="logo-manager">Manager</span>
                </div>
            </div>
            
            <div class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </div>

            <div class="nav-menu" id="navMenu">
                <a href="#features">Producto</a>
                <a href="#pricing">Precios</a>
                <a href="#contact">Contacto</a>
                
                <div class="nav-buttons">
                    <a href="login.php" class="login-btn">
                        <i class="fas fa-lock"></i> Iniciar sesión
                    </a>
                    <a href="register.php" class="signup-btn">
                        <i class="fas fa-rocket"></i> Registrarse
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">
                    El inventario de tu <span class="manager-part">PYME</span><br>
                    sin complicaciones
                </h1>
                <p class="hero-description">
                    Controla tu stock, ventas y compras en tiempo real. La solución SaaS más intuitiva y accesible para pequeñas y medianas empresas.
                </p>
                <div class="hero-buttons">
                    <a href="register.php" class="btn btn-primary">
                        <i class="fas fa-play"></i> Comenzar gratis
                    </a>
                    <a href="#features" class="btn btn-outline">
                        <i class="fas fa-video"></i> Ver demo
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="stat-item">
                        <span class="stat-number">500+</span>
                        <span class="stat-label">Empresas</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">50k+</span>
                        <span class="stat-label">Productos</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">98%</span>
                        <span class="stat-label">Satisfacción</span>
                    </div>
                </div>
            </div>
            <div class="hero-image">
                <!-- MOCKUP PROFESIONAL -->
                <div class="dashboard-mockup">
                    <div class="mockup-header">
                        <div class="mockup-logo">
                            <i class="fas fa-boxes"></i>
                            <span>StockManager</span>
                        </div>
                        <div class="mockup-date">Febrero 2026</div>
                    </div>
                    
                    <div class="mockup-stats">
                        <div class="mockup-stat">
                            <div class="value">234</div>
                            <div class="label">Productos</div>
                        </div>
                        <div class="mockup-stat">
                            <div class="value">1.2M</div>
                            <div class="label">Ventas</div>
                        </div>
                        <div class="mockup-stat">
                            <div class="value">28</div>
                            <div class="label">Alertas</div>
                        </div>
                    </div>

                    <div class="mockup-chart">
                        <div class="chart-bar"></div>
                        <div class="chart-bar"></div>
                        <div class="chart-bar"></div>
                        <div class="chart-bar"></div>
                        <div class="chart-bar"></div>
                        <div class="chart-bar"></div>
                    </div>

                    <div class="mockup-table">
                        <div class="mockup-row header">
                            <span>Producto</span>
                            <span>Stock</span>
                            <span>Estado</span>
                        </div>
                        <div class="mockup-row">
                            <span>Arroz Extra 5kg</span>
                            <span>45</span>
                            <span><span class="status">OK</span></span>
                        </div>
                        <div class="mockup-row">
                            <span>Aceite Vegetal</span>
                            <span>12</span>
                            <span><span class="status" style="background: #f59e0b;">BAJO</span></span>
                        </div>
                        <div class="mockup-row">
                            <span>Leche Entera</span>
                            <span>8</span>
                            <span><span class="status" style="background: #ef4444;">CRÍTICO</span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURES SECTION -->
    <section class="features section" id="features">
        <div class="container">
            <h2 class="section-title">Todo lo que necesitas en un solo lugar</h2>
            <p class="section-subtitle">
                Diseñado específicamente para PYMES. Sin complejidades, sin costos ocultos.
            </p>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <h3 class="feature-title">Control de Inventario</h3>
                    <p class="feature-description">
                        Gestión completa de productos, categorías y stock en tiempo real. Alertas automáticas cuando algo se agota.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-cash-register"></i>
                    </div>
                    <h3 class="feature-title">Punto de Venta</h3>
                    <p class="feature-description">
                        Registra ventas de forma rápida e intuitiva. Genera facturas, tickets y gestiona clientes fácilmente.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h3 class="feature-title">Gestión de Compras</h3>
                    <p class="feature-description">
                        Crea órdenes de compra, gestiona proveedores y recibe mercancía actualizando automáticamente el stock.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="feature-title">Reportes y Analíticas</h3>
                    <p class="feature-description">
                        Genera reportes de ventas, compras e inventario. Exporta a PDF y Excel con un solo clic.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="feature-title">Múltiples Usuarios</h3>
                    <p class="feature-description">
                        Gestión de roles y permisos. Define qué puede ver y hacer cada miembro de tu equipo.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-cloud"></i>
                    </div>
                    <h3 class="feature-title">Acceso en la Nube</h3>
                    <p class="feature-description">
                        Accede a tu negocio desde cualquier lugar. Disponible en PC, tablet y smartphone.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- BENEFITS SECTION -->
    <section class="benefits section">
        <div class="container">
            <div class="benefits-grid">
                <div class="benefits-content">
                    <h2 class="benefits-title">
                        ¿Por qué las PYMES eligen Stock Manager?
                    </h2>
                    <p style="color: var(--text-muted); margin-bottom: 30px;">
                        Diseñado pensando en la simplicidad y la eficiencia. Miles de empresas ya confían en nosotros.
                    </p>
                    
                    <ul class="benefits-list">
                        <li>
                            <i class="fas fa-check"></i>
                            <span><strong>Ahorra tiempo:</strong> Reduce un 70% el tiempo dedicado al inventario</span>
                        </li>
                        <li>
                            <i class="fas fa-check"></i>
                            <span><strong>Evita pérdidas:</strong> Alertas de stock bajo y productos caducados</span>
                        </li>
                        <li>
                            <i class="fas fa-check"></i>
                            <span><strong>Aumenta ventas:</strong> Nunca te quedes sin tu producto estrella</span>
                        </li>
                        <li>
                            <i class="fas fa-check"></i>
                            <span><strong>Sin complicaciones:</strong> Aprende a usarlo en menos de 1 hora</span>
                        </li>
                    </ul>
                </div>
                <div class="benefits-mockup">
                    <h4>Métricas en tiempo real</h4>
                    
                    <div class="progress-item">
                        <div class="progress-header">
                            <span>Rotación de inventario</span>
                            <span>85%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 85%;"></div>
                        </div>
                    </div>

                    <div class="progress-item">
                        <div class="progress-header">
                            <span>Precisión de stock</span>
                            <span>96%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 96%;"></div>
                        </div>
                    </div>

                    <div class="progress-item">
                        <div class="progress-header">
                            <span>Ventas mensuales</span>
                            <span>+23%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 73%;"></div>
                        </div>
                    </div>

                    <div style="margin-top: 30px; display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div style="background: #f8fafc; padding: 15px; border-radius: 12px; text-align: center;">
                            <div style="font-size: 24px; font-weight: 700; color: var(--navy-matte);">500+</div>
                            <div style="font-size: 12px; color: var(--text-muted);">Empresas activas</div>
                        </div>
                        <div style="background: #f8fafc; padding: 15px; border-radius: 12px; text-align: center;">
                            <div style="font-size: 24px; font-weight: 700; color: var(--navy-matte);">50k</div>
                            <div style="font-size: 12px; color: var(--text-muted);">Productos gestionados</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- PRICING SECTION - ACTUALIZADA SEGÚN TUS ESPECIFICACIONES -->
    <section class="pricing section" id="pricing">
        <div class="container">
            <h2 class="section-title">Precios claros y sin sorpresas</h2>
            <p class="section-subtitle">
                Elige el plan que mejor se adapte a tu negocio. Cancela cuando quieras.
            </p>
            
            <div class="pricing-grid">
                <!-- PLAN BÁSICO -->
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3 class="pricing-name">Básico</h3>
                        <div class="pricing-price">0<span>/mes</span></div>
                    </div>
                    <ul class="pricing-features">
                        <li><i class="fas fa-check"></i> Hasta 50 productos</li>
                        <li><i class="fas fa-check"></i> Hasta 2 usuarios</li>
                        <li><i class="fas fa-check"></i> Gestión de inventario</li>
                        <li><i class="fas fa-xmark"></i> <span style="color: var(--text-muted);">Reportes avanzados</span></li>
                        <li><i class="fas fa-xmark"></i> <span style="color: var(--text-muted);">Múltiples almacenes</span></li>
                        <li><i class="fas fa-xmark"></i> <span style="color: var(--text-muted);">Soporte prioritario</span></li>
                    </ul>
                    <a href="register.php" class="pricing-btn btn-outline">Comenzar gratis</a>
                </div>

                <!-- PLAN PROFESIONAL -->
                <div class="pricing-card popular">
                    <div class="popular-badge">MÁS POPULAR</div>
                    <div class="pricing-header">
                        <h3 class="pricing-name">Profesional</h3>
                        <div class="pricing-price">7.500<span>/mes</span></div>
                    </div>
                    <ul class="pricing-features">
                        <li><i class="fas fa-check"></i> Productos ilimitados</li>
                        <li><i class="fas fa-check"></i> Hasta 5 usuarios</li>
                        <li><i class="fas fa-check"></i> Gestión de inventario</li>
                        <li><i class="fas fa-check"></i> Reportes avanzados</li>
                        <li><i class="fas fa-check"></i> Múltiples almacenes</li>
                        <li><i class="fas fa-xmark"></i> <span style="color: var(--text-muted);">Soporte prioritario</span></li>
                    </ul>
                    <a href="register.php" class="pricing-btn btn-primary">Elegir Profesional</a>
                </div>

                <!-- PLAN PREMIUM -->
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3 class="pricing-name">Premium</h3>
                        <div class="pricing-price">15.000<span>/mes</span></div>
                    </div>
                    <ul class="pricing-features">
                        <li><i class="fas fa-check"></i> Productos ilimitados</li>
                        <li><i class="fas fa-check"></i> Usuarios ilimitados</li>
                        <li><i class="fas fa-check"></i> Gestión de inventario</li>
                        <li><i class="fas fa-check"></i> Reportes avanzados</li>
                        <li><i class="fas fa-check"></i> Múltiples almacenes</li>
                        <li><i class="fas fa-check"></i> Soporte prioritario</li>
                    </ul>
                    <a href="register.php" class="pricing-btn btn-outline">Elegir Premium</a>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA SECTION -->
    <section class="cta" id="contact">
        <div class="container">
            <div class="cta-content">
                <h2 class="cta-title">¿Listo para optimizar tu inventario?</h2>
                <p class="cta-text">
                    Únete a más de 500 PYMES que ya confían en Stock Manager. Comienza hoy con nuestro plan gratuito.
                </p>
                <a href="register.php" class="cta-btn">
                    <i class="fas fa-rocket"></i> Probar gratis 30 días
                </a>
                <p style="margin-top: 20px; opacity: 0.8;">
                    <i class="fas fa-check-circle"></i> Sin compromiso - Cancela cuando quieras
                </p>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-about">
                    <div class="footer-logo">
                        <!-- Aquí va tu logo PNG -->
                        <img src="assets/img/Logo_Stock_Manager.png" alt="Stock Manager Logo">
                        <div class="footer-logo-text">
                            <span class="stock">Stock</span>
                            <span class="manager">Manager</span>
                        </div>
                    </div>
                    <p>
                        La solución SaaS más intuitiva para la gestión de inventario en PYMES. Simplificamos tu operación diaria.
                    </p>
                </div>

                <div class="footer-links">
                    <h4>Producto</h4>
                    <ul>
                        <li><a href="#features">Características</a></li>
                        <li><a href="#pricing">Precios</a></li>
                        <li><a href="#">Demo</a></li>
                        <li><a href="#">Actualizaciones</a></li>
                    </ul>
                </div>

                <div class="footer-links">
                    <h4>Compañía</h4>
                    <ul>
                        <li><a href="#">Sobre nosotros</a></li>
                        <li><a href="#">Equipo</a></li>
                        <li><a href="#">Blog</a></li>
                    </ul>
                </div>

                <div class="footer-links">
                    <h4>Soporte</h4>
                    <ul>
                        <li><a href="#">Centro de ayuda</a></li>
                        <li><a href="#">Documentación</a></li>
                        <li><a href="#">Videotutoriales</a></li>
                        <li><a href="#">Contacto</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2026 Stock Manager S.L. Todos los derechos reservados.</p>
                <p style="margin-top: 10px;">
                    <a href="#" style="color: rgba(255, 255, 255, 0.6);">Términos y condiciones</a> | 
                    <a href="#" style="color: rgba(255, 255, 255, 0.6);">Política de privacidad</a>
                </p>
            </div>
        </div>
    </footer>

    <!-- SCRIPTS -->
    <script>
        // Mobile menu toggle
        const menuToggle = document.getElementById('menuToggle');
        const navMenu = document.getElementById('navMenu');

        menuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    // Cerrar menú móvil si está abierto
                    navMenu.classList.remove('active');
                }
            });
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!menuToggle.contains(event.target) && !navMenu.contains(event.target)) {
                navMenu.classList.remove('active');
            }
        });
    </script>
</body>
</html>
<?php
// No hay lógica PHP adicional aquí, pero el archivo debe terminar con la extensión .php
// para poder ser ejecutado en un servidor con PHP.
?>