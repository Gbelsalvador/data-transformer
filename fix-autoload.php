<?php
// fix-autoload.php
$autoloadFile = __DIR__ . '/vendor/composer/autoload_static.php';

if (file_exists($autoloadFile)) {
    $content = file_get_contents($autoloadFile);
    
    // Vérifier si l'import manque
    if (strpos($content, 'use Composer\\Autoload\\ClassLoader;') === false) {
        // Ajouter l'import après le namespace
            $content = preg_replace(
                '/namespace Composer\\\\Autoload;\\R+/',
                "namespace Composer\\Autoload;\n\nuse Composer\\Autoload\\ClassLoader;\n\n",
                $content,
                1
            );
        
        file_put_contents($autoloadFile, $content);
        echo "Fichier autoload_static.php corrigé.\n";
    } else {
        echo "L'import est déjà présent.\n";
    }
} else {
    echo "Fichier autoload_static.php non trouvé.\n";
}