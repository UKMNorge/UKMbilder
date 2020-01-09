<?php

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Database\SQL\Query;
use UKMNorge\Database\SQL\Update;

$imageId = $_POST['imageId'] ? intval($_POST['imageId']) : 0; //TODO: handle non-integer input with UKM-approved method

// die('{"imageId": "' . $imageId . '" }');

$arrangement = new Arrangement( get_option( 'pl_id' ) );


$sql = new Query("SELECT * FROM `ukm_bilder`
WHERE `pl_id` = '#pl_id'
AND `season` = '#season'
AND `status` = 'uploaded'
ORDER BY `id` ASC
LIMIT 1", [
    'pl_id' => $arrangement->getId(),
    'season' => $arrangement->getSesong()
]);

$sqlImageData = $sql->getArray();


// FIND LOCATION OF CURRENT FILE
$path = UKM_BILDER_SYNC_FOLDER . $sqlImageData['filename'];
$wp_upload_dir = wp_upload_dir();
$wp_path = $wp_upload_dir['path'] .'/'. $sqlImageData['filename'];




// COMPRESS AND MOVE TO WORDPRESS UPLOAD DIR (wp_ins_att requirement)
try {
    $image = new Imagick( $path );
    $imageprops = $image->getImageGeometry();
} catch( Exception $e ) {
    $db_update = new SQLins('ukm_bilder', array('id' => $r['id']));
    $db_update->add('status', 'crash');
    $db_update->run();
    die(json_encode(array('success'=>false, 'reload' => true, 'message' => 'Unsupported image format: '. $e->getCode())));
}

// Find proportions
$width = $height = 2048;
if($imageprops['width'] > $imageprops['height']) {
    $height = 0;
    $compare = 'width';
} else {
    $width = 0;
    $compare = 'height';
}

// IF IMAGE IS LARGER THAN TARGET, RESIZE
if($imageprops[$compare] > $$compare) {
    $image->scaleImage($width, $height);
}
$image->writeImage($wp_path);


/// WORDPRESS GENERATE ATTACHMENT AND THUMBS
$wp_filetype = wp_check_filetype(basename($wp_path), null );
$attachment = array(
                'post_mime_type' => $wp_filetype['type'],
              'post_title' => $sqlImageData['filename'],
              'post_content' => '',
              'post_status' => 'inherit'
            );
$attach_id = wp_insert_attachment( $attachment, $wp_path);

// you must first include the image.php file
// for the function wp_generate_attachment_metadata() to work
require_once(ABSPATH . 'wp-admin/includes/image.php');
$attach_data = wp_generate_attachment_metadata( $attach_id, $wp_path );
wp_update_attachment_metadata( $attach_id, $attach_data );
$image_wp_url = wp_get_attachment_thumb_url($attach_id);





// die(json_encode([
//     'pathData' => [$path, $wp_upload_dir, $wp_path],
//     'imageData' =>$sqlImageData,
//     'originalFilename' => $sqlImageData['original_filename'],
//     'wp_path' => $wp_path,
//     'newPath' => $image->getFilename(),
//     'updateData' => [$sqlImageData['id'], $attach_id],
//     'returnData' => [
//         'attach_id' => $attach_id,
//         'imageId' => $sqlImageData['id'],
//         'imageUrl' => $image_wp_url,
//         'originalFilename' => $sqlImageData['original_filename']
//     ]
// ]));

$db_update = new Update('ukm_bilder', array('id' => $sqlImageData['id'] ));
$db_update->add('wp_post', $attach_id);
$db_update->add('status', 'compressed');
$db_update->add('url', $image_wp_url);
$db_update->run();


UKMbilder::addResponseData('imageData', [
    'imageId' => $sqlImageData['id'],
    'imageUrl' => $image_wp_url,
    'originalFilename' => $sqlImageData['original_filename']
]);
