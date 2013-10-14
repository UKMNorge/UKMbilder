<?php

class PicturesController extends Controller
{
	public $mode;

	/**
	 * __wpAtt function.
	 * 
	 * Oppretter ett innlegg med bilde som attachment
	 *
	 * @access private
	 * @param mixed $wpattatch
	 * @return void
	 */
	private function __wpAtt($wpattatch) {	
		/// WORDPRESS GENERATE ATTACHMENT AND THUMBS
		$wp_filetype = wp_check_filetype(basename($wpattatch), null );
		$attachment = array(
				  	  'post_mime_type' => $wp_filetype['type'],
					  'post_title' => preg_replace('/\.[^.]+$/', '', basename($wpattatch)),
					  'post_content' => '',
					  'post_status' => 'inherit'
					);
		$attach_id = wp_insert_attachment( $attachment, $wpattatch);
		// you must first include the image.php file
		// for the function wp_generate_attachment_metadata() to work
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		$attach_data = wp_generate_attachment_metadata( $attach_id, $wpattatch );
		wp_update_attachment_metadata( $attach_id, $attach_data );
		/// EOWP-GEN 
		return $attach_id;
	}

	/**
	 * __wpAttUpdate function.
	 * 
	 * For å oppdatere post med wpattachment
	 *
	 * @access private
	 * @param mixed $wpattach
	 * @param mixed $b_name
	 * @return void
	 */
	private function __wpAttUpdate($wpattach, $b_name, $post_author = '') {
		global $wpdb;
		
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		require_once(ABSPATH . 'wp-includes/post.php');
		
		if ($post_author != '' && is_numeric($post_author))
			$arrUpdate['post_author'] = $post_author;
		
		$arrUpdate['post_title'] = $b_name; // Array of key(col) => val(value to update to)
		
		update_post_meta($wpattatch, '_wp_attachment_image_alt', $b_name);
		$wpdb->update($wpdb->posts, // Table
				      $arrUpdate, // Array of key(col) => val(value to update to)
				      array('ID' => $wpattach) // Where
					 );
	}

	/**
	 * prerender function.
	 * 
	 * Dette skjer før view blir renderet
	 *
	 * @access public
	 * @return void
	 */
	public function prerender()
	{
		$album = $this->getVar('album');
		$event = $this->getVar('event');
		$band = $this->getVar('band');
		
		if( ! strlen ( $album ) > 0 AND 
		    ! strlen ( $event ) > 0 AND 
		    ! strlen ( $band ) > 0 )
			$this->redirect( 'upload.php?page=UKM_images' );
			
		if( strlen( $album ) > 0 )
			$this->mode = 'album';
		else if( strlen( $band ) > 0 )
			$this->mode = 'band';
		else
			$this->mode = 'event'; 
	}
	
	/**
	 * nameAction function.
	 * 
	 * For å navngi bilder
	 *
	 * @access public
	 * @return void
	 */
	public function nameAction(){
	    set_time_limit ( 3600 );
	    $imguplcounter = 0;
		if( isset( $_GET['attach'] ) ):
			$imgs[] = array('name' => '', 'attachid' => $_GET['attach']);
		else: 
			$images = $this->getVar('images');
			$images = unserialize ( urldecode ( stripslashes ( $images ) ) );
			
			if( ! is_array( $images ) )
				$this->redirect( 'upload.php?page=UKM_images&c=upload&'.$this->mode.'='.$this->getVar($this->mode));
				
			/* Liste ut alle filer i mappen */ 
			$dir = wp_upload_dir();
			$files = array();
			$handle = opendir( $dir['path'] );
			while (false !== ($file = readdir($handle))) {
				$files[] = $file;
			}
			closedir( $handle );
					
			/* Initiere arrays */
			$imgs = array();
			$numbers = array();
	
			echo '<span id="imgKomp" style="font-weight:bold;">Vennligst vent, komprimerer bilder...';
			foreach( $images as $image ):
				$imguplcounter++;
				echo 'Bilde '. $imguplcounter.'<br />';
				$image = preg_replace('/[^\w\._]+/', '', $image);
				$image = str_replace(array('æ,ø,å','(',')',' '),array('a,o,a','','',''), $image);
						
				$image = explode('.', $image);
				$type = array_pop($image);
				$image = implode('.', $image);
				$image = str_replace(' ', '', $image);
				$image = str_replace('-', '', $image);
				
                $count = 0;

                /* For hvert bilde, se om filen allerede eksisterer */
                while( file_exists( $dir['path'] . '/' . $image . '_' . ( $count + 1 ) . '.' . $type  ) )          
                    ++$count;
                
                if( $count > 0 )
                    $numbers[$image] = $count;
					
				if( isset( $numbers[$image] ) AND is_numeric( $numbers[$image] )  ):
					/* Det finnes flere bilder med samme navn */
					$n = $numbers[$image]; // Henter ut h¿yeste tall (altsŒ, siste bilde)
					$name = $image . '_' . $n . '.' . $type; // Legger til i array
							
				else:
					/* Finnes ikke andre bilder med samme navn, legger i array */
					$name = $image . '.' . $type;
						
				endif;
						
				$wpattatch = $dir['path'] .'/' . $name;
				$wpattID = $this->__wpAtt($wpattatch);
						
				$imgs[] = array( 'name' => $name, 'attachid' => $wpattID );
						
			endforeach;
			echo '</span>';
			echo '<script language="javascript" type="text/javascript">
					jQuery("#imgKomp").hide();
				  </script>';
		endif;
		global $wpdb;
		
		$wp_user_search = get_users( array('fields'=>array('ID','display_name')) );
		$userData = array();
		foreach ( $wp_user_search as $user ) {
			$userData[] = array('display_name'=>ucwords($user->display_name), 'ID'=>$user->ID);
		}
	
		switch( $this->mode ):
			case 'event':
				
				$pl_id = intval( get_option( 'pl_id' ) );
				$monstring = new monstring( $pl_id );
				$bandIds = $monstring->concertBands( intval( $this->getVar( 'event' ) ) );
				$innslag = array();
				
				foreach( $bandIds as $bandId ):
					$i = new innslag( $bandId['b_id'] );
					$innslag[] = array( 'name' => $i->get('b_name'),
								 		'id' => $bandId['b_id']);
				endforeach;
				
				
				
				$this->setData( 'authors', $userData );
				
				$this->setData( 'images', $imgs );
				$this->setData( 'selectFrom', $innslag );
				
				$this->setView( 'eventPictures' );
								
				break;
				
			case 'album':
			
				$this->setData( 'authors', $userData );
                $this->setData( 'images', $imgs );
				$this->setView( 'albumPictures' );
				
				break;	
			
			case 'band':

				$path = wp_upload_dir();
				$path = $path['url'];
				
				$b_id = intval( $_GET['band'] );
				foreach( $imgs as $k => $v ) {
					$path = $path . $k;
					
					$attach_id = $v['attachid'];
										
					$meta = wp_get_attachment_metadata( $attach_id );
					
					$folder = substr($meta['file'],0,strrpos($meta['file'],'/')+1);
					foreach($meta['sizes'] as $size => $info)
						$meta['sizes'][$size]['file'] = $folder.$meta['sizes'][$size]['file'];
					
					$saveMeta = array('file'=>$meta['file'],
									  'sizes'=>$meta['sizes'],
									  'author'=>$_POST['author']);
					$rel = new related($b_id);
					$rel->set($attach_id, 'image', $saveMeta);

					// Get band info to update image
					$innslag = new innslag($b_id);
					$b_name = $innslag->get('b_name');
					
					$this->__wpAttUpdate($attach_id, $b_name, $_POST['author']);
				}
				?><script>window.location = 'admin.php?page=UKMVideresending&steg=3#b_<?=$b_id;?>';</script><?php
				exit;
				break;
		endswitch;
	}
	
	/**
	 * overviewAction function.
	 * 
	 * @access public
	 * @return void
	 */
	public function overviewAction()
	{
		switch( $this->mode ):
		
			case 'event':
			
                $pl_id = intval( get_option( 'pl_id' ) );
                $event = intval( $this->getVar( 'event' ) );
                $monstring = new monstring( $pl_id );
                $bands = $monstring->concertBands( $event );
				$concert = $monstring->concert( $event );
                $innslag = array();
                
                foreach( $bands as $band ):
                    $inns = new innslag( $band['b_id'] );
                    $innslag[] = array( 'b_id' => $band['b_id'], 'b_name' => utf8_encode($inns->info['b_name']) );
                endforeach;
			
				$this->setData( 'name', $concert['c_name'] );
                $this->setData( 'bands', $innslag );
				$this->setView( 'eventOverview' );
			
				break;
				
			case 'album':
			
				global $wpdb;
				
				$wp_posts = $wpdb->get_results("SELECT ID, post_title FROM $wpdb->posts WHERE (post_status = 'publish' OR post_status = 'draft') AND post_title != '' ORDER BY post_modified DESC");

                $album_id = intval( $this->getVar( 'album' ) );
                
                if( $album_id > 0 ):
                
					$att = new related(false,$album_id);
					$attachments = $att->getAlbum();
                                            
                    $sql = new SQL( 'SELECT `a_name` FROM `smartukm_album` WHERE `a_id` = ' . $album_id );
                    $result = $sql->run('field','a_name');
                        
                    $this->setData( 'name', $result );
                    $this->setData( 'attachments', $attachments );
                    $this->setData( 'wp_posts', $wp_posts );
                    
                endif;
		
				$this->setView( 'albumOverview' );
		
				break;
		
		endswitch;
	}
	
	/**
	 * saveAction function.
	 * 
	 * @access public
	 * @return void
	 */
	public function saveAction(){
		$type = $this->getVar( 'form_type' );
		
		switch( $type ):
		
			case 'eventPictures':
			
        		$post = array();
         		foreach( $_POST as $k => $v ):
        			if( strpos( $k, '|' ) !== false ):       				
        				$arr = explode( '|', $k );
        				$post[$arr[0]][$arr[1]] = $v;
        			endif;
        		endforeach;
		
				$path = wp_upload_dir();
				$path = $path['url'];
				
				foreach( $post as $k => $v ):
					$path = $path . $k;
					$b_id = intval( $v['b_id'] );
					
					$attach_id = $v['attachid'];
										
					$meta = wp_get_attachment_metadata( $attach_id );
					
					$folder = substr($meta['file'],0,strrpos($meta['file'],'/')+1);
					foreach($meta['sizes'] as $size => $info)
						$meta['sizes'][$size]['file'] = $folder.$meta['sizes'][$size]['file'];
					
					$saveMeta = array('file'=>$meta['file'],
									  'sizes'=>$meta['sizes'],
									  'author'=>$_POST['author']);
					$rel = new related($b_id);
					$rel->set($attach_id, 'image', $saveMeta);

					// Get band info to update image
					$innslag = new innslag($b_id);
					$b_name = $innslag->get('b_name');
					
					$this->__wpAttUpdate($attach_id, $b_name, $_POST['author']);				
					
				endforeach;
			
				break;
				
			case 'albumPictures':
				foreach( $_POST as $k => $v ):
				    if( $this->getVar('s') != 1 ):
	    				if( is_array( $v ) ):
	        				$album_id = $this->getVar( 'album' );
	        				$attach_id = $v['attachid'];
	        				
	    					$rel = new related(false,$album_id);
	    					$rel->set($attach_id, 'image');
	        				
	        				$this->__wpAttUpdate($attach_id, $v['text'],$_POST['author']);
	    				endif;
	    			else:
	    				if( is_array( $v ) ):
	    					$this->__wpAttUpdate($v['attachid'], $v['text'],$_POST['author']);	    				
	    				endif;
	    			endif;
                endforeach;
               
                $this->redirect('upload.php?page=UKM_images&c=pictures&a=overview&album='. $this->getVar('album') ); 
			
				break;
				
			case 'eventOverview':
			
				break;
				
			case 'albumOverview':
			
				break;
		
		endswitch;
	}
	
	/**
	 * eventAttachments function.
	 * 
	 * Hent alle attachments tilhørende ett innslag
	 *
	 * @access public
	 * @param mixed $b_id
	 * @return void
	 */
	public function eventAttachments( $b_id ) {
		$rel = new related($b_id);
		return $rel->get();             
	}
	
	/**
	 * shortString function.
	 * 
	 * Forkorter streng
	 *
	 * @access public
	 * @param mixed $str
	 * @param int $length (default: 14)
	 * @return void
	 */
	public function shortString( $str, $length = 14 )
	{
		if( strlen( $str ) >= $length ):
			$separator = '...';
			$separatorlength = strlen($separator) ;
			$maxlength = $length;
			$start = $maxlength / 2 ;
			$trunc =  strlen($str) - $maxlength;

			return substr_replace($str, $separator, $start, $trunc);
		else:
			return $str;
		endif;
	}
	
	/**
	 * deleteAction function.
	 * 
	 * Slette bilder fra album eller innslag
	 *
	 * @access public
	 * @return void
	 */
	public function deleteAction()
	{
    
        switch( $this->mode ):
            
            case 'event':
            
                $attach_id = intval( $this->getVar( 'attach_id' ) );
                $rel = new related(false);
                $rel->delete($attach_id, 'image');
            
                break;
                
            case 'album':
            
            	$attach_id = intval( $_POST[ 'attach_id' ] );
            	$album_id = intval( $_GET['album'] );
				
				$related = new related(false);
				$related->delete($attach_id,'image');
            
                break;
                
        endswitch;
	}
	
	public function attachAction() 
	{
		$album_id = intval( $_GET['album'] );
	
		if( isset( $_POST['post'] ) ):
			
			$post_id = intval( $_POST['post'] );
			add_post_meta( $post_id, 'album_id', $album_id );
			
		endif;
		
		$this->redirect( 'post.php?post='.$post_id.'&action=edit' );
	}
	
	public function postAction()
	{
		$album_id = intval( $_GET['album'] );
		
		$current_user = wp_get_current_user();
		
		$my_post = array(
		     'post_title' => ' ',
		     'post_content' => '',
		     'post_status' => 'draft',
		     'post_author' => $current_user->ID
		 );
		 
		 $post_id = wp_insert_post( $my_post );
		 
		 add_post_meta( $post_id, 'album_id', $album_id );
		 
		 //echo $post_id;
		 
		 $this->redirect( 'post.php?post='.$post_id.'&action=edit' );
	}

}