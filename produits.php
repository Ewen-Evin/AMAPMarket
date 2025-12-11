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
$connexion->query("SET CHARACTER SET utf8");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
      window.clientId = <?php echo isset($_SESSION['id_client']) ? $_SESSION['id_client'] : 'null'; ?>;
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css"  rel="stylesheet">
    <link rel="website icon" href="image/logo.png">
    <title>Produits - Amap'Market</title>
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
                <a class="nav-link actif" href="produits.php">Produits</a>
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

      <div class="row">
        <div class="col panel panel-white">
          <h1>Composez votre panier <br>de fruits et légumes frais et locaux</h1>
        </div>
      </div>
            
<br><br>
<div class="row">
<?php
  $sql = "SELECT * FROM {$config['db_prefix']}produit WHERE stock_produit > 0 ORDER BY nom_produit"; 
  $reponse = $connexion->query($sql);
  if (!$reponse) {
    echo "<p>Erreur lors de la récupération des produits.</p>";
  } else {
    while ($lignes = $reponse->fetch(PDO::FETCH_ASSOC)){
      echo '<div class="col-12 col-sm-6 col-md-4 panel">
        <img class="image" src="image/'. strtolower($lignes["nom_produit"]).strtolower($lignes["variete_produit"]).'.webp" alt="'. $lignes["nom_produit"]. " ".$lignes["variete_produit"].'">
        <div class="text-overlay">'. $lignes["nom_produit"] .'
        <br>
        '.$lignes["variete_produit"].' </div>
        <div class="panel panel-white">
          '. $lignes["prix_produit"] .'€/Kg
          <br><br>
          <button class="btn-green panier" data-product="'. $lignes["nom_produit"].'"data-variety="'.$lignes["variete_produit"] .'" data-price="'. $lignes["prix_produit"] .'">Ajouter au panier</button>
        </div>
      </div>';
    }
  }
?>
<br><br>

</div></div>
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

      <!-- JavaScript de Bootstrap -->
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
      <script src="app.js"></script>
</body>
</html>