<?php

use UKMNorge\Database\SQL\Insert;
use UKMNorge\Database\SQL\Update;
use UKMNorge\Innslag\Innslag;
use UKMNorge\Arrangement\Arrangement;

require_once( UKMbilder::$path_plugin . 'class/ConvertBilde.class.php');
require_once( UKMbilder::$path_plugin . 'class/TaggerBilde.class.php');

/*
    ### Denne filen laster opp kunstverk fra Playback til WP, converterer og tagger bildet ###
*/

$arrangement = new Arrangement( intval(get_option( 'pl_id' ) )); 


// rename('image1.jpg', 'del/image1.jpg');
$innslagId;
$playbackId;

try {
    $innslagId = $_GET['innslag'];
    $playbackId = $_GET['playback'];
} catch( Exception $e ) {
    echo '<h1>Innslag eller playback er ikke inkludert i kallet!</h1>';
}

$innslag = new Innslag($innslagId);
$playback = $innslag->getPlayback()->get($playbackId);
$imgUrl = $playback->base_url . $playback->file_path . $playback->fil;



if(!$arrangement->erKunstgalleri()) {
    throw new Exception("Denne funksjonen er oprettet for å laste opp bilder som kunstverk og arrangement type må være kunstgalleri");
}

$b64image = base64_encode(curl_get_contents($imgUrl));

function curl_get_contents($url)
{
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  $data = curl_exec($ch);
  curl_close($ch);
  return $data;
}

$data = base64_decode($b64image);

$uploadedBildeNavn = $playback->fil;
$whereToSave = '/home/ukmno/private_sync/'. $uploadedBildeNavn .'.jpg';
$result = file_put_contents($whereToSave, $data);

var_dump($result);


$season = get_option('season');
$place  = get_option('pl_id');


require_once('UKM/Autoloader.php');
global $blog_id;

$sql = new Insert('ukm_bilder');
$sql->add('season', $season);
$sql->add('pl_id', $place);
$sql->add('wp_blog', $blog_id);
$sql->add('delta_user_id', $playback->getUserId());
$id = $sql->run();

$filename = $uploadedBildeNavn;
$extension = pathinfo($filename, PATHINFO_EXTENSION);

$name = $season . '_' . $place . '_' . $id . '.' . $extension;
$path = UKM_BILDER_SYNC_FOLDER . $name;


if (rename($whereToSave, $path)) {
    $sql = new Update('ukm_bilder', ['id' => $id]);
    $sql->add('season', $season);
    $sql->add('pl_id', $place);
    $sql->add('wp_blog', $blog_id);
    $sql->add('filename', $name);
    $sql->add('original_filename', $uploadedBildeNavn);
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
        'originalFilename' => $uploadedBildeNavn
    ];

} else {
    http_response_code(413);
    $imageArray[] = [
        'id' => '1234', 
        'filename' => 'error',
        'originalFilename' => $uploadedBildeNavn
    ];

} // ENDIF


if($arrangement->getProgram()->getAntall() > 1) {
    throw new Exception('Det er opprettet mer enn 1 hendelse for type kunstgalleri.');
}
else if($arrangement->getProgram()->getAntall() < 1) {
    throw new Exception('Hendelse finnes ikke. Det må være en hendelse for type kunstgalleri');
}

# Konverter bilde
echo 'converterer bildet ...';
ConvertBilde::converterBilde($id);

# Tagg bilde
$innslagId = $innslagId;
$imageId = $id;
$fotografId = wp_get_current_user()->ID;
$fotografId = 1;
// $delta_user_id = $playback->getUserId();
$hendelseId = $arrangement->getProgram()->forestillinger[0]->id;

echo 'sleeping...';
sleep(3);
echo 'tagger bildet ...';


TaggerBilde::taggBilde($innslagId, $imageId, $fotografId, $hendelseId);


