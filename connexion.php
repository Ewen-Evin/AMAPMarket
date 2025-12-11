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

  if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();

    // On redémarre une session vide pour afficher une alerte proprement
    session_start();
    $_SESSION['logout_success'] = true;

    // Recharge la page pour appliquer les changements (et effacer le POST)
    header("Location: connexion.php");
    exit;
}

$isGestion = false;
$isGestionTesteur = false;
if (isset($_SESSION['id_client'])) {
    $profilReq = $connexion->prepare("
        SELECT p.type_profil 
        FROM {$config['db_prefix']}profil p
        INNER JOIN {$config['db_prefix']}client c ON p.login = c.email_client
        WHERE c.id_client = :id
    ");
    $profilReq->execute(['id' => $_SESSION['id_client']]);
    $profil = $profilReq->fetch();
    if ($profil) {
        if ($profil['type_profil'] === 'gestion') {
            $isGestion = true;
        }
        if ($profil['type_profil'] === 'gestion_testeur') {
            $isGestionTesteur = true;
        }
    }
}


if (isset($_SESSION['inscription_success']) && $_SESSION['inscription_success'] === true) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Inscription réussie !',
                text: 'Votre compte a été créé avec succès.',
                icon: 'success',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });
        });
    </script>";
    unset($_SESSION['inscription_success']);
}

if (isset($_SESSION['alerte_connexion'])) {
  echo "<script>
      document.addEventListener('DOMContentLoaded', function() {
          Swal.fire({
              title: 'Accès refusé',
              text: 'Vous devez être connecté pour accéder à votre panier.',
              icon: 'warning',
              confirmButtonColor: '#3085d6',
              confirmButtonText: 'OK'
          });
      });
  </script>";
  unset($_SESSION['alerte_connexion']);
}

if (isset($_SESSION['logout_success'])) {
  echo "<script>
      document.addEventListener('DOMContentLoaded', function() {
          Swal.fire({
              title: 'Déconnexion réussie',
              text: 'Vous avez été déconnecté.',
              icon: 'success',
              confirmButtonColor: '#3085d6',
              confirmButtonText: 'OK'
          });
      });
  </script>";
  unset($_SESSION['logout_success']);
}

if (isset($_SESSION['inscription_error'])) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Erreur',
                text: '" . $_SESSION['inscription_error'] . "',
                icon: 'error',
                confirmButtonColor: '#d33',
                confirmButtonText: 'OK'
            });
        });
    </script>";
    unset($_SESSION['inscription_error']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <link rel="icon" href="image/logo.png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="app.js" defer></script>
    <title>Connexion - Amap'Market</title>
</head>

<body class="d-flex flex-column min-vh-100">
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
                $clientReq = $connexion->prepare("SELECT prenom_client, nom_client FROM {$config['db_prefix']}client WHERE id_client = :id");
                $clientReq->execute(['id' => $_SESSION['id_client']]);
                $client = $clientReq->fetch();

                if ($client) {
                    echo '<li class="nav-item">
                            <span class="nav-link actif">Connecté : ' . htmlspecialchars($client['prenom_client']) . ' ' . htmlspecialchars($client['nom_client']) . '</span>
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



<main class="flex-grow-1">
<div class="container my-4">
    <div class="row">
        <div class="col panel">
            <img src="image/logo.png" class="logo-co">
        </div>
    </div>
    <!-----[CONNECTÉ]------->
    <?php 
    if (isset($_SESSION['id_client'])) {
      $clientReq = $connexion->prepare("SELECT * FROM {$config['db_prefix']}client WHERE id_client = :id");
      $clientReq->execute(['id' => $_SESSION['id_client']]);
      $client = $clientReq->fetch();

      echo '<div class="row">
              <div class="col-md-10 offset-md-1 panel panel-white text-center">
                <h2>Vous êtes déjà connecté !</h2>';

      if ($client) {
        echo '<p>Connecté en tant que compte '. $profil['type_profil'] .': <strong>'. htmlspecialchars($client['prenom_client']) . ' ' . htmlspecialchars($client['nom_client']) . '</strong></p>';
      }

      if ($isGestion || $isGestionTesteur): ?>
        <div class="row mb-3">
            <div class="col text-center">
                <button type="button" class="btn btn-primary" onclick="window.location.href='gestion.php'">Accès Gestion</button>
            </div>
        </div>
    <?php endif;

      echo '  <form method="POST" style="display:inline;">
                <button type="submit" name="logout" class="btn btn-danger">Se déconnecter</button>
              </form>
            </div>
          </div>';
    } else {?>
    <!-----[INSCRIPTION]------->
      <div class="row inscription" id="inscription" style="display: none">
        <div class="col-lg-10 offset-lg-1 col-md-10 offset-md-1 panel panel-white">
            <h1>Inscription</h1>
            <form class="inscription" method="POST" action="traitement_inscription.php">
                <div class="mb-3 row">
                    <label for="nom" class="col-sm-2 col-form-label">Nom : </label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="nom" name="nom" required>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="prenom" class="col-sm-2 col-form-label">Prénom : </label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="prenom" name="prenom" required>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="email" class="col-sm-2 col-form-label">Adresse mail : </label>
                    <div class="col-sm-10">
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="adresse" class="col-sm-2 col-form-label">Adresse : </label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="adresse" name="adresse" required>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="password" class="col-sm-2 col-form-label">Mot de passe:</label>
                    <div class="col-sm-10">
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>
                <div class="d-flex justify-content-center">
                    <button type="submit" name="envoie-inscription" class="btn-green btn-primary btn-inscription">S'inscrire</button>
                </div>
            </form>
            <br>
            <div>Si vous avez déja un compte : <u style="cursor: pointer;" onclick="toggleForm('connexion')">Connectez-vous</u></div>
        </div>
        <div class="col-lg-1 col-md-0 panel"></div>
    </div>

    <?php
      if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST['email'];
        $password = $_POST['password'];
        if ($email != '' && $password != '') {
          $req = $connexion->prepare("SELECT * FROM {$config['db_prefix']}profil WHERE login = :email");
          $req->execute(['email' => $email]);
          $rep = $req->fetch();
          if ($rep && password_verify($password, $rep['mot_de_passe'])) {
            $clientReq = $connexion->prepare("SELECT id_client FROM {$config['db_prefix']}client WHERE email_client = :email");
            $clientReq->execute(['email' => $email]);
            $client = $clientReq->fetch();

            if ($client) {
                $_SESSION['id_client'] = $client['id_client'];

                $panierReq = $connexion->prepare("SELECT * FROM {$config['db_prefix']}panier WHERE id_client = :id_client AND statut_panier = 'en_cours'");
                $panierReq->execute(['id_client' => $client['id_client']]);
                $panier = $panierReq->fetchAll();

                if ($panier) {
                    $_SESSION['panier'] = $panier;
                }
            }

            echo "<script>
              document.addEventListener('DOMContentLoaded', function() {
                  Swal.fire({
                      title: 'Connexion réussie !',
                      text: 'Vous êtes connecté avec succès.',
                      icon: 'success',
                      confirmButtonColor: '#3085d6',
                      confirmButtonText: 'OK'
                  }).then(function() {
                      // Actualisation de la page après la connexion réussie
                      window.location.reload();
                  });
              });
          </script>";
          } else {
            $error_msg = "Erreur de connexion : adresse mail ou mot de passe incorrect.";
          }
        }
      }
    ?>
<!-----[CONNEXION]------->
    <div class="row connexion" id="connexion">
        <div class="col-lg-10 offset-lg-1 col-md-10 offset-md-1 panel panel-white">
            <h1>Connexion</h1>
            
            <!-- Message pour les identifiants de test -->
            <div class="alert alert-info mb-4">
                <h6 class="alert-heading">📋 Accès testeur Gestion</h6>
                <p class="mb-2">Pour tester l'interface de gestion en mode démonstration :</p>
                <ul class="mb-1">
                    <li><strong>Email :</strong> jean-dupont@gmail.com</li>
                    <li><strong>Mot de passe :</strong> Jean123</li>
                </ul>
                <small class="text-muted">Ce profil testeur permet d'explorer le menu de gestion sans modifier les données réelles.</small>
            </div>

            <form class="connexion" method="POST" action="">
                <div class="mb-3 row">
                    <label for="email" class="col-sm-2 col-form-label">Adresse mail : </label>
                    <div class="col-sm-10">
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="password" class="col-sm-2 col-form-label">Mot de passe:</label>
                    <div class="col-sm-10">
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>
                <?php
                if (isset($error_msg)) {
                    echo "<div class='alert alert-danger' role='alert'>$error_msg</div>";
                }
                ?>
                <div class="d-flex justify-content-center">
                    <button type="submit" name="envoie-connexion" class="btn-green btn-primary btn-connexion">Se connecter</button>
                </div>
            </form>
            <br>
            <div>Si vous n'avez pas de compte : <u style="cursor: pointer;" onclick="toggleForm('inscription')">Inscrivez-vous</u></div>
        </div>
    </div>
    <?php
    }
    ?>
</div>
</main>

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