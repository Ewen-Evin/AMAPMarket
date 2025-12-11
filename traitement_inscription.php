<?php
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

if(isset($_POST['envoie-inscription'])) {
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $email = htmlspecialchars($_POST['email']);
    $adresse = htmlspecialchars($_POST['adresse']);
    $password = htmlspecialchars($_POST['password']);

    // Verifier si le mot de passe est valide
    $checkEmail = $connexion->prepare("SELECT * FROM {$config['db_prefix']}client WHERE email_client = :email");
    $checkEmail->execute(['email' => $email]);
    $existingUser = $checkEmail->fetch();

    if ($existingUser) {
        session_start();
        $_SESSION['inscription_error'] = "Cette adresse mail est déjà utilisée.";
        header('Location: connexion.php');
        exit;
    } else {
        $requete = $connexion->prepare("INSERT INTO {$config['db_prefix']}client VALUES (0, :prenom_client, :nom_client, :email_client, :adresse_client)");
        $requete->execute(
            array(
                'prenom_client' => $prenom,
                'nom_client' => $nom,
                'email_client' => $email,
                'adresse_client' => $adresse,
            )
        );
        $requete = $connexion->prepare("INSERT INTO {$config['db_prefix']}profil VALUES (:login, :mot_de_passe, 'client')");
        $requete->execute(
            array(
                'login' => $email,
                'mot_de_passe' => password_hash($password, PASSWORD_DEFAULT),
            )
        );
        session_start();
        $_SESSION['inscription_success'] = true;
        header('Location: connexion.php');
        exit;
    }
}
?>