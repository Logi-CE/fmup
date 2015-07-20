<?php
/**
 * Contient différentes fonctions de création et de manipulation d'images
 * @version 1.0
 */
class ImageHelper
{
    /**
     * Crée une image en décalant d'un nombre de dégrés donnés vers la droite
     * @param string $nom_fichier : Le fichier à pivoter
     * @param int $degres : [OPT] Le nombre de degrés, par défaut 90
     * @todo : Voir le fonctionnement
     */
    public static function pivoterImage ($nom_fichier, $degres = 90)
    {
        if (file_exists($nom_fichier)) {
            $source = imagecreatefrompng($nom_fichier);

            //rotation de l'image
            $rotation = imagerotate($source, $degres, 255);

            // sauvegarde de l'image
            imagepng($rotation, $nom_fichier);
        }
    }

	/**
     * Génère une image sous forme de code barre à partir du paramètre
     * Le format utilisé est le "code 39"
     * @param string $chaine : La chaine à utiliser pour le code barre, pouvant contenir chiffres, lettres, et quelques caractères supplémentaires
     * @return string : Une chaine qui correspond à l'image
     */
    public static function genererCodeBarre ($chaine)
    {
        // Vérification de la chaine
        if (!preg_match('/^[a-zA-Z0-9\-\.\ \$\/\+%\*]+$/', $chaine)) {
            new Error('Format incorrect pour la génération de code barre.', E_WARNING);
        } else {
            // Correspondances
            $correspondances = array(
               '0' =>  '101000111011101',
               '1' =>  '111010001010111',
               '2' =>  '101110001010111',
               '3' =>  '111011100010101',
               '4' =>  '101000111010111',
               '5' =>  '111010001110101',
               '6' =>  '101110001110101',
               '7' =>  '101000101110111',
               '8' =>  '111010001011101',
               '9' =>  '101110001011101',
               'A' =>  '111010100010111',
               'B' =>  '101110100010111',
               'C' =>  '111011101000101',
               'D' =>  '101011100010111',
               'E' =>  '111010111000101',
               'F' =>  '101110111000101',
               'G' =>  '101010001110111',
               'H' =>  '111010100011101',
               'I' =>  '101110100011101',
               'J' =>  '101011100011101',
               'K' =>  '111010101000111',
               'L' =>  '101110101000111',
               'M' =>  '111011101010001',
               'N' =>  '101011101000111',
               'O' =>  '111010111010001',
               'P' =>  '101110111010001',
               'Q' =>  '101010111000111',
               'R' =>  '111010101110001',
               'S' =>  '101110101110001',
               'T' =>  '101011101110001',
               'U' =>  '111000101010111',
               'V' =>  '100011101010111',
               'W' =>  '111000111010101',
               'X' =>  '100010111010111',
               'Y' =>  '111000101110101',
               'Z' =>  '100011101110101',
               '-' =>  '100010101110111',
               '.' =>  '111000101011101',
               ' ' =>  '100011101011101',
               '$' =>  '100010001000101',
               '/' =>  '100010001010001',
               '+' =>  '100010100010001',
               '%' =>  '101000100010001',
               '*' =>  '100010111011101'
            );
            
            // hauteur de l'image, fixe
            $hauteur = 25;
            // Largeur de l'image, dépendante des caractères (15 * nb caractère)
            $largeur = 0;
            
            // On met la chaine en majuscule
            $chaine = strtoupper($chaine);
            
            $image_tampon = imagecreatetruecolor(500, $hauteur);
            
            // On charge les couleurs
            $couleur_blanche = imagecolorallocate($image_tampon, 255, 255, 255);
            $couleur_noire = imagecolorallocate($image_tampon, 0, 0, 0);
            
            // On met le fond en place
            imagefill($image_tampon, 0, 0, $couleur_blanche);
            
            $code_barre = '';
            for ($i = 0; $i < strlen($chaine); $i++) {
                $code_barre .= '0'.$correspondances[$chaine[$i]];
            }
            
            // Le code barre est délimité par des *
            $code_barre = $correspondances["*"].$code_barre.'0'.$correspondances["*"];
            
            // On empile les barres noires et blanches
            for ($x = 0; $x < strlen($code_barre); $x++) {
                if ($code_barre[$x] == "1") {
                    imageline($image_tampon, $largeur, 0, $largeur, $hauteur, $couleur_noire);
                } else {
                    imageline($image_tampon, $largeur, 0, $largeur, $hauteur, $couleur_blanche);
                }
                $largeur++;
            }
            
            // Création de l'image finale, avec la vraie taille
            $image = imagecreatetruecolor($largeur, $hauteur);
            imagefill($image, 0, 0, $couleur_blanche);
            imagecopymerge($image, $image_tampon, 0, 0, 0, 0, 500, $hauteur, 100);
            imagedestroy($image_tampon);
            
            return $image;
        }
    }
    
    /**
     * Crée une image plus petite d'une image en PNG, JPG ou GIF
     * @param string $nom_fichier : Le nom du fichier complet avec son chemin
     * @param int $largeur : Largeur voulue de la vignette
     * @param int $hauteur : Hauteur voulue de la vignette
     * @param bool $respecter_echelle : [OPT] Respecter ou non les proportions de l'image parente, par défaut non
     * @return bool|resource : L'image sous forme de ressource
     */
    public static function creerVignette ($nom_fichier, $largeur, $hauteur, $respecter_echelle = false) {
         
        if ($largeur <= 0 || $hauteur <= 0) {
            throw new Error("Les dimensions de l'image doivent être des nombres positifs.");
        }
        
        if (!file_exists($nom_fichier)) {
            throw new Error("Le fichier n'existe pas.");
        }
        
        $extension = explode('.', $nom_fichier);
        $extension = strtolower($extension[count($extension) - 1]);
         
        if ($extension == 'png') {
            $image = imagecreatefrompng($nom_fichier);
        } elseif ($extension == 'jpeg' || $extension == 'jpg') {
            $image = imagecreatefromjpeg($nom_fichier);
        } elseif ($extension == 'gif') {
            $image = imagecreatefromgif($nom_fichier);
        } else {
            //new Error('Erreur extension introuvable', E_WARNING);
            return false;
        }
        
        $taille = getimagesize($nom_fichier);
        $image_copie = imagecreate($largeur, $hauteur);
        
        $depart_largeur = 0;
        $depart_hauteur = 0;
        if ($respecter_echelle) {
            $ratio_largeur  = $taille[0] / $largeur;
            $ratio_hauteur = $taille[1] / $hauteur;
            
            // Calcul des nouvelles dimensions
            if ($ratio_largeur > $ratio_hauteur) {
                $largeur /= $ratio_largeur;
                $hauteur /= $ratio_largeur;
            } else {
                $largeur /= $ratio_hauteur;
                $hauteur /= $ratio_hauteur;
            }
            
            // Centrer l'image
            if ($newwidth < $newheight) {
                $depart_largeur = ($hauteur - $largeur) / 2;
            } else {
                $depart_hauteur = ($largeur - $hauteur) / 2;
            }
        }
        
        imagecopyresized($image_copie, $image, $depart_largeur, $depart_hauteur, 0, 0, $largeur, $hauteur, $taille[0], $taille[1]);
         
        return $image_copie;
    }
}

if (!function_exists("imagerotate")) {
    function imagerotate(&$srcImg, $angle, $transparentColor = null)
    {
        $srcw = imagesx($srcImg);
        $srch = imagesy($srcImg);
        if($angle == 0) return $srcImg;

        // Convert the angle to radians
        $pi = 3.141592654;
        $theta = $angle * $pi / 180;

        // Get the origin (center) of the image
        $originx = $srcw / 2;
        $originy = $srch / 2;

        // The pixels array for the new image
        $pixels = array();
        $minx = 0;
        $maxx = 0;
        $miny = 0;
        $maxy = 0;
        $dstw = 0;
        $dsth = 0;

        // Loop through every pixel and transform it
        for ($x = 0; $x < $srcw; $x++) {
            for ($y = 0; $y < $srch; $y++) {
                list($x1, $y1) = translateCoordinate($originx, $originy, $x, $y, false);

                $x2 = $x * cos($theta) - $y * sin($theta);
                $y2 = $x * sin($theta) + $y * cos($theta);

                // Store the pixel color
                $pixels[] = array($x2, $y2, imagecolorat($srcImg, $x, $y));

                // Check our boundaries
                if($x2 > $maxx) $maxx = $x2;
                if($x2 < $minx) $minx = $x2;
                if($y2 > $maxy) $maxy = $y2;
                if($y2 < $miny) $miny = $y2;
            }
        }

        // Determine the new image size
        $dstw = $maxx - $minx + 1;
        $dsth = $maxy - $miny + 1;

        // Create our new image
        $dstImg = imagecreatetruecolor($dstw, $dsth);

        // Fill the background with our transparent color
        if($transparentColor == null) $transparentColor = imagecolorallocate($dstImg, 1, 2, 3);
        imagecolortransparent($dstImg, $transparentColor);
        imagefilledrectangle($dstImg, 0, 0, $dstw + 1, $dsth + 1, $transparentColor);

        // Get the new origin
        $neworiginx = -$minx;
        $neworiginy = -$miny;

        // Fill in the pixels
        foreach ($pixels as $data) {
            list($x, $y, $color) = $data;
            list($newx, $newy) = translateCoordinate($neworiginx, $neworiginy, $x, $y);
            imagesetpixel($dstImg, $newx, $newy, $color);
        }

        return $dstImg;
    }

    /**
     * Translates from mathematical coordinate system to computer coordinate system using
     * origin coordinates from the computer system or visa versa
     *
     * @param int $originx
     * @param int $originy
     * @param int $x
     * @param int $y
     * @param bool $toComp
     * @return array(int $x, int $y)
     */
    function translateCoordinate($originx, $originy, $x, $y, $toComp = true)
    {
        if ($toComp) {
            $newx = $originx + $x;
            $newy = $originy - $y;
        } else {
            $newx = $x - $originx;
            $newy = $originy - $y;
        }

        return array($newx, $newy);
    }
}
