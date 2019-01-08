<?php
$program = $monstring->forestillinger();
$alle_innslag = $monstring->innslag();


$monstring_v2 = new monstring_v2( get_option('pl_id') );
$alle_innslag_v2 = $monstring_v2->getInnslag();

$INFOS = array('program' => $program, 'alle_innslag' => $alle_innslag_v2->getAll(), 'users' => UKMbilder_users());