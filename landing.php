<?php
$error = "" ;
// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $username = $_POST["identifiant"] ?? "" ;
    $password = $_POST["password"] ?? "" ;

    //  Définissons ici les identifiants valides (temporaire)
    $valid_user ="Admin";
    $valid_pass = "12345";

    if ($username === $valid_user && $password === $valid_pass) {
        // Identifiants corrects → redirection vers types_cas.php
        header("Location: types_cas.php");
        exit();
    } else {
        $error = "Identifiant ou mot de passe incorrect , veuillez réessayer.";
    }
}
?>

<!-- Affichage du formulaire de connexion -->
 
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>prooftag CATIS - Connexion</title>
    <link rel="stylesheet" href="types_cas.css">
    <link rel="stylesheet" href="landing.css">
</head>

<body>

    <div class="landing-wrapper">
        <div class="login-card">

            <img src="image/logo-prooftag-securite-tracabilite-bouteilles-vins.jpg" class="login-logo" alt="Logo CATIS">

            <h1 class="login-title">Bienvenue sur Prooftag CATIS</h1>
            <p class="login-subtext">Veuillez vous identifier pour continuer</p>
            

        <?php if ($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">

            <form action="types_cas.php" method="GET">
                <div class="form-group">
                    <label for="identifiant">Identifiant :</label>
                    <input type="text" id="identifiant" name="identifiant" required placeholder="Entrez votre identifiant exple: Admin">
                </div>
                 <div class="form-group">
                    <label for="password">Mot de passe :</label>
                    <input type="password" id="password" name="password" required placeholder="Entrez votre mot de passe exple: 12345">
                </div>
                <button type="submit"   class="login-btn">Se connecter</button>
            </form>
        </div>
    </div>

</body>
</html>