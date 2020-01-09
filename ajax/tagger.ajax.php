<?php

use UKMNorge\Arrangement\Arrangement;

$imageId = $_POST['imageId'] ? intval($_POST['imageId']) : 0; //TODO: handle non-integer input with UKM-approved method

$arrangement = new Arrangement( get_option('pl_id') );

