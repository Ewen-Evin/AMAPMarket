<?php
session_start(); // Démarrer la session

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

if (!isset($_SESSION['id_client'])) {
    header("Location: connexion.php");
    exit;
  }
  
  $profilReq = $connexion->prepare("
    SELECT p.type_profil 
    FROM {$config['db_prefix']}profil p
    INNER JOIN {$config['db_prefix']}client c ON p.login = c.email_client
    WHERE c.id_client = :id
  ");
  $profilReq->execute(['id' => $_SESSION['id_client']]);
  $profil = $profilReq->fetch();

$isGestion = false;
$isGestionTesteur = false;
if ($profil) {
    if ($profil['type_profil'] === 'gestion') {
        $isGestion = true;
    }
    if ($profil['type_profil'] === 'gestion_testeur') {
        $isGestionTesteur = true;
    }
}

if (!$profil || (!$isGestion && !$isGestionTesteur)) {
    $_SESSION['not_allowed'] = true;
    header("Location: connexion.php");
    exit;
}

if (isset($_SESSION['not_allowed'])) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Accès refusé',
                text: 'Vous n\'avez pas les droits pour accéder à cette page.',
                icon: 'error',
                confirmButtonColor: '#d33',
                confirmButtonText: 'OK'
            });
        });
    </script>";
    unset($_SESSION['not_allowed']);
  }  

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_commande'], $_POST['statut'])) {
    if ($isGestionTesteur) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Action non autorisée',
                    text: 'Les modifications ne sont pas autorisées pour le profil gestion_testeur.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
            });
        </script>";
        // Ne pas enregistrer
    } else {
        $updateReq = $connexion->prepare("
            UPDATE {$config['db_prefix']}commande 
            SET statut = :statut 
            WHERE id_commande = :id_commande
        ");
        $updateReq->execute([
            'statut' => $_POST['statut'],
            'id_commande' => $_POST['id_commande']
        ]);
        header("Location: gestion.php");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_commande'])) {
    if ($isGestionTesteur) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Action non autorisée',
                    text: 'La suppression n\'est pas autorisée pour le profil gestion_testeur.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
            });
        </script>";
    } else {
        $deleteReq = $connexion->prepare("
            DELETE FROM {$config['db_prefix']}commande 
            WHERE id_commande = :id_commande
        ");
        $deleteReq->execute(['id_commande' => $_POST['delete_commande']]);
        header("Location: gestion.php");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_produit'])) {
    if ($isGestionTesteur) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Action non autorisée',
                    text: 'La suppression n\'est pas autorisée pour le profil gestion_testeur.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
            });
        </script>";
    } else {
        $deleteProduitReq = $connexion->prepare("
            DELETE FROM {$config['db_prefix']}produit 
            WHERE id_produit = :id_produit
        ");
        $deleteProduitReq->execute(['id_produit' => $_POST['delete_produit']]);
        header("Location: gestion.php");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_produit'])) {
    if ($isGestionTesteur) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Action non autorisée',
                    text: 'L\'ajout n\'est pas autorisé pour le profil gestion_testeur.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
            });
        </script>";
    } else {
        $nomProduit = $_POST['nom_produit'];
        $varieteProduit = $_POST['variete_produit'];
        $stockProduit = $_POST['stock_produit'];
        $prixProduit = $_POST['prix_produit'];

        if (isset($_FILES['image_produit']) && $_FILES['image_produit']['error'] === UPLOAD_ERR_OK) {
            // CORRECTION : Utiliser strtolower pour le nom de l'image
            $imageName = strtolower($nomProduit) . strtolower($varieteProduit) . ".webp";
            $imagePath = "image/" . $imageName;
            move_uploaded_file($_FILES['image_produit']['tmp_name'], $imagePath);

            $addProduitReq = $connexion->prepare("
                INSERT INTO {$config['db_prefix']}produit (nom_produit, variete_produit, stock_produit, prix_produit) 
                VALUES (:nom_produit, :variete_produit, :stock_produit, :prix_produit)
            ");
            $addProduitReq->execute([
                'nom_produit' => $nomProduit,
                'variete_produit' => $varieteProduit,
                'stock_produit' => $stockProduit,
                'prix_produit' => $prixProduit
            ]);
            header("Location: gestion.php");
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_produit'])) {
    if ($isGestionTesteur) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Action non autorisée',
                    text: 'La modification n\'est pas autorisée pour le profil gestion_testeur.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
            });
        </script>";
    } else {
        $idProduit = $_POST['id_produit'];
        $nomProduit = $_POST['nom_produit'];
        $varieteProduit = $_POST['variete_produit'];
        $stockProduit = $_POST['stock_produit'];
        $prixProduit = $_POST['prix_produit'];

        $currentProduitReq = $connexion->prepare("
            SELECT nom_produit, variete_produit 
            FROM {$config['db_prefix']}produit 
            WHERE id_produit = :id_produit
        ");
        $currentProduitReq->execute(['id_produit' => $idProduit]);
        $currentProduit = $currentProduitReq->fetch();

        if ($currentProduit) {
            // CORRECTION : Utiliser strtolower pour les noms d'images
            $oldImageName = strtolower($currentProduit['nom_produit']) . strtolower($currentProduit['variete_produit']) . ".webp";
            $newImageName = strtolower($nomProduit) . strtolower($varieteProduit) . ".webp";

            if ($oldImageName !== $newImageName) {
                $oldImagePath = "image/" . $oldImageName;
                $newImagePath = "image/" . $newImageName;

                if (file_exists($oldImagePath)) {
                    rename($oldImagePath, $newImagePath);
                }
            }

            $editProduitReq = $connexion->prepare("
                UPDATE {$config['db_prefix']}produit 
                SET nom_produit = :nom_produit, variete_produit = :variete_produit, stock_produit = :stock_produit, prix_produit = :prix_produit
                WHERE id_produit = :id_produit
            ");
            $editProduitReq->execute([
                'nom_produit' => $nomProduit,
                'variete_produit' => $varieteProduit,
                'stock_produit' => $stockProduit,
                'prix_produit' => $prixProduit,
                'id_produit' => $idProduit
            ]);
        }

        header("Location: gestion.php");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet"> 
    <link href="style.css"  rel="stylesheet">
    <link rel="website icon" href="image/logo.png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Gestion - Amap'Market</title>
    <style>
        .table.commandes th, .table.commandes td {
            width: 33%; /* Chaque colonne occupe un tiers de la largeur totale */
            text-align: center; /* Centrer le contenu */
        }
        .form-select {
            width: auto; /* Réduit la largeur de la case de sélection */
            display: inline-block;
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
                            <a class="nav-link actif" style="color: red;" href="connexion.php">Connecté : ' . htmlspecialchars($client['prenom_client']) . ' ' . htmlspecialchars($client['nom_client']) . '</a>
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
            <h1>Paniers AMAP</h1>
          </div>
        </div>
        
            <div class="row">
                <div class="col panel panel-white">
                    <h2>Liste des produits</h2>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>ID Produit</th>
                                <th>Nom</th>
                                <th>Stock (KG)</th>
                                <th>Prix (€)</th>
                                <th>Variété</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $produitReq = $connexion->query("
                                SELECT id_produit, nom_produit, stock_produit, prix_produit, variete_produit
                                FROM {$config['db_prefix']}produit
                            ");
                            $hasProduit = false;
                            while ($produit = $produitReq->fetch()) {
                                $hasProduit = true;
                                // CORRECTION : Utiliser strtolower pour le chemin de l'image
                                $imagePath = "image/" . strtolower(htmlspecialchars($produit['nom_produit'])) . 
                                             strtolower(htmlspecialchars($produit['variete_produit'])) . ".webp";
                                echo '<tr>
                                        <td><img src="' . $imagePath . '" alt="Produit" style="width: 40px; height: 40px;"></td>
                                        <td>' . htmlspecialchars($produit['id_produit']) . '</td>
                                        <td>' . htmlspecialchars($produit['nom_produit']) . '</td>
                                        <td>' . htmlspecialchars($produit['stock_produit']) . '</td>
                                        <td>' . htmlspecialchars($produit['prix_produit']) . ' €</td>
                                        <td>' . htmlspecialchars($produit['variete_produit']) . '</td>
                                        <td>
                                            <form method="POST" action="" style="display: inline;">
                                                <input type="hidden" name="delete_produit" value="' . htmlspecialchars($produit['id_produit']) . '">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                            <button class="btn btn-warning btn-sm" onclick="editProduct(' . htmlspecialchars($produit['id_produit']) . ', \'' . htmlspecialchars($produit['nom_produit']) . '\', \'' . htmlspecialchars($produit['variete_produit']) . '\', ' . htmlspecialchars($produit['stock_produit']) . ', ' . htmlspecialchars($produit['prix_produit']) . ')">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        </td>
                                      </tr>';
                            }
                            if (!$hasProduit) {
                                echo '<tr><td colspan="7" class="text-center">Aucun produit trouvé.</td></tr>';
                            }
                            ?>
                            <tr>
                                <form method="POST" action="" enctype="multipart/form-data">
                                    <td>
                                        <input type="file" name="image_produit" accept="image/*" required>
                                    </td>
                                    <td>--</td>
                                    <td>
                                        <input type="text" name="nom_produit" class="form-control" placeholder="Nom" required>
                                    </td>
                                    <td>
                                        <input type="number" name="stock_produit" class="form-control" placeholder="Stock (KG)" min="0" required>
                                    </td>
                                    <td>
                                        <input type="number" name="prix_produit" class="form-control" placeholder="Prix (€)" step="0.01" min="0" required>
                                    </td>
                                    <td>
                                        <input type="text" name="variete_produit" class="form-control" placeholder="Variété">
                                    </td>
                                    <td>
                                        <button type="submit" name="add_produit" class="btn btn-success btn-sm">
                                            <i class="bi bi-plus-circle"></i> Ajouter
                                        </button>
                                    </td>
                                </form>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row">
                <div class="col panel panel-white">
                    <h2>Liste des commandes</h2>
                    <table class="table table-striped commandes">
                        <thead>
                            <tr>
                                <th>ID Commande</th>
                                <th>Date</th>
                                <th>Statut</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $commandeReq = $connexion->query("
                                SELECT id_commande, date_commande, statut
                                FROM {$config['db_prefix']}commande
                            ");
                            $hasCommande = false;
                            while ($commande = $commandeReq->fetch()) {
                                $hasCommande = true;
                                echo '<tr>
                                        <td>' . htmlspecialchars($commande['id_commande']) . '</td>
                                        <td>' . htmlspecialchars($commande['date_commande']) . '</td>
                                        <td>
                                            <form method="POST" action="">
                                                <input type="hidden" name="id_commande" value="' . htmlspecialchars($commande['id_commande']) . '">
                                                <select name="statut" class="form-select" onchange="this.form.submit()">
                                                    <option value="en cours de traitement" ' . ($commande['statut'] === 'en cours de traitement' ? 'selected' : '') . '>En cours de traitement</option>
                                                    <option value="préparation de la commande" ' . ($commande['statut'] === 'préparation de la commande' ? 'selected' : '') . '>Préparation de la commande</option>
                                                    <option value="en cours de livraison" ' . ($commande['statut'] === 'en cours de livraison' ? 'selected' : '') . '>En cours de livraison</option>
                                                    <option value="livré" ' . ($commande['statut'] === 'livré' ? 'selected' : '') . '>Livré</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td>
                                            <form method="POST" action="">
                                                <input type="hidden" name="delete_commande" value="' . htmlspecialchars($commande['id_commande']) . '">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                      </tr>';
                            }
                            if (!$hasCommande) {
                                echo '<tr><td colspan="4" class="text-center">Aucune commande trouvée.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

  </div>

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


      <!-- JavaScript de Bootstrap -->
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
      <script>
      function editProduct(id, name, variety, stock, price) {
          const formHtml = `
              <div id="editProductHeader" style="display: flex; justify-content: flex-end; position: absolute; top: 10px; right: 10px; z-index: 10;">
                  <button type="button" id="closeEditProduct" style="background: none; border: none; font-size: 2rem; cursor: pointer; line-height: 1;" aria-label="Fermer">&times;</button>
              </div>
              <form method="POST" action="" style="margin-top: 30px;">
                  <input type="hidden" name="id_produit" value="${id}">
                  <div class="mb-2">
                      <label>Nom</label>
                      <input type="text" name="nom_produit" class="form-control" value="${name}" required>
                  </div>
                  <div class="mb-2">
                      <label>Variété</label>
                      <input type="text" name="variete_produit" class="form-control" value="${variety}">
                  </div>
                  <div class="mb-2">
                      <label>Stock (KG)</label>
                      <input type="number" name="stock_produit" class="form-control" value="${stock}" min="0" required>
                  </div>
                  <div class="mb-2">
                      <label>Prix (€)</label>
                      <input type="number" name="prix_produit" class="form-control" value="${price}" step="0.01" min="0" required>
                  </div>
                  <button type="submit" name="edit_produit" class="btn btn-success">Enregistrer</button>
              </form>
          `;
          Swal.fire({
              title: '<div style="position:relative;">Modifier le produit</div>',
              html: formHtml,
              showConfirmButton: false,
              didOpen: () => {
                  document.getElementById('closeEditProduct').onclick = () => Swal.close();
              }
          });
      }
      </script>
</body>
</html>