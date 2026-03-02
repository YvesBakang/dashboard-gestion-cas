<?php
$error = "";

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $username = $_POST["identifiant"] ?? "";
    $password = $_POST["password"] ?? "";

    $valid_user = "lea";
    $valid_pass = "lea1905";

    if ($username === $valid_user && $password === $valid_pass) {
        header("Location: types_cas.php");
        exit();
    } else {
        $error = "Identifiant ou mot de passe incorrect, veuillez réessayer.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    <!-- ✅ INDISPENSABLE POUR LE RESPONSIVE -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Prooftag CATIS - Connexion</title>

    <!-- ✅ UN SEUL CSS -->
    <link rel="stylesheet" href="landing.css">
</head>
<body>

<div class="landing-wrapper">
    <div class="login-card">

        <img
            src="image/logo-prooftag-securite-tracabilite-bouteilles-vins.jpg"
            class="login-logo"
            alt="Logo CATIS"
        >

        <h1 class="login-title">Bienvenue sur Prooftag CATIS</h1>
        <p class="login-subtext">Veuillez vous identifier pour continuer</p>

        <?php if ($error): ?>
            <div class="error-msg">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- ✅ UN SEUL FORMULAIRE -->
        <form method="POST">

            <div class="form-group">
                <label for="identifiant">Identifiant</label>
                <input
                    type="text"
                    id="identifiant"
                    name="identifiant"
                    required
                    placeholder="Ex : Admin">
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    placeholder="Ex : 12345">
            </div>

            <button type="submit" class="login-btn">
                Se connecter
            </button>

        </form>

    </div>
</div>

</body>
</html>
