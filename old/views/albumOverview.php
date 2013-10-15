<style type="text/css">
	#imgpre {
		width: 300px;
		height: 100px;
		float:left;
	}
	#imgpre img
	{
		margin-left: 10px;
	}
	#imgpre p
	{
		margin-left: 10px;
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
		margin-left: 10px;
		width: 70px;
	}

	.leftGrey {
		float:left;
		width: 250px;
		height: 110px;
		background: #ddd;
		padding:8px;
	}
	.middle {
		float:left;
		width: 80px;
		text-align: center;
		margin-top:50px;
	}
	h4 {
		padding: 0px;
		margin: 0px;
		margin-bottom: 15px;
	}
	.leftGrey h3 {
		margin-top:40px;
		margin-left: 30px;
	}
	a .leftGrey:hover {
		background: #ccc;
	}
	a {
		text-decoration: none;
	}
	.trash {
		float:left;
		margin-left:20px;
	}
	.text {
		float:left;
	}
	.trash img {
		padding-left:15px;
		margin-top:45px;
	}
	</style>
	
<script type="text/javascript" src="http://code.jquery.com/jquery-1.6.1.min.js"></script>
<script>
$(document).ready(function() {
	$('a#del').click(function(){
    	var answer = confirm("Er du sikker?")
    	if (answer){
    		$(this).html('Fjerner...');
    		$.post( 'upload.php?page=UKM_images&c=pictures&a=delete&album=<?=$this->getVar('album')?>', { 'attach_id' : $(this).attr('rel') }, function(data) {
                 window.location.reload();
/*var ScreenWidth=window.screen.width;
var ScreenHeight=window.screen.height;
var movefromedge=0;
placementx=(ScreenWidth/2)-((400)/2);
placementy=(ScreenHeight/2)-((300+50)/2);
WinPop=window.open("About:Blank","","width=400,height=300,toolbar=0,location=0,directories=0,status=0,scrollbars=0,menubar=0,resizable=0,left="+placementx+",top="+placementy+",scre enX="+placementx+",screenY="+placementy+",");
var SayWhat = "<p><font color='blue'>This is what the windows text is</font></p>"; 
WinPop.document.write('<html>\n<head>\n</head>\n<body>'+data+'</body></html>');*/
    		});
    	}
		return false;
	});
});
</script>


<h2>Albumet "<?=utf8_encode($this->getData('name'))?>"</h2>

<p style="color:red"><img src="http://ico.ukm.no/alert_icon.gif" /> Albumet vises ikke p&aring; nettsiden f&oslash;r det er knyttet til en artikkel/innlegg<br /><br /></p>

<h3>Vil du knytte albumet til et innlegg?</h3>
<a href="upload.php?page=UKM_images&c=pictures&a=post&album=<?=$_GET['album']?>">
	<div class="leftGrey">
		<h3><img src="http://ico.ukm.no/plus-icon.png" /> Opprett nytt innlegg</h3>
	</div>
</a>
<div class="middle">Eller</div>
<div class="leftGrey">
	<form method="POST" action="upload.php?page=UKM_images&c=pictures&a=attach&album=<?=$_GET['album']?>">
		<h4>Knytt til eksisterende innlegg</h4>
		<select name="post">
			<option disabled>VELG INNLEGG</option>
	<?php 
		foreach( $this->getData( 'wp_posts' ) as $post ):
		
			echo '<option value="'.$post->ID.'">'.$post->post_title.'</option>';
		
		endforeach;
	?>
		</select>
		<br /><br />
		<input type="submit" value="Knytt sammen" />
	</form>
</div>

<div class="clearfix"></div>
<form method="POST" action="upload.php?page=UKM_images&c=pictures&a=save&album=<?=$_GET['album']?>&s=1">
<input type="hidden" name="form_type" value="albumPictures" />
<h2>Alle bilder i albumet</h2>
<?php 
	$upload_dir = wp_upload_dir();
	$attachments = $this->getData( 'attachments' );
	
	foreach( $attachments as $attach => $info ):
		$meta = wp_get_attachment_metadata( $attach );
?>

<div id="imgpre">
	<img src="<?=wp_get_attachment_thumb_url($attach)?>" />
	<br />
	<p><?=$this->shortString($meta['file'],24)?></p>
</div>

<div id="imgsel">
	<div class="text">
		<p>Bildetekst</p>
	    <textarea rows="4" name="<?=$attach?>[text]"><?php echo get_the_title($attach) ?></textarea>
	    <input type="hidden" name="<?=$attach?>[attachid]" value="<?=$attach?>" />
	</div>
	<a id="del" rel="<?=$attach?>" href=""><div class="trash">
		<img height="16" width="16" src="http://ico.ukm.no/trash-32.png" /><br />
		<span style="font-size: 11px">Fjern bilde</span>
	</div></a>
</div>

<div class="clearfix"></div><br /><br />
<?php
	endforeach;
?>
<input id="save" type="submit" value="LAGRE" />
</form>
<br /><br />