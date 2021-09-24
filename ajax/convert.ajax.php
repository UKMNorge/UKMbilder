<?php

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Database\SQL\Insert;
use UKMNorge\Database\SQL\Query;
use UKMNorge\Database\SQL\Update;

require_once( UKMbilder::$path_plugin . 'class/ConvertBilde.class.php');


$imageId = $_POST['imageId'] ? intval($_POST['imageId']) : 0; //TODO: handle non-integer input with UKM-approved method


return UKMbilder::addResponseData('imageData', ConvertBilde::converterBilde($imageId));