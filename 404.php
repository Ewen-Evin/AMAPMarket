<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>404 - Page non trouvée | Amap'Market</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="image/logo.png">
    <style>
        body {
            background-color: #e6ddca;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .error-container {
            max-width: 500px;
            margin: auto;
            background: #fff;
            border-radius: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
            padding: 2.5rem 2rem 2rem 2rem;
            text-align: center;
            color: #288b00;
            position: relative;
        }
        .error-logo {
            width: 80px;
            margin-bottom: 1rem;
        }
        .error-code {
            font-size: 5rem;
            font-weight: bold;
            color: #218838;
            margin-bottom: 0.5rem;
            letter-spacing: 2px;
        }
        .error-title {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: #288b00;
        }
        .error-message {
            font-size: 1.1rem;
            color: #555;
            margin-bottom: 2rem;
        }
        .error-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-green {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 0.5rem 1.2rem;
            border-radius: 5rem;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.2s;
        }
        .btn-green:hover {
            background-color: #218838;
            color: #fff;
        }
        .btn-outline {
            background: none;
            border: 2px solid #28a745;
            color: #28a745;
            padding: 0.5rem 1.2rem;
            border-radius: 5rem;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.2s, color 0.2s;
        }
        .btn-outline:hover {
            background: #28a745;
            color: #fff;
        }
        @media (max-width: 600px) {
            .error-container {
                padding: 1.5rem 0.5rem;
            }
            .error-code {
                font-size: 3.5rem;
            }
            .error-title {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <img src="image/logo.png" alt="Logo Amap'Market" class="error-logo">
        <div class="error-code">404</div>
        <div class="error-title">Oups ! Page non trouvée</div>
        <div class="error-message">
            La page que vous recherchez n'existe pas ou a été déplacée.<br>
            Retournez à l'accueil ou revenez à la page précédente.
        </div>
        <div class="error-actions">
            <a href="index.php" class="btn-green">Accueil</a>
            <button class="btn-outline" id="back-btn">Revenir en arrière</button>
        </div>
    </div>
    <script>
        document.getElementById('back-btn').onclick = function(e) {
            e.preventDefault();
            if (window.history.length > 1) {
                window.history.back();
                setTimeout(function() {
                    if (document.referrer === "" || document.referrer === window.location.href) {
                        window.location.href = "index.php";
                    }
                }, 500);
            } else {
                window.location.href = "index.php";
            }
        };
    </script>
</body>
</html>