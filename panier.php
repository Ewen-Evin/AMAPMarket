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
    $_SESSION['alerte_connexion'] = true;
    header('Location: connexion.php');
    exit;
}

// Récupérer tous les produits du panier en base de données avec le statut "en_cours"
$sql = "SELECT id_panier, nom_panier, prix_panier, variete_panier, quantite_panier 
        FROM {$config['db_prefix']}panier 
        WHERE id_client = :id_client AND statut_panier = 'en_cours'";
$stmt = $connexion->prepare($sql);
$stmt->execute(['id_client' => $_SESSION['id_client']]);
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mettre à jour les quantités dans la base de données si une requête POST est reçue
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantities'])) {
    foreach ($_POST['quantities'] as $id_panier => $quantity) {
        $quantity = max(0, floatval($quantity)); // Empêcher les valeurs négatives
        $updateStmt = $connexion->prepare("UPDATE {$config['db_prefix']}panier SET quantite_panier = :quantity WHERE id_panier = :id");
        $updateStmt->bindParam(':quantity', $quantity);
        $updateStmt->bindParam(':id', $id_panier);
        $updateStmt->execute();
    }
    // Rafraîchir les produits après mise à jour des quantités
    $stmt->execute(['id_client' => $_SESSION['id_client']]);
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Supprimer un produit du panier si une requête POST est reçue
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = intval($_POST['delete_id']);
    $deleteStmt = $connexion->prepare("DELETE FROM {$config['db_prefix']}panier WHERE id_panier = ?");
    $deleteStmt->execute([$deleteId]);
}

// Valider le panier et mettre à jour les statuts
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['validate_cart'])) {
    if (empty($produits)) {
        $_SESSION['panier_vide_error'] = "Votre panier est vide.<br>Veuillez ajouter des produits avant de valider.";
        header('Location: panier.php');
        exit;
    } else {
        try {
            $connexion->beginTransaction();

            $insertCommande = $connexion->prepare("INSERT INTO {$config['db_prefix']}commande (date_commande, statut) VALUES (NOW(), 'en cours de traitement')");
            $insertCommande->execute();

            foreach ($produits as $produit) {
                $updateStock = $connexion->prepare("UPDATE {$config['db_prefix']}produit SET stock_produit = stock_produit - :quantite WHERE nom_produit = :nom AND variete_produit = :variete");
                $updateStock->execute([
                    'quantite' => floatval($produit['quantite_panier']), // Ensure the quantity is treated as a numeric value
                    'nom' => $produit['nom_panier'],
                    'variete' => $produit['variete_panier']
                ]);
            }

            $updatePanier = $connexion->prepare("UPDATE {$config['db_prefix']}panier SET statut_panier = 'commander' WHERE id_client = :id_client AND statut_panier = 'en_cours'");
            $updatePanier->execute(['id_client' => $_SESSION['id_client']]);

            $connexion->commit();
            header('Location: confirmation.php');
            exit;
        } catch (Exception $e) {
            $connexion->rollBack();
            die('Erreur : ' . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panier - Amap'Market</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet"> 
    <link href="style.css"  rel="stylesheet">
    <link rel="website icon" href="image/logo.png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg bar">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">
      <img src="image/logo.png" alt="Logo" height="80px">
      Amap'Market
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Accueil</a></li>
        <li class="nav-item"><a class="nav-link" href="produits.php">Produits</a></li>
        <li class="nav-item"><a class="nav-link actif" href="panier.php">Panier</a></li>
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
            echo '<li class="nav-item"><a class="nav-link" href="connexion.php">Connexion</a></li>';
        }
        ?>
      </ul>
    </div>
  </div>
</nav>

<br><br><br><br><br>

<div class="container my-4">
  <div class="row"><div class="col panel panel-white"><h1>Paniers AMAP</h1></div></div>
  <div class="row">
    <div class="col panel panel-white">
      <form method="post">
      <table class="table table-bordered table-striped text-center mt-4">
        <thead class="table-success">
          <tr>
            <th>IMAGE</th>
            <th>NOM DU PRODUIT</th>
            <th>PRIX UNITAIRE (€)</th>
            <th>QUANTITÉ (KG)</th>
            <th>TOTAL (€)</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($produits):
              foreach ($produits as $produit): ?>
              <tr>
                <td><img src="image/<?php echo strtolower(htmlspecialchars($produit['nom_panier'].$produit['variete_panier'])); ?>.webp" alt="Produit" class="img-fluid" style="max-width: 60px;"></td>
                <td><?php echo htmlspecialchars($produit['nom_panier']); ?></td>
                <td><?php echo number_format($produit['prix_panier'], 2); ?>€</td>
                <td>
                  <div class="d-flex justify-content-center align-items-center">
                    <button type="button" class="btn btn-danger btn-delete me-3" data-id="<?php echo $produit['id_panier']; ?>"><i class="bi bi-trash"></i></button>
                    <button class="btn btn-outline-secondary btn-decrease">-</button>
                    <input type="number" step="0.1" class="form-control text-center mx-2 quantity-input" name="quantities[<?php echo $produit['id_panier']; ?>]" value="<?php echo number_format($produit['quantite_panier'], 1); ?>" style="width: 80px;">
                    <button class="btn btn-outline-secondary btn-increase">+</button>
                  </div>
                </td>
                <td class="product-total"><?php echo number_format($produit['prix_panier'] * $produit['quantite_panier'], 2); ?>€</td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="5">Votre panier est vide</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
      <button type="submit" name="validate_cart" class="btn btn-success mt-3">Valider le panier</button>
      </form>
    </div>
  </div>
  <div class="row">
    <div class="col panel panel-white text-center">
      <?php 
        $totalPanier = 0;
        foreach ($produits as $produit) {
            $totalPanier += $produit['prix_panier'] * $produit['quantite_panier'];
        }
      ?>
      <h3>Total du panier : <span id="total-panier"><?php echo number_format($totalPanier, 2); ?>€</span></h3>
    </div>
  </div>
</div>

<script>
function updateTotalPanier() {
  let totalPanier = 0;
  document.querySelectorAll('tr').forEach(row => {
    const totalCell = row.querySelector('.product-total');
    if (totalCell) {
      const total = parseFloat(totalCell.textContent.replace('€', '').trim()) || 0;
      totalPanier += total;
    }
  });
  document.getElementById('total-panier').textContent = totalPanier.toFixed(2) + '€';
}

document.addEventListener('click', function (event) {
  if (event.target.classList.contains('btn-decrease') || event.target.classList.contains('btn-increase')) {
    event.preventDefault();
    const row = event.target.closest('tr');
    const input = row.querySelector('.quantity-input');
    let value = parseFloat(input.value) || 0;

    if (event.target.classList.contains('btn-decrease')) value = Math.max(0, value - 1);
    if (event.target.classList.contains('btn-increase')) value += 1;

    input.value = value.toFixed(1);
    const unitPrice = parseFloat(row.querySelector('td:nth-child(3)').innerText.replace('€', '').replace(',', '.'));
    row.querySelector('.product-total').innerText = (unitPrice * value).toFixed(2) + '€';
    updateTotalPanier();
  }

  if (event.target.classList.contains('btn-delete') || event.target.closest('.btn-delete')) {
    event.preventDefault();
    const button = event.target.closest('.btn-delete');
    const productId = button.getAttribute('data-id');

    fetch('panier.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `delete_id=${productId}`
    }).then(() => window.location.reload());
  }
});

document.addEventListener('input', function (event) {
  if (event.target.classList.contains('quantity-input')) {
    const row = event.target.closest('tr');
    let value = parseFloat(event.target.value) || 0;
    value = Math.max(0, value);
    event.target.value = value.toFixed(1);

    const unitPrice = parseFloat(row.querySelector('td:nth-child(3)').innerText.replace('€', '').replace(',', '.'));
    row.querySelector('.product-total').innerText = (unitPrice * value).toFixed(2) + '€';
    updateTotalPanier();
  }
});
</script>

<?php
if (isset($_SESSION['panier_vide_error'])) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Erreur',
                html: '" . addslashes($_SESSION['panier_vide_error']) . "',
                icon: 'error',
                confirmButtonColor: '#d33',
                confirmButtonText: 'OK'
            });
        });
    </script>";
    unset($_SESSION['panier_vide_error']);
}
?>


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