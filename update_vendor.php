<?php
// script PHP qui :
//     - Vide automatiquement dossier vendor et son contenu
//     - Décompresse l'archive vendor.zip dans le dossier vendor
//     - Supprime l’archive vendor.zip après extraction pour plus de sécurité.
//
//
// ************** Prérequis:
//
// Ce script doit être placé à la racine
//     put update_vendor.php
//
// zipper le dossier vendor en local (4s, 11Mo)
//     (cd vendor && zip -r ../vendor.zip ./*)
//
// copier le fichier vendor.zip dans le dossier remote (transferred in 4 seconds, 2.56 MiB/s)
//     put vendorbis.zip
//
//
// ************** Déclenchement:
//
// call script (Temps d'exécution moyen : 35 secondes)
//     http://<host cinePS_API>/update_vendor.php
// 

$start = microtime(true);

// Nom de l'archive uploadée
$archive = 'vendor.zip';

// Répertoire de destination
$destination = __DIR__ . '/vendor';


// Fonction pour vider un dossier récursivement
function deleteFolderContents($dir) {
    if (!is_dir($dir)) return;
    // liste les fichier et dossiers
    $files = array_diff(scandir($dir), array('.', '..'));
    // pour chaque fichier ou dossier
    foreach ($files as $file) {
        $path = "$dir/$file";
        // si c'est un dossier
        if (is_dir($path)) {
            // supprime le contenu du dossier
            deleteFolderContents($path);
            // supprime le dossier
            rmdir($path);
        } 
        // si c'est un fichier
        else {
            // supprime le fichier
            unlink($path);
        }
    }
}

// Désactiver le cache pour s'assurer que le script se declenche à chaque fois
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT"); // date passée
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // maintenant
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// start html -----------------

echo "<h1>Update vendor</h1>";

echo "⏳ Mise à jour du dossier vendor en cours...<br>\n";

if (!file_exists($archive)) {
    die("❌ Le fichier " . $archive . " n'existe pas.");
}

// si le dossier vendor est présent
if (is_dir($destination)) {
    // Vider le contenu existant
    deleteFolderContents($destination);
    echo "✅ Dossier " . $destination . " vidé.<br>\n";
} 
// Créer le dossier vendor si nécessaire
else {
    // permission drwxr-xr-x
    mkdir($destination, 0755, true);
    echo "✅ Dossier " . $destination . " créé.<br>\n";
}

// Calcul du temps écoulé en secondes avec millisecondes
$elapsed = microtime(true) - $start;
echo "Temps d'exécution : " . round($elapsed, 3) . " secondes.<br>\n";


// Extraction de l'archive
$zip = new ZipArchive;
if ($zip->open($archive) === TRUE) {
    echo "⏳ Archive " . $archive . " en cours d'extraction...<br>\n";
    $zip->extractTo($destination);
    $zip->close();
    echo "✅ Extraction ZIP terminée.<br/>\n";
} else {
    die("❌ Erreur : impossible d'ouvrir l'archive " . $archive . ".<br>\n");
}


// Supprimer l’archive après extraction
unlink($archive);
echo "✅ Archive " . $archive . " supprimée.<br>\n";

echo "✅ Mise à jour du dossier vendor terminée avec succès.<br>\n";


// Calcul du temps écoulé en secondes avec millisecondes
$elapsed = microtime(true) - $start;
echo "Temps d'exécution : " . round($elapsed, 3) . " secondes.<br>\n";