<?php

use UKMNorge\Database\SQL\Insert;
use UKMNorge\Database\SQL\Update;

error_reporting(0);
if (!isset($_FILES) || sizeof($_FILES) == 0)
    die(json_encode(array('success' => false, 'error' => 'Missing files')));

$season = get_option('season');
$place  = get_option('pl_id');

require_once('UKM/Autoloader.php');
global $blog_id;


sleep(2); //TODO: remove this debug statement

/**
 * Files are sent from DropZone as an array, but since we want to process them sequentually, this array will have a max-length of 1.
 * I am keeping this method as is, so it can support parallell uploads.
 */

$imageJson = [];
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

    // var_dump( [$_FILES, $imageFile, $name, $path] );

    if (move_uploaded_file($imageFile['tmp_name'], $path)) {

        
        $sql = new Update('ukm_bilder', ['id'=>$id] );
        $sql->add('filename', $name);
        $sql->add('original_filename', $imageFile['name']);
        $res = $sql->run();
        $image = new Imagick($path); // TODO: find Imagic object from somewhere
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
        
        $imageJson[] = [
            'id' => $id, 
            'filename' => $name,
            'request_filename' => $imageFile['name']
        ];

    } else {
        $imageJson[] = [
            'id' => '1234', 
            'filename' => 'error',
            'request_filename' => $imageFile['name']
        ];

    } // ENDIF

} // END FOREACH

die (json_encode($imageJson));
