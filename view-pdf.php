<?php
// Récupérer le nom du fichier
$pdf = $_GET['file'] ?? '';

// Sécurité : nettoyer le nom du fichier
$pdf = basename($pdf);

// Chemin complet du fichier
$filepath = __DIR__ . '/uploads/' . $pdf;

// Vérifier que le fichier existe
if (!file_exists($filepath)) {
    http_response_code(404);
    echo '<!DOCTYPE html>
    <html>
    <head><meta charset="utf-8"><title>Erreur</title></head>
    <body style="font-family:Arial; text-align:center; padding-top:100px;">
        <h1>❌ Fichier PDF introuvable</h1>
        <p><a href="javascript:history.back()">← Retour</a></p>
    </body>
    </html>';
    exit;
}

// Vérifier que c'est bien un PDF
$ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
if ($ext !== 'pdf') {
    http_response_code(403);
    echo '<!DOCTYPE html>
    <html>
    <head><meta charset="utf-8"><title>Erreur</title></head>
    <body style="font-family:Arial; text-align:center; padding-top:100px;">
        <h1>❌ Ce fichier n\'est pas un PDF</h1>
        <p><a href="javascript:history.back()">← Retour</a></p>
    </body>
    </html>';
    exit;
}

// Nettoyer le buffer de sortie
if (ob_get_level()) {
    ob_end_clean();
}

// Headers optimisés pour forcer l'affichage
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($pdf) . '"');
header('Content-Transfer-Encoding: binary');
header('Accept-Ranges: bytes');
header('Content-Length: ' . filesize($filepath));

// Désactiver la mise en cache
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Lire et afficher le fichier
@readfile($filepath);
exit;
?>