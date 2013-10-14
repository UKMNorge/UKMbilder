<?php

class UploadController extends Controller
{

	public function prerender()
	{
		$album = $this->getVar('album');
		$event = $this->getVar('event');
		$band = $this->getVar('band');
		
		if( ! strlen ( $album ) > 0 AND 
		    ! strlen ( $event ) > 0 AND 
		    ! strlen ( $band ) > 0 )
			$this->redirect( 'admin.php?page=UKM_images' );
		
		$pl_id     = intval( get_option( 'pl_id' ) );
		$monstring = new monstring( $pl_id );
		
		if( strlen( $event ) > 0 ):
			
			$eventId = intval( $event );
			$concert = $monstring->concert( $event );
			$this->setData( 'uploadTo', $concert['c_name'] );
			
		elseif (strlen( $band ) > 0 ):
		
			$bandId = intval( $band );
			UKM_loader('api/innslag.api');
			$innslag = new innslag($_GET['band']);
			$this->setData( 'uploadTo', $innslag->g('b_name') );
			
		else:
		
		  $albumId = intval( $album );
		  $sql = new SQL( 'SELECT `a_name` FROM `smartukm_album` WHERE `a_id` = ' . $albumId );
		  $result = $sql->run( 'array' );
		  $this->setData( 'uploadTo', $result['a_name'] );	
			
		endif;
			
		$this->setView( 'upload' );
	}

}