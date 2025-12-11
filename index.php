<?php
session_start();

$config = require './config/db_config.php';

try {
    $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false
    ];

    $connexion = new PDO($dsn, $config['db_user'], $config['db_pass'], $options);

} catch (PDOException $e) {
    error_log("Erreur de connexion DB: " . $e->getMessage());
    die("Erreur de connexion à la base.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <link rel="website icon" href="image/logo.png">
    <title>Accueil - Amap'Market</title>
    <style>
        .text {
            font-size: 1.2rem;
            line-height: 1.6;
        }

        .veggie-animation {
            text-align: center;
            margin: 2rem 0;
        }

        .veggie-animation img {
            width: 100px;
            height: auto;
            animation: bounce 2s infinite ease-in-out;
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-20px);
            }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg bar">
        <div class="container-fluid">
          <a class="navbar-brand" href="index.php">
            <img src="image/logo.png" alt="Logo" height="80px">
            Amap'Market
          </a>
    
          <!-- Bouton hamburger pour les petits écrans -->
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
    
          <!-- Liens de navigation -->
          <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
              <li class="nav-item">
                <a class="nav-link actif" href="index.php">Accueil</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="produits.php">Produits</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="panier.php">Panier</a>
              </li>
              <?php 
               if (isset($_SESSION['id_client'])) {
                $clientReq = $connexion->prepare("SELECT prenom_client, nom_client FROM {$config['db_prefix']}client WHERE id_client = :id");
                $clientReq->execute(['id' => $_SESSION['id_client']]);
                $client = $clientReq->fetch();

                if ($client) {
                    echo '<li class="nav-item">
                            <a class="nav-link" style="color: red;" href="connexion.php">Connecté : ' . htmlspecialchars($client['prenom_client']) . ' ' . htmlspecialchars($client['nom_client']) . '</a>
                          </li>';
                }
            } else {
                echo '<li class="nav-item">
                        <a class="nav-link" href="connexion.php">Connexion</a>
                      </li>';
            }
              ?>
            </ul>
          </div>
        </div>
      </nav>

    <br><br><br><br><br>
    <div class="container my-4">
        <?php if (isset($_SESSION['user_email'])): ?>
            <div class="alert alert-success">
                Bienvenue, <?= htmlspecialchars($_SESSION['user_email']); ?> !
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col panel panel-white">
                <h1>Amap'Market</h1>
            </div>
        </div>

        <div class="row">
            <div class="col panel panel-white">
                <div class="text">
                    Bienvenue sur Amap'Market, votre plateforme dédiée aux paniers de légumes frais, locaux et de saison. En collaboration avec des producteurs engagés, nous vous proposons une alimentation saine et responsable, directement issue d’une agriculture respectueuse de l’environnement. Ici, vous pouvez commander votre panier selon vos besoins, opter pour un abonnement régulier ou une commande ponctuelle, et choisir entre différents modes de retrait ou de livraison. En rejoignant Amap'Market, vous soutenez les agriculteurs de votre région tout en profitant de produits de qualité, cultivés sans pesticides ni intermédiaires. Découvrez nos offres et faites le choix d’une consommation plus locale et durable.
                </div>
                <div class="veggie-animation">
                    <?php
                    $products = ['Carotte', 'Tomate', 'Poivronrouge', 'Courgette'];
                    foreach ($products as $product) {
                        echo '<img src="image/' . strtolower($product) . '.webp" alt="' . ucfirst($product) . '">';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <br><br>
    <br><br>
    <footer class="bg-dark text-white py-4 mt-auto">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Contact</h5>
                    <p><strong>Email :</strong> amap'market@amap.com </p>
                    <p><strong>Téléphone :</strong> 06 12 34 56 78 </p>
                    <p><strong>Adresse :</strong> 3 Rue Henri Hure, <br> 49300 Cholet, France </p>
                </div>
                <div class="col-md-4">
                    <h5>À propos</h5>
                    <p>Amap'Market</p>
                </div>
                <div class="col-md-4">
                    <h5>Suivez-nous</h5>
                    <a href="https://www.facebook.com" class="text-white">
                        <img class="logo" src="image/facebook.png">
                    </a><br>
                    <a href="https://www.instagram.com" class="text-white">
                        <img class="logo" src="image/insta.webp">
                    </a><br>
                    <a href="https://www.x.com" class="text-white">
                        <img class="logo" src="image/x.webp">
                    </a>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
            <p>&copy; 2025 Amap'Market. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>