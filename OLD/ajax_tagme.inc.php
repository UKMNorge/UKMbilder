<?php
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');

require_once('UKM/sql.class.php');

$sql = new SQL("SELECT * 
				FROM `ukm_bilder`
				WHERE `pl_id` = '#pl_id'
				AND `season` = '#season'
				AND `status` != 'tagged'
				AND `status` != 'crash'",
				array('pl_id' => get_option('pl_id'),
					  'season' => get_option('season')
					  ));
$res = $sql->run();

$images = array();
while( $r = SQL::fetch($res) ) {
	if($r['status'] == 'uploaded' || $r['status'] == 'compressing') {
		$r['compressing'] = true;
		$r['url'] = '//ukm.no/wp-content/plugins/UKMbilder/img/compressing.gif';
	}
	$images[] = $r;

}

die(json_encode(array('images' => $images)));