<?php
$sql = new SQL("SELECT * 
				FROM `ukm_bilder`
				WHERE `id` = '#id'"
				array('id' => $_POST['image_id']
					  ));
$res = $sql->run();

while( $r = mysql_fetch_assoc($res) ) {
	if($r['status'] == 'uploaded' || $r['status'] == 'compressing') {
		$r['compressing'] = true;
		$r['url'] = 'http://ukm.no/wp-content/plugins/UKMbilder/img/compressing.gif';
	}
}

die(json_encode($r));