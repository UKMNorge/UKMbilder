<?php

use UKMNorge\Database\SQL\Insert;
use UKMNorge\Database\SQL\Update;

#error_reporting(0);
if (!isset($_FILES) || sizeof($_FILES) == 0)
    die(json_encode(array('success' => false, 'error' => 'Missing files')));

$season = get_option('season');
$place  = get_option('pl_id');

require_once('UKM/Autoloader.php');
global $blog_id;

/**
 * Files are sent from DropZone as an array, but since we want to process them sequentually, this array will have a max-length of 1.
 * I am keeping this method as is, so it can support parallell uploads.
 */

$imageArray = [];
foreach ($_FILES as $index => $imageFile) {

    $sql = new Insert('ukm_bilder');
    $sql->add('season', $season);
    $sql->add('pl_id', $place);
    $sql->add('wp_blog', $blog_id);
    $id = $sql->run();
    

    $filename = $imageFile['name'];
    $extension = pathinfo($filename, PATHINFO_EXTENSION);

    $name = $season . '_' . $place . '_' . $id . '.' . $extension;
    $path = UKM_BILDER_SYNC_FOLDER . $name;

    if (move_uploaded_file($imageFile['tmp_name'], $path)) {
        $sql = new Update('ukm_bilder', ['id' => $id]);
        $sql->add('season', $season);
        $sql->add('pl_id', $place);
        $sql->add('wp_blog', $blog_id);
        $sql->add('filename', $name);
        $sql->add('original_filename', $imageFile['name']);
        $update = $sql->run();

        $image = new Imagick($path);
        $imageprops = $image->getImageGeometry();
        
        // RESIZE IMAGE

        // Find proportions
        $width = $height = 3800;
        if ($imageprops['width'] > $imageprops['height']) {
            $height = 0;
            $compare = 'width';
        } else {
            $width = 0;
            $compare = 'height';
        }

        // IF IMAGE IS LARGER THAN TARGET, RESIZE
        if ($imageprops[$compare] > $$compare) {
            $image->scaleImage($width, $height);
            $image->writeImage($path);
        }
        
        $imageArray[] = [
            'id' => $id, 
            'filename' => $name,
            'originalFilename' => $imageFile['name']
        ];

    } else {
        http_response_code(413);
        $imageArray[] = [
            'id' => '1234', 
            'filename' => 'error',
            'originalFilename' => $imageFile['name']
        ];

    } // ENDIF

} // END FOREACH

UKMbilder::addResponseData('imageData', $imageArray);
