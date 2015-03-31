<?php
/**
 * Cette classe est à revoir, les noms de fonctions sont pourries !!!
 */

class ImageHelper
{
    /**
     * Retourne l'adresse d'une miniature correspondant à une image donnée
     * Si jamais l'image cherchée n'existe pas on la crée.
     *
     * @param {String} $image L'image
     * @param {Integer} $type Le type d'image (thumbnail, mini, moyen, gros, ...)
     * @return unknown
     */
    public static function getImage($image, $type, $params)
    {
        if (isset ($params["campagne"])) {
            $campagne = $params["campagne"];
            $id = $campagne -> getId();
        }
        if (isset ($params["id_campagne"])) {
            $id = $params["id_campagne"];
        }

        $stock = true;
        if (isset($params["stock"])) {
            if ($params["stock"] <= 0) {
                $stock = false;
            }
        }

        if ('' == $image) {
            $image_reduite = Constantes::getImagePasDImage()."_$type.png";
            $image =  Constantes::getImagePasDImage().".png";
        } else {
            //$image_temp = explode('.', $image);
            //$image_temp = $image_temp[0];
            if (!(is_dir(Constantes::getDossierPhotosCache().$id."/produits/tmp/"))) {
                mkdir(Constantes::getDossierPhotosCache().$id."/produits/tmp/", 0777);
            }
            if ($stock) {
                $image_reduite = Constantes::getDossierPhotosCache().$id."/produits/tmp/".str_replace('/', '_', $image)."_$type.png";
            } else {
                $image_reduite = Constantes::getDossierPhotosCache().$id."/produits/tmp/".str_replace('/', '_', $image)."_epuise_$type.png";
            }
            $image = Constantes::getDossierPhotos().$id."/produits/".$image;

        }

        if (!file_exists($image_reduite)) {

            $im = ImageHelper::resizeImage($image, Constantes::getResizeL($type), Constantes::getResizeH($type), $stock);

            // on enregistre l'image créée
            imagepng($im, $image_reduite);
            imagedestroy($im);
        }
        $image_reduite = str_replace('../back', AppFront::getImagePath(), $image_reduite);
        return $image_reduite;
    }

    public static function getImageNavigation($image, $type, $params = array())
    {
        $image = explode('/', $image);
        $image = $image[count($image) - 1];
        if (isset ($_GET['sous_dossier'])) {
            $sous_dossier = $_GET['sous_dossier'];
        } else {
            $sous_dossier = "";
        }
        if ('' == $image) {
            $image_reduite = Constantes::getImagePasDImage()."_$type.png";
            $image =  Constantes::getImagePasDImage().".png";
        } else {
            if ($sous_dossier != "") {
                if (!(is_dir(Constantes::getDossierPhotosCache().$sous_dossier."/tmp"))) {
                    mkdir(Constantes::getDossierPhotosCache().$sous_dossier."/tmp", 0777);
                }
            $image_reduite = Constantes::getDossierPhotosCache().$sous_dossier."/tmp/".str_replace('/', '_', $image)."_$type.png";
            $image = Constantes::getDossierPhotos().$sous_dossier."/".$image;
            } else {
                if (!(is_dir(Constantes::getDossierPhotosCache()."/tmp"))) {
                    mkdir(Constantes::getDossierPhotosCache()."/tmp", 0777);
                }
            $image_reduite = Constantes::getDossierPhotosCache()."/tmp/".str_replace('/', '_', $image)."_$type.png";
            $image = Constantes::getDossierPhotos()."/".$image;
            }

        }

        if (!file_exists($image_reduite)) {

            $im = ImageHelper::resizeImage($image, Constantes::getResizeL($type), Constantes::getResizeH($type));

            // on enregistre l'image créée
            imagepng($im, $image_reduite);
            imagedestroy($im);
        }
        $image_reduite = str_replace('../back', AppFront::getImagePath(), $image_reduite);
        return $image_reduite;
    }
    /**
     * Redimentionne une image
     *
     * @param {String} $filename L'url de l'image
     * @param {Integer} $width La largeur cible
     * @param {Integer} $height La hauteur cible
     * @param {Boolean} $stock Si il y a encore un produit en stock (car sinon on affiche une image épuisée)
      * @return unknown
     */
    public static function resizeImage($filename, $width, $height, $stock = 1)
    {
        if ($width <= 0 && $height <= 0) {
            throw new Error("Les dimensions de l'image doivent être des nombres positifs.");
        }

        if (! file_exists($filename)) {
            $filename = Constantes::getImagePasDImage().".png";
        }

        // dimentions réelles de l'image
        list($image_width, $image_height) = getimagesize($filename);

        // est-ce que l'image à une taille correcte
        if (! $image_height * $image_width) {
            throw new Error("Mauvais format d'image.");
        }

        // on récupère l'extension
        $ext = substr($filename, -4);
        $ext = strtolower($ext);

        // création d'une image
        if ($ext == ".gif") {
            $source = imagecreatefromgif($filename);
        } elseif ($ext == ".jpg") {
            $source = imagecreatefromjpeg($filename);
        } elseif ($ext == ".jpeg") {
            $source = imagecreatefromjpeg($filename);
        } elseif ($ext == ".png") {
            $source = imagecreatefrompng($filename);
        } else {
            $filename = Constantes::getImagePasDImage().".png";
        }

        $ratio_width  = $image_width / $width;
        $ratio_height = $image_height / $height;

        // Calcul des nouvelles dimentions
        if ($ratio_width > $ratio_height) {
            $newwidth = $image_width / $ratio_width;
            $newheight = $image_height / $ratio_width;
        } else {
            $newwidth = $image_width / $ratio_height;
            $newheight = $image_height / $ratio_height;
        }

        // Centre l'image et lui donne une taille fixe (hauteur et largeur donnés en paramètres)
        $a = 0;
        $b = 0;
        if ($newwidth < $newheight) {
            $a = $newheight - $newwidth;
            $a = $a/2;
            $t = $newheight;

        } else {
            $b = $newwidth - $newheight;
            $b = $b/2;
            $t = $newwidth;
        }

        $im = imagecreatetruecolor($t, $t);

        imagesavealpha($im, true);
        //$trans_colour = imagecolorallocatealpha($im, 0, 0, 0, 127);
        $colour = imagecolorallocate($im, 255, 255, 255);
        imagefill($im, 0, 0, $colour);

        imageCopyResampled($im, $source, $a, $b, 0, 0, $newwidth, $newheight, $image_width, $image_height);
        if (!$stock) {
            $im = ImageHelper::ajouterFinigraneNoStock($im, $width, $newheight);
        }

        return $im;
    }

    /**
     * Ajoute le filigrane : épuisé sur une image
     * @param {Image} $image L'image sur laquelle ajouter le filigrane
     * @param {Integer} $width La largeur de l'image
     * @param {Integer} $height La hauteur de l'image
     **/
    public static function ajouterFinigraneNoStock($image, $width, $height)
    {
        $filigrane = imagecreatefrompng(Constantes::getFiligraneEpuise());
        // 500x500 est la taille du filigrane
        imageCopyResampled($image, $filigrane, 0, 0, 0, 0, $width, $height, 500, 500);
        return $image;
    }

    /**
     * Génère les images d'en-tête pour les espaces
     **/
    public static function getImageEspaceDroitsTournee($alt)
    {
        $rep = "../../public/back/images/droits/"; //repertoire ou se trouve les boutons
        $alt = FileHelper::sanitize($alt);

        $nom = $rep."espace_$alt.png";
        ImageHelper::genererImageEspaceDroitsTournee($alt, $nom, $rep);

        return "<img class=\"image_rotate\" src='./images/droits/espace_$alt.png' alt='$alt' /><canvas class=\"canvas_rortate\" /></canvas>";
    }
    public static function genererImageEspaceDroitsTournee($alt, $nom, $rep)
    {
        if (is_file($nom)) { // Si le bouton existe déja, on renvoie les dimensions
            $taille = getimagesize($nom);
            $largeur = $taille[0];
            $hauteur = $taille[1];
        } else { // Sinon on va le créer
            // Utilisation des ressources graphiques
            $fond = $rep.'fond.png';
            $taille_fond = getimagesize($fond);
            $img_fond = ImageCreateFromPng($fond);

            // Paramêtres du bouton
            $hauteur = $taille_fond[1];
            $largeur = (strlen($alt)+1)*imagefontwidth(3);

            // Création de l'image vierge + choix de la couleur
            $img = imageCreate($largeur, $hauteur);
            $couleur = ImageColorAllocate($img, 0, 0, 0);

            // Elémente graphiques du bouton
            @imageCopyMerge($img, $img_fond, 0, 0, 0, 0, $largeur, $hauteur, 100);

            // Texte
            imageString($img, 3, 0, ($hauteur-imagefontheight(3))/2, ' '.stripslashes(trim($alt)), $couleur);

            Imagepng($img, $nom);
            //ImageHelper::rotation($nom);
        }
    }

    // rotation de l'image a 90 degré vers la droite
    public static function rotation($img)
    {
        $degres	= "90";
        if (file_exists($img)) {
            $image = getimagesize($img);
            $image_type = $image['2'];

            $source = imagecreatefrompng($img);

            //rotation de l'image
            $rotation = imagerotate($source, $degres, 255) or die("Erreur lors de la rotation de ".$file);

            // sauvegarde de l'image
            imagepng($rotation, $img);
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
