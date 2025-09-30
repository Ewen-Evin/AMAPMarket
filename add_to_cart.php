<?php
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

$data = json_decode(file_get_contents('php://input'), true);

// Vérifier que les données nécessaires sont présentes
if (isset($data['product']) && isset($data['price']) && isset($data['variety']) && isset($data['clientId'])) {
    $product = $data['product'];
    $price = $data['price'];
    $variety = $data['variety'];
    $quantity = 1; // Quantité par défaut
    $clientId = $data['clientId']; // ID client

    // Vérifier si le produit est déjà dans le panier de l'utilisateur
    $sql = "SELECT * FROM panier WHERE nom_panier = :product AND variete_panier = :variety AND id_client = :clientId AND statut_panier = 'en_cours'";
    $stmt = $connexion->prepare($sql);
    $stmt->bindParam(':product', $product);
    $stmt->bindParam(':variety', $variety);
    $stmt->bindParam(':clientId', $clientId);
    $stmt->execute();

    $existingProduct = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingProduct) {
        // Si le produit existe déjà, on augmente la quantité
        $newQuantity = $existingProduct['quantite_panier'] + 1;

        $updateSql = "UPDATE panier SET quantite_panier = :quantity WHERE id_panier = :id";
        $updateStmt = $connexion->prepare($updateSql);
        $updateStmt->bindParam(':quantity', $newQuantity);
        $updateStmt->bindParam(':id', $existingProduct['id_panier']);

        if ($updateStmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Quantité mise à jour dans le panier.']);
        } else {
            echo json_encode(['success' => false, 'message' => "Erreur lors de la mise à jour de la quantité."]);
        }
    } else {
        // Si le produit n'existe pas dans le panier, on l'ajoute
        $insertSql = "INSERT INTO panier (nom_panier, prix_panier, variete_panier, quantite_panier, statut_panier, id_client) 
                      VALUES (:product, :price, :variety, :quantity, 'en_cours', :clientId)";
        $insertStmt = $connexion->prepare($insertSql);
        $insertStmt->bindParam(':product', $product);
        $insertStmt->bindParam(':price', $price);
        $insertStmt->bindParam(':variety', $variety);
        $insertStmt->bindParam(':quantity', $quantity);
        $insertStmt->bindParam(':clientId', $clientId);

        if ($insertStmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Produit ajouté au panier.']);
        } else {
            echo json_encode(['success' => false, 'message' => "Erreur lors de l'ajout du produit au panier."]);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => "Données invalides."]);
}
?>
