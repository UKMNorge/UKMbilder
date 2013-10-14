<?php

class DefaultController extends Controller
{
	public function prerender()
	{
		$pl_id 	   = intval( get_option( 'pl_id' ) );
		$monstring = new monstring( $pl_id );
		$concerts  = $monstring->concerts('c_start',false);
		
		global $blog_id;
		$sql = new SQL("SELECT `a_id`, `a_name` 
						FROM `smartukm_album` 
						WHERE `blog_id` = '#blogid'
						ORDER BY `a_id` ASC",
						array('blogid'=>$blog_id));
		$result = $sql->run();
        $albums = array();
        
        while( $row = mysql_fetch_assoc( $result ) )
            $albums[] = $row;
            
        
        $this->setData( 'concerts', $concerts );
        $this->setData( 'albums', $albums );
	}
	
	public function createAction()
	{
	    if( strlen( $_POST['album_name'] ) > 0 ):
	    	global $blog_id;
            $sql = new SQLins( 'smartukm_album' );
            $sql->add( 'a_name', $_POST['album_name'] );
            $sql->add( 'blog_id', $blog_id);
            $sql->run();
        endif;
        $this->redirect( 'upload.php?page=UKM_images' );
	}
}