<?php
if(!isset($_FILES) || sizeof($_FILES) == 0)
	die(false);

$SYNC_FOLDER = '/home/ukmno/private_sync/';

$season = get_option('season');
$place	= get_option('pl_id');

require_once('UKM/sql.class.php');

$sql = new SQLins('ukm_bilder');
$sql->add('season', $season);
$sql->add('pl_id', $place);
$res = $sql->run();

$id = $sql->insId();

$filename = $_FILES['image']['name'];
$extension = pathinfo($filename, PATHINFO_EXTENSION);

$name = $season.'_'.$place.'_'.$id.'.'.$extension;
$path = $SYNC_FOLDER.$name;

move_uploaded_file($_FILES['image']['tmp_name'], $path);


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
	$image->resizeImage($width, $height);
	$image->writeImage($path);
}

die(array('id' => $id, 'filename' => $name));