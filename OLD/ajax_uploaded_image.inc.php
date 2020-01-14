<?php
error_reporting(0);
if(!isset($_FILES) || sizeof($_FILES) == 0)
	die(json_encode(array('success'=>false,'error'=>'Missing files')));

$SYNC_FOLDER = UKMbilder_syncfolder();

$season = get_option('season');
$place	= get_option('pl_id');

require_once('UKM/sql.class.php');

global $blog_id;
$sql = new SQLins('ukm_bilder');
$sql->add('season', $season);
$sql->add('pl_id', $place);
$sql->add('wp_blog', $blog_id);
$res = $sql->run();

$id = $sql->insId();

$filename = $_FILES['image']['name'];
$extension = pathinfo($filename, PATHINFO_EXTENSION);

$name = $season.'_'.$place.'_'.$id.'.'.$extension;
$path = $SYNC_FOLDER.$name;

if(move_uploaded_file($_FILES['image']['tmp_name'], $path)) {

    $sql = new SQLins('ukm_bilder', array('id' => $id));
    $sql->add('filename', $name);
    $sql->run();

    // RESIZE IMAGE
    $image = new Imagick( $path );
    $imageprops = $image->getImageGeometry();
    
    	// Find proportions
    	$width = $height = 3800;
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
    	$image->writeImage($path);
    }

    die(json_encode(array('id' => $id, 'filename' => $name)));
}
else {
    die(json_encode(array('id' => '1234', 'filename' => 'error')));
}