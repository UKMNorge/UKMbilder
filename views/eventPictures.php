<style type="text/css">
	#imgpre {
		width: 300px;
		height: 100px;
		float:left;
	}
	#imgpre img
	{
		margin-left: 50px;
	}
	#imgpre p{
		text-align: left;
		margin-left: 50px;
	}
	#imgpre p.right
	{
		margin-right: 50px;
		float:right;
	}

	#select {
		width:300px;
		height:98px;
		float:left;
		overflow: auto;

	}
	#search {
		width: 120px;
	}
	
	#left {
		width: 400px;
		float:left;
	}
	#right {
		width: 400px;
		float: left;
	}
	
	.clearfix{
		clear:both;
		display:block;
		height:50px;
	}
	button#wrongass {
		margin-bottom:85px;;
	}
	#save {
		margin-left: 50px;
		width: 70px;
	}
	h2{
		margin-left: 40px;
	}
	
	.imageContainer {
		background-image:url('http://ukm.no/wp-content/plugins/UKMimages/img/imgbg.jpg');
		background-repeat:no-repeat;
		width: 650px;
		height: 175px;
		padding: 4px;
		vertical-align: middle;
	}
</style>
<script type="text/javascript" src="http://code.jquery.com/jquery-1.6.1.min.js"></script>
<script>
$(document).ready(function(){
	
	/* Søkefelt... */
	$('input#search').keydown(function(){
		var val = $(this).val();
		val = val.toLowerCase();
		var gr = $(this).parent().attr('rel');
		
		if( val != '' ) {
			$('div.'+gr).each(function(){
				$(this).hide();
			});
		}
		else {
			$('div.'+gr).each(function(){
				$(this).show();
			});
		}
		
		$('input[name="'+gr+'"][value^="'+val+'"]').parent().show();
	});
	
	/* Søkefelt... Helt lik den forrige. Bruker både keydown og keyup for raskest respons */
	$('input#search').keyup(function(){
		var val = $(this).val();
		val = val.toLowerCase();
		var gr = $(this).parent().attr('rel');
		
		if( val != '' ) {
			$('div.'+gr).each(function(){
				$(this).hide();
			});
		}
		else {
			$('div.'+gr).each(function(){
				$(this).show();
			});
		}
		
		$('input[name="'+gr+'"][value^="'+val+'"]').parent().show();
	});
	
	/* Wrong assumption... Gir deg mulig til å velge blant innslag som vanlig  */
	$('button#wrongass').click(function(){
		var gr = $(this).parent().attr('rel');
		
		$('input#sel[name="'+gr+'"]').each(function(){
			if( $(this).attr('checked') == 'checked' ) {
				$(this).removeAttr('checked');
				$('p.right[rel="'+gr+'"]').text( '' );
			}
		});
		
		$(this).parent().hide();
		$('div#imgsel[rel="'+gr+'"]').show();
		
	});
	
	/* Samme som forrige bilde...  */
	$('button#sameas').click(function(){
		var gr = $(this).parent().attr('rel');
		var grn = $(this).parent().attr('data-number');
		grn = grn-1;
		var name = "gr"+grn;
		var value = $('input[name="'+name+'"]:checked').val();
		
		$('input[name="'+gr+'"][value="'+value+'"]').click();
		

	});
	
	/* Velger bilde...  */
	$('input#sel').click(function(){
		var gr = $(this).attr('name');
		$('p.[rel="'+gr+'"]').text( $(this).attr('shortname') );
		$('input#navn-innslag[rel="'+gr+'"]').val($(this).val());
		$('input#navn-bid[rel="'+gr+'"]').val($(this).attr('rel'));

	});
	
	/* Setter riktig navn til høyre for bildet dersom noen bokser har blitt satt som checked fra serversiden  */
	$('input#sel').each(function(){
		if( $(this).attr('checked') == 'checked' ) {
			var gr = $(this).attr('name');
			$('p.[rel="'+gr+'"]').text( $(this).attr('shortname') );
			$('input#navn-innslag[rel="'+gr+'"]').val($(this).val());
			$('input#navn-bid[rel="'+gr+'"]').val($(this).attr('rel'));
		}
	});
	
	$('input#save').click(function(){
		<?php if( strlen( $this->getVar( 'event' ) ) > 0 ):
			      $url = 'upload.php?page=UKM_images&c=pictures&a=save&event='. $this->getVar('event');
				  if(isset($_GET['attach']))
				      $url .= '&change=y';
			      $ftype = 'eventPictures';
			  else:
			  	  $url = 'upload.php?page=UKM_images&c=pictures&a=save&album='. $this->getVar('album');
			  	  $ftype = 'albumPictures';
			  endif;
		?>
		
		var postData = {};
		postData['form_type'] = "<?=$ftype?>";
		postData['author'] = $('#author').val();
		
		$('input#navn-innslag').each(function(){
			postData[$(this).attr('name')+'|innslag'] = $(this).val();
			
		});
		
		$('input#navn-bid').each(function(){
			postData[$(this).attr('name')+'|b_id'] = $(this).val();
		});
		
		$('input#navn-attachid').each(function(){
			postData[$(this).attr('name')+'|attachid'] = $(this).val();
		});
		
		$.post( '<?=$url?>', postData, function(data){
/*var ScreenWidth=window.screen.width;
var ScreenHeight=window.screen.height;
var movefromedge=0;
placementx=(ScreenWidth/2)-((400)/2);
placementy=(ScreenHeight/2)-((300+50)/2);
WinPop=window.open("About:Blank","","width=400,height=300,toolbar=0,location=0,directories=0,status=0,scrollbars=0,menubar=0,resizable=0,left="+placementx+",top="+placementy+",scre enX="+placementx+",screenY="+placementy+",");
var SayWhat = "<p><font color='blue'>This is what the windows text is</font></p>"; 
WinPop.document.write('<html>\n<head>\n</head>\n<body>'+data+'</body></html>');*/

            window.location = "<?= get_admin_url();?>upload.php?page=UKM_images&c=pictures&a=overview&event=<?=$this->getVar('event')?>"; 
		});
	});
});
</script>

<h2>Gi navn til bildene</h2>
    <div class="author_list" style="margin-left: 40px; margin-bottom:10px;">
    Fotograf: <select id="author" name="author">
<?php	
    foreach( $this->getData( 'authors' ) as $author ):
		?>
		<option value="<?=$author['ID']?>"
        <?php if (wp_get_current_user()->ID == $author['ID']) echo ' SELECTED';?>><?=$author['display_name']?></option>
		<?php
			endforeach;
?>
	</select>
    </div>


<?php
	$upload_dir = wp_upload_dir();
	
	$images = $this->getData('images');
	$gr = 0;	  
			  
	foreach( $images as $image ):
		++$gr;
?>
<div class="imageContainer">
	<div id="imgpre">
		<img src="<?=wp_get_attachment_thumb_url($image['attachid']);?>" width="100" /><br />
		<p><?=$this->shortString($image['name'],25)?></p>
		<p rel="gr<?=$gr?>"></p>
	</div>

<?php

	$similar = '';

	foreach( $this->getData( 'selectFrom' ) as $ins ):
		similar_text( strtolower( substr( $image['name'], 0, strlen($image['name'])-4 ) ), strtolower($ins), $percent );
		if( $percent > 70 ):
			$similar = $ins;
			break;			
		endif;
	endforeach;
	
	if( strlen( $similar ) > 0 )
		$selectHidden = true;
	else
		$selectHidden = false;

?>
	<div id="imgsel" rel="gr<?=$gr?>" data-number="<?=$gr?>" <?php if( $selectHidden === true ) echo 'style="display:none;"' ?> >
		<?php if( $gr > 1 ) echo '<button id="sameas">Dette er samme som bildet ovenfor.</button><br />eller<br />'; ?>
		
		S&oslash;k: <input type="text" id="search" size="50" /> <br />
		<div id="select">
		<?php $n = 1;
			foreach( $this->getData( 'selectFrom' ) as $innslag ): ?>
				<div class="gr<?=$gr?>"><label><input type="radio" id="sel" rel="<?=$innslag['id']?>" shortname="<?=$this->shortString($innslag['name'],25)?>" name="gr<?=$gr?>" value="<?=strtolower($innslag['name'])?>" <?php if($innslag['name'] == $similar) echo 'checked=checked' ?>/> <?=$n?>. <?=$innslag['name']?></label></div>
				<?php ++$n;
			endforeach; ?>
	</div>
	<input type="hidden" rel="gr<?=$gr?>" id="navn-attachid" name="<?=$image['name']?>" value="<?=$image['attachid']?>" />
	<input type="hidden" rel="gr<?=$gr?>" id="navn-innslag" name="<?=$image['name']?>" value="" />
	<input type="hidden" rel="gr<?=$gr?>" id="navn-bid" name="<?=$image['name']?>" value="" />
</div>
	<div rel="gr<?=$gr?>" id="same" <?php if( $selectHidden === false) echo 'style="display:none;"' ?> >
		<p>Ut fra bildenavnet tror vi dette innslaget er kalt: <?=$similar?></p>
		<button id="wrongass">Nei, dette er ikke <?=$similar?>, la meg velge.</button>
	</div>
</div>
<div class="clearfix"><br clear="all" /></div>
<?php
	endforeach;
?>
<div class="clearfix"><br /></div>
<input id="save" type="submit" value="Lagre" />
