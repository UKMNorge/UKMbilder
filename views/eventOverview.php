<style type="text/css">

.images {
	max-width: 98%;
}

#image {
	float:left;
	margin-right: 15px;
	margin-bottom: 15px;
}

#image img#img {
	float:left;
}

.tools {
	float:left;
	width:100px;
}

.clearfix {
	clear:both;
}
.tools p 
{
	font-size: 11px;¬
	padding: 0px;
	margin: 0px;
	margin-bottom:10px;
}

.over {
	display:block;
	width: 100%;
	height: 20px;
	border-bottom: 1px solid #999;
	margin-bottom: 10px;
	font-weight: bold;
}

.tools .Delete {
	width: 110px;
	text-align: center;
}

.tools .Edit {
	width: 110px;
	text-align: center;
}
a {
	text-decoration: none;
}

</style>

<script type="text/javascript" src="http://code.jquery.com/jquery-1.6.1.min.js"></script>
<script>
$(document).ready(function() {
	$('a#change').click(function(){
		window.location = 'upload.php?page=UKM_images&c=pictures&a=name&event=<?=$this->getVar('event')?>&attach='+$(this).attr('rel');
	});
	$('a#del').click(function(){
    	var answer = confirm("Er du sikker?")
    	if (answer){
    		$(this).html('<span style="text-align:center;font-weight:bold;">Fjerner...</span>')
    		$.post( 'upload.php?page=UKM_images&c=pictures&a=delete&event=<?=$this->getVar('event')?>', { 'attach_id' : $(this).attr('rel') }, function(data) {
                 window.location.reload();
    		});
    	}
	});
});
</script>
<h2>Bilder i "<?=$this->getData('name')?>"</h2>

<?php
	$i = 0;
	$upload_dir = wp_upload_dir();
	
	
	
	foreach( $this->getData('bands') as $band ):
	++$i;
	$attachments = $this->eventAttachments( $band[ 'b_id' ] );
	
	
	$innslag = new innslag( $band['b_id'] );
	$items = $innslag->related_items();
	$bilder = $items['image'];

?>
	
	<div class="over"><?=$i?>. <?=$band['b_name']?></div>	
	<div class="images">

<?php
	if( ! count( $bilder ) > 0 ):
	   echo '<span style="color:red">Ingen bilder.</span><br /><br /></div>';
	else:
?>

<?php foreach( $bilder as $item ) { 
			$url = $item['blog_url'].'/files/';
			$large = $url . (!isset($item['post_meta']['sizes']['thumbnail'])
							? $item['post_meta']['file']
							: $item['post_meta']['sizes']['thumbnail']['file']
							);

			$imgRealPath = preg_replace('(\/pl[0-9]+\/files\/)',
										'/wp-content/blogs.dir/'.$item['blog_id'].'/files/',
										$url);
			$imgRealPath = preg_replace('([a-z,-]+\/files\/)',
										'wp-content/blogs.dir/'.$item['blog_id'].'/files/',
										$imgRealPath);
			$linkUrl = $url . $item['post_meta']['file'];
#			var_dump($item);

?>
	<div id="image">
	<a href="<?= $linkUrl ?>" target="_blank"><img id="img" src="<?= $large ?>" /></a>
 				<div class="tools">
					<a href="#" id="change" rel="<?=$item['post_id']?>"><div class="Edit">
						<img height="16" width="16" src="http://ico.ukm.no/pencil-32.png" />
						<p>Endre<br />tilknytning</p>
					</div></a>
					<a href="#" id="del" rel="<?=$item['post_id']?>"><div class="Delete">
						<img height="16" width="16" src="http://ico.ukm.no/trash-32.png" />
						<p>Fjern bilde</p>
					</div></a>
				</div>	
	</div>
<?php } ?>


		<?php foreach( $attachments as $attach_id => $info ): ?>
<!--			<div id="image">
				<img id="img" src="<?=wp_get_attachment_thumb_url($attach_id)?>" />
 				<div class="tools">
					<a href="#" id="change" rel="<?=$attach_id?>"><div class="Edit">
						<img height="16" width="16" src="http://ico.ukm.no/pencil-32.png" />
						<p>Endre<br />tilknytning</p>
					</div></a>
					<a href="#" id="del" rel="<?=$attach_id?>"><div class="Delete">
						<img height="16" width="16" src="http://ico.ukm.no/trash-32.png" />
						<p>Fjern bilde</p>
					</div></a>
				</div>
			</div>-->
			
		<?php endforeach;?>
			<div class="clearfix"></div>
	</div>

<?php
    endif;
?>


<?php
	endforeach;
?>