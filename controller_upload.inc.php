<?php
require_once('UKM/forestilling.class.php');

$hendelse = new forestilling($_GET['c_id']);

$INFOS = array('hendelse' => $hendelse);