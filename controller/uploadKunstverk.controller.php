<?php

use UKMNorge\Database\SQL\Insert;
use UKMNorge\Database\SQL\Update;
use UKMNorge\Innslag\Innslag;
use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Innslag\Playback\Write as WritePlayback;
use UKMNorge\Innslag\Titler\Write as WriteTitler;
use UKMNorge\Innslag\Context\Context;
use UKMNorge\Log\Logger;

global $current_user;

require_once( UKMbilder::$path_plugin . 'class/ConvertBilde.class.php');
require_once( UKMbilder::$path_plugin . 'class/TaggerBilde.class.php');

/*
### Denne filen laster opp kunstverk fra Playback til WP, converterer og tagger bildet ###
*/

$arrangement = new Arrangement( intval(get_option( 'pl_id' ) ));

Logger::setID( 'wordpress', $current_user->ID, get_option('pl_id') );

if(!$arrangement->erKunstgalleri()) {
    throw new Exception("Denne funksjonen er oprettet for å laste opp bilder som kunstverk og arrangement type må være kunstgalleri");
}

try {
    $innslagId = $_GET['innslag'];
    $playbackId = $_GET['playback'];
    if(isset($_GET['tittel_id'])) {
        $tittelId = $_GET['tittel_id'];
    }
} catch( Exception $e ) {
    echo '<h1>Innslag eller playback er ikke inkludert i kallet!</h1>';
}

$innslag = new Innslag($innslagId);

$context = Context::createMonstring(
    $arrangement->getId(),
    $arrangement->getType(),
    $arrangement->getSesong(),
    $arrangement->getFylke()->getId(),
    $arrangement->getKommuner()->getIdArray()
);
$innslag->setContext($context);

$playback = $innslag->getPlayback()->get($playbackId);

if($playback->erGodkjent()) {
    throw new Exception('Bilde er allerede godkjent');
}

$tittelKunstverk = null;

// Tittle ble send fra arrangørsystemet (en lokalkontakt)
// Set playback id til utstilling (tittel)
if($tittelId) {
    $tittelKunstverk = $innslag->getTitler()->get($tittelId);
    $tittelKunstverk->setPlaybackId($playback->getId());
    WriteTitler::save($tittelKunstverk);
}
else {
    foreach($innslag->getTitler()->getAll() as $tittel) {
        if($tittel->getPlayback() && $tittel->getPlayback()->getId() == $playback->getId()) {
            $tittelKunstverk = $tittel;
        }
    }
}

$imgUrl = $playback->base_url . $playback->file_path . $playback->fil;


$uploadedBildeNavn = $playback->fil;
$extension = pathinfo($uploadedBildeNavn, PATHINFO_EXTENSION);

if(!$playback->erBilde()) {
    throw new Exception('Fil er ikke et bilde');
}

$b64image = base64_encode(curl_get_contents($imgUrl));

$data = base64_decode($b64image);

$whereToSave = '/home/ukmno/private_sync/'. $uploadedBildeNavn;
$result = file_put_contents($whereToSave, $data);


$season = get_option('season');
$place  = get_option('pl_id');

require_once('UKM/Autoloader.php');
global $blog_id;

$sql = new Insert('ukm_bilder');
$sql->add('season', $season);
$sql->add('pl_id', $place);
$sql->add('wp_blog', $blog_id);
$id = $sql->run();

// Hvis filen er overført til wp-content, godkjenn det på Playback og
// set bildeId til Tittel av type Utstilling (Kunstverk)
if($result) {
    $playback->godkjenn();
    WritePlayback::lagre($playback);

    if($tittelKunstverk) {
        $tittelKunstverk->setBildeId($id);
        WriteTitler::save($tittelKunstverk);
    }
}

$name = $season . '_' . $place . '_' . $id . '.' . $extension;
$path = UKM_BILDER_SYNC_FOLDER . $name;

// Kopierer fil til wp
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
} 
else {
    http_response_code(413);
} // ENDIF


if($arrangement->getProgram()->getAntall() > 1) {
    throw new Exception('Det er opprettet mer enn 1 hendelse for type kunstgalleri.');
}
else if($arrangement->getProgram()->getAntall() < 1) {
    throw new Exception('Hendelse finnes ikke. Det må være en hendelse for type kunstgalleri');
}
# Konverter bilde
ConvertBilde::converterBilde($id);

# Tagg bilde
$innslagId = $innslagId;
$imageId = $id;
$fotografId = wp_get_current_user()->ID;
$fotografId = 1;
$hendelseId = $arrangement->getProgram()->forestillinger[0]->id;

TaggerBilde::taggBilde($innslagId, $imageId, $fotografId, $hendelseId);


# Funksjoner brukt i denne filen
function curl_get_contents(String $url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}