<?php
session_start(); // Démarrer la session

$config = require '/home/ewenevh/config/db_config.php';

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
    <link href="index.css" rel="stylesheet">
    <link rel="icon" href="image/logo.png">
    <title>Commande confirmée - Amap'Market</title>
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
                <a class="nav-link" href="index.php">Accueil</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="produits.php">Produits</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="panier.php">Panier</a>
              </li>
              <?php 
               if (isset($_SESSION['id_client'])) {
                $clientReq = $connexion->prepare("SELECT prenom_client, nom_client FROM client WHERE id_client = :id");
                $clientReq->execute(['id' => $_SESSION['id_client']]);
                $client = $clientReq->fetch();

                if ($client) {
                    echo '<li class="nav-item">
                            <a class="nav-link actif" style="color: red;" href="connexion.php">Connecté : ' . htmlspecialchars($client['prenom_client']) . ' ' . htmlspecialchars($client['nom_client']) . '</a>
                          </li>';
                }
            } else {
                echo '<li class="nav-item">
                        <a class="nav-link actif" href="connexion.php">Connexion</a>
                      </li>';
            }
              ?>
            </ul>
          </div>
        </div>
      </nav>

<br><br><br><br><br>

<div class="container my-4">
    <div class="row">
        <div class="col panel panel-white text-center">
            <h1>Confirmation de commande</h1>
            <p>Votre commande a été confirmée avec succès !</p>
            <a href="index.php">
                <img src="image/logo.png" alt="Logo" class="logo">
            </a>
            <p>Merci pour votre achat. <br>Cliquez sur le logo pour revenir à l'accueil.</p>
        </div>
    </div>
</div>

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
</body>
</html>