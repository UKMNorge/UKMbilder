<?php

use UKMNorge\Arrangement\Arrangement;

$arrangement = new Arrangement( get_option( 'pl_id ') );

UKMbilder::addViewData('arrangement', $arrangement);