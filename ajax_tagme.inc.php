<?php

require_once('UKM/sql.class.php');

$sql = new SQL("SELECT * 
				FROM `ukm_bilder`
				WHERE `pl_id` = '#plid'
				AND `season` = '#season'",
				array('pl_id' => get_option('pl_id'),
					  'season' => get_option('season')
					  ));
echo $sql->debug();
$res = $sql->run();

$images = array();
while( $r = mysql_fetch_assoc($res) ) {
	$images[] = $r;
}

die(json_encode(array('images' => $images)));