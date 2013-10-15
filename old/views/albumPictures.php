<style type="text/css">
	#imgpre {
		width: 350px;
		height: 100px;
		float:left;
	}
	#imgpre img
	{
		width:250px;
		height:120px;
		margin-left: 50px;
	}
	#imgpre p
	{
		margin-left: 50px;
		float:left;
	}
	#imgpre p.right
	{
		margin-right: 50px;
		float:right;
	}

	#select {
		width:350px;
		height:98px;
		float:left;
		overflow: auto;

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
</style>
<h2>Gi navn til bildene</h2>
<form method="POST" action="upload.php?page=UKM_images&c=pictures&a=save&album=<?=$_GET['album']?>">
<input type="hidden" name="form_type" value="albumPictures" />
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
	
	foreach( $this->getData('images') as $image ):
?>

<div id="imgpre">
	<img src="<?=wp_get_attachment_thumb_url($image['attachid'])?>" />
	<p><?=$this->shortString($image['name'],24)?></p>
</div>

<div id="imgsel">
	<p>Bildetekst:</p>
	   <textarea rows="4" name="<?=$image['name']?>[text]"></textarea>
	   <input type="hidden" name="<?=$image['name']?>[attachid]" value="<?=$image['attachid']?>" />
	
</div>

<div class="clearfix"></div><br /><br />
<?php
	endforeach;
?>
<input id="save" type="submit" value="LAGRE" />
</form>