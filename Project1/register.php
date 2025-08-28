<?php
session_start();
include("db_config.php");

$message = "";

// Proses register
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nip = trim($_POST['nip']);
    $nama = trim($_POST['nama_lengkap']);
    $jabatan =trim($_POST['jabatan']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = "pegawai"; // default role pegawai

    // Cek apakah NIP atau username sudah terdaftar
    $stmt = $conn->prepare("SELECT * FROM users WHERE nip = ? OR username = ?");
    $stmt->bind_param("ss", $nip, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "NIP atau Username sudah terdaftar!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (nip, nama_lengkap,jabatan, username, password, role) VALUES (?,?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $nip, $nama, $jabatan, $username, $password, $role);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Registrasi berhasil! Silakan login.";
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error'] = "Terjadi kesalahan, coba lagi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register | Aplikasi Monitoring dan Evaluasi Pegawai</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* === Global Styles === */
        :root {
            --primary: #3498db;
            --secondary: #2c3e50;
            --accent: #1abc9c;
            --danger: #e74c3c;
            --light: #ecf0f1;
            --dark: #34495e;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, rgba(28, 31, 34, 0.9), rgba(43, 49, 48, 0.85)), 
                        url('bg2.jpg') no-repeat center/cover fixed;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
        }

        /* === Floating Particles === */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: none;
        }

        /* === Card Glassmorphism === */
        .register-box {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            padding: 40px;
            border-radius: 20px;
            width: 420px;
            box-shadow: 0 25px 45px rgba(0,0,0,0.25);
            text-align: center;
            color: white;
            z-index: 2;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.1);
            animation: fadeInUp 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
            opacity: 0;
            transform: translateY(30px);
        }

        .register-box::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                to bottom right,
                rgba(255,255,255,0.1) 0%,
                rgba(255,255,255,0) 50%,
                rgba(255,255,255,0.1) 100%
            );
            transform: rotate(30deg);
            z-index: -1;
            animation: shine 8s infinite linear;
        }

        .register-box img {
            width: 100px;
            margin-bottom: 15px;
            transition: var(--transition);
            filter: drop-shadow(0 5px 10px rgba(0,0,0,0.2));
        }
        .register-box img:hover {
            transform: scale(1.1) rotate(5deg);
        }

        .register-box h2 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
            background: linear-gradient(to right, #fff, #1abc9c);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .register-box p {
            font-size: 14px;
            font-weight: 400;
            margin-bottom: 25px;
            opacity: 0.85;
            line-height: 1.6;
        }

        /* Input Fields */
        .form-group {
            text-align: left;
            margin-bottom: 20px;
            position: relative;
        }
        .form-group label {
            font-size: 14px;
            font-weight: 500;
            display: block;
            margin-bottom: 8px;
            color: rgba(255,255,255,0.9);
        }
        .form-group input {
            width: 100%;
            padding: 14px 14px 14px 40px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transition: var(--transition);
            border: 1px solid rgba(255,255,255,0.1);
        }
        .form-group input::placeholder {
            color: rgba(255,255,255,0.6);
        }
        .form-group input:focus {
            background: rgba(255, 255, 255, 0.25);
            box-shadow: 0 0 0 3px rgba(26, 188, 156, 0.2);
        }
        .form-group i {
            position: absolute;
            left: 15px;
            top: 40px;
            color: rgba(255,255,255,0.7);
            font-size: 16px;
        }

        /* Error */
        .error {
            background: rgba(231, 76, 60, 0.2);
            color: #ffb3b3;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 15px;
            animation: shake 0.4s ease;
            border: 1px solid rgba(231, 76, 60, 0.3);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .error i {
            font-size: 18px;
        }

        /* Button */
        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 16px;
            color: white;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(26, 188, 156, 0.3);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }
        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(26, 188, 156, 0.4);
        }
        button:active {
            transform: translateY(1px);
        }
        button::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -60%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                to bottom right,
                rgba(255,255,255,0.3) 0%,
                rgba(255,255,255,0) 60%
            );
            transform: rotate(30deg);
            transition: var(--transition);
        }
        button:hover::after {
            left: 100%;
        }

        /* Login link */
        .login-link {
            margin-top: 20px;
            font-size: 14px;
            color: rgba(255,255,255,0.8);
        }
        .login-link a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }
        .login-link a:hover {
            text-decoration: underline;
            color: #16a085;
        }

        /* Animations */
        @keyframes fadeInUp {
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes shake {
            0% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            50% { transform: translateX(5px); }
            75% { transform: translateX(-5px); }
            100% { transform: translateX(0); }
        }
        @keyframes shine {
            0% { transform: rotate(30deg) translate(-30%, -30%); }
            100% { transform: rotate(30deg) translate(30%, 30%); }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        /* Floating elements */
        .floating-elements {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: 1;
        }
        .floating {
            position: absolute;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            filter: blur(5px);
            animation: float 6s ease-in-out infinite;
        }
        .floating:nth-child(1) {
            width: 150px;
            height: 150px;
            top: 10%;
            left: 5%;
            animation-delay: 0s;
        }
        .floating:nth-child(2) {
            width: 200px;
            height: 200px;
            top: 60%;
            left: 80%;
            animation-delay: 2s;
        }
        .floating:nth-child(3) {
            width: 100px;
            height: 100px;
            top: 80%;
            left: 10%;
            animation-delay: 4s;
        }

        /* Mobile */
        @media screen and (max-width: 480px) {
            .register-box {
                width: 90%;
                padding: 30px;
            }
            
            .register-box h2 {
                font-size: 22px;
            }
            
            .form-group input {
                padding: 12px 12px 12px 38px;
            }
        }

        /* Password strength indicator */
        .password-strength {
            margin-top: 5px;
            height: 5px;
            border-radius: 3px;
            background: rgba(255,255,255,0.1);
            overflow: hidden;
        }
        .strength-meter {
            height: 100%;
            width: 0;
            transition: width 0.3s ease;
        }
        .weak { background: #e74c3c; width: 30%; }
        .medium { background: #f39c12; width: 60%; }
        .strong { background: #2ecc71; width: 100%; }
    </style>
</head>
<body>
    <!-- Floating background elements -->
    <div class="floating-elements">
        <div class="floating"></div>
        <div class="floating"></div>
        <div class="floating"></div>
    </div>

    <!-- Floating particles canvas -->
    <canvas class="particles" id="particles"></canvas>

    <!-- Register Box -->
    <div class="register-box">
        <img src="logo2.png" alt="Logo Dinas Pendidikan Kota Kupang" class="logo">
        <h2>Buat Akun Baru</h2>
        <p>Silakan isi formulir berikut untuk mendaftar sebagai pegawai</p>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= $_SESSION['error']; unset($_SESSION['error']); ?></span>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST" id="registerForm">
            <div class="form-group">
                <label for="nip">NIP</label>
                <i class="fas fa-id-card"></i>
                <input type="text" name="nip" id="nip" placeholder="Masukkan NIP" required>
            </div>

            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap</label>
                <i class="fas fa-user"></i>
                <input type="text" name="nama_lengkap" id="nama_lengkap" placeholder="Masukkan nama lengkap" required>
            </div>

            <div class="form-group">
                <label for="jabatan">Jabatan</label>
                <i class="fas fa-id-badge"></i>
                <input type="text" name="jabatan" id="jabatan" placeholder="Masukkan Jabatan" required>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <i class="fas fa-at"></i>
                <input type="text" name="username" id="username" placeholder="Masukkan username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="password" placeholder="Masukkan password" required>
                <div class="password-strength">
                    <div class="strength-meter" id="strength-meter"></div>
                </div>
            </div>

            <button type="submit" id="registerBtn">
                <i class="fas fa-user-plus"></i>
                <span id="btnText">Daftar</span>
            </button>
        </form>

        <div class="login-link">
            Sudah punya akun? <a href="index.php">Login disini</a>
        </div>
    </div>

    <script>
        // Floating particles effect
        document.addEventListener('DOMContentLoaded', function() {
            const canvas = document.getElementById('particles');
            const ctx = canvas.getContext('2d');
            
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
            
            const particles = [];
            const particleCount = Math.floor(window.innerWidth / 10);
            
            for (let i = 0; i < particleCount; i++) {
                particles.push({
                    x: Math.random() * canvas.width,
                    y: Math.random() * canvas.height,
                    size: Math.random() * 3 + 1,
                    speedX: Math.random() * 0.5 - 0.25,
                    speedY: Math.random() * 0.5 - 0.25,
                    color: `rgba(255, 255, 255, ${Math.random() * 0.2 + 0.1})`
                });
            }
            
            function animateParticles() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                
                for (let i = 0; i < particles.length; i++) {
                    const p = particles[i];
                    
                    p.x += p.speedX;
                    p.y += p.speedY;
                    
                    if (p.x < 0 || p.x > canvas.width) p.speedX *= -1;
                    if (p.y < 0 || p.y > canvas.height) p.speedY *= -1;
                    
                    ctx.fillStyle = p.color;
                    ctx.beginPath();
                    ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
                    ctx.fill();
                }
                
                requestAnimationFrame(animateParticles);
            }
            
            animateParticles();
            
            window.addEventListener('resize', function() {
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;
            });
        });

        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthMeter = document.getElementById('strength-meter');
            let strength = 0;
            
            // Check password length
            if (password.length > 0) strength += 1;
            if (password.length >= 6) strength += 1;
            if (password.length >= 8) strength += 1;
            
            // Check for mixed case
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 1;
            
            // Check for numbers
            if (/\d/.test(password)) strength += 1;
            
            // Check for special chars
            if (/[^a-zA-Z0-9]/.test(password)) strength += 1;
            
            // Update strength meter
            strengthMeter.className = 'strength-meter';
            if (password.length === 0) {
                strengthMeter.style.width = '0';
            } else if (strength <= 2) {
                strengthMeter.classList.add('weak');
            } else if (strength <= 4) {
                strengthMeter.classList.add('medium');
            } else {
                strengthMeter.classList.add('strong');
            }
        });

        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const nip = document.getElementById("nip").value.trim();
            const nama = document.getElementById("nama_lengkap").value.trim();
            const username = document.getElementById("username").value.trim();
            const password = document.getElementById("password").value.trim();
            const btn = document.getElementById("registerBtn");
            const btnText = document.getElementById("btnText");
            
            // NIP validation (8 digits)
            if (nip.length < 8 || isNaN(nip)) {
                alert("NIP harus berupa angka minimal 8 digit!");
                return false;
            }
            
            // Password validation (min 6 chars)
            if (password.length < 6) {
                alert("Password minimal 6 karakter!");
                return false;
            }
            
            // Loading state
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Mendaftarkan...</span>';
            
            // Submit form
            this.submit();
            
            return true;
        });

        // Input focus effects
        document.querySelectorAll('.form-group input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.querySelector('i').style.color = '#1abc9c';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.querySelector('i').style.color = 'rgba(255,255,255,0.7)';
            });
        });

        // Logo animation
        const logo = document.querySelector('.logo');
        logo.addEventListener('mouseenter', () => {
            logo.style.transform = 'scale(1.1) rotate(5deg)';
            logo.style.filter = 'drop-shadow(0 8px 15px rgba(0,0,0,0.3))';
        });
        
        logo.addEventListener('mouseleave', () => {
            logo.style.transform = 'scale(1) rotate(0)';
            logo.style.filter = 'drop-shadow(0 5px 10px rgba(0,0,0,0.2))';
        });

        // Background animation on load
        document.body.style.opacity = '0';
        setTimeout(() => {
            document.body.style.transition = 'opacity 1s ease';
            document.body.style.opacity = '1';
        }, 100);
    </script>
</body>
</html>