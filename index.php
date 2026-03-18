<?php
require_once 'config.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $username = $_POST["identifiant"] ?? "";
    $password = $_POST["password"] ?? "";

    $valid_user = "lea";
    $valid_pass = "lea1905";

    if ($username === $valid_user && $password === $valid_pass) {
        $_SESSION['connecte'] = true;
        $_SESSION['username'] = $username;
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prooftag CATIS - Connexion</title>
    <link rel="stylesheet" href="landing.css">
</head>
<body>

<div class="landing-wrapper">
    <div class="login-card">

        <img
            src="image/logo-prooftag-securite-tracabilite-bouteilles-vins.jpg"
            class="login-logo"
            alt="Logo CATIS">

        <h1 class="login-title">Bienvenue sur Prooftag CATIS</h1>
        <p class="login-subtext">Veuillez vous identifier pour continuer</p>

        <?php if ($error): ?>
            <div class="error-msg">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-group">
                <label for="identifiant">Identifiant</label>
                <input
                    type="text"
                    id="identifiant"
                    name="identifiant"
                    required
                    autocomplete="off"
                    placeholder="Ex : Admin">
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <div class="password-wrapper">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        autocomplete="new-password"
                        placeholder="Ex : 12345">
                    <span class="toggle-password" onclick="togglePassword()">
                        <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </span>
                </div>
            </div>

            <button type="submit" class="login-btn">
                Se connecter
            </button>

        </form>

    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('password');
    const icon = document.getElementById('eye-icon');

    if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
        `;
    } else {
        input.type = 'password';
        icon.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        `;
    }
}
</script>

</body>
</html>
