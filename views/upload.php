<!-- Load Queue widget CSS and jQuery -->
<style type="text/css">@import url(/wp-content/plugins/UKMNorge/plupload/js/jquery.plupload.queue/css/jquery.plupload.queue.css);</style>
<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script type="text/javascript">
	google.load("jquery", "1.3");
</script>

<!-- Thirdparty intialization scripts, needed for the Google Gears and BrowserPlus runtimes -->
<script type="text/javascript" src="/wp-content/plugins/UKMNorge/plupload/js/plupload.gears.js"></script>
<script type="text/javascript" src="http://bp.yahooapis.com/2.4.21/browserplus-min.js"></script>

<!-- Load plupload and all it's runtimes and finally the jQuery queue widget -->
<script type="text/javascript" src="/wp-content/plugins/UKMNorge/plupload/js/plupload.full.js"></script>
<script type="text/javascript" src="/wp-content/plugins/UKMNorge/plupload/js/jquery.plupload.queue/jquery.plupload.queue.js"></script>

<script type="text/javascript">
plupload.addI18n({
        'Select files' : 'Last opp bildene til &quot;<?=$this->getData( 'uploadTo' )?>&quot;',
        'Add files to the upload queue and click the start button.' : 'Legg til filer i ved &aring; klikke &quot;Legg til filer&quot;, og klikk deretter &quot;Start opplasting&quot;<br />TIPS: Skal du velge flere bilder kan du bruke shift-tasten, eller ctrl-tasten p&aring; tastaturet.',
        'Filename' : 'Filnavn',
        'Status' : 'Status',
        'Size' : 'St&oslash;rrelse',
        'Add files' : 'Legg til filer',
    	'Start upload':'Start opplasting',
        'Stop current upload' : 'Avbryt',
        'Start uploading queue' : 'Start opplastingsk&oslash;',
        'Drag files here.' : 'Dra filer hit'
});
</script>

<script type="text/javascript">
// Convert divs to queue widgets when the DOM is ready
$(function() {

function serializeArray(array)
{
	var serialized = "";
	var size = 0;
	
	for (var key in array)
	{
		++size;
		serialized = serialized + "s:" +
				unescape(encodeURIComponent(String(key))).length + ":\"" + String(key) + "\";s:" +
				unescape(encodeURIComponent(String(array[key]))).length + ":\"" + String(array[key]) + "\";";
	}
	
	serialized = "a:" + size + ":{" + serialized + "}";
	
	return serialized;
}

	function attachCallbacks(Uploader) {
		var i = 1;
		var filesarr = new Array();
	    Uploader.bind('FileUploaded', function(Up, File, Response) {
	    	
	    	filesarr[i] = File.name;
	    	i++;
	    	
	        if( (Uploader.total.uploaded + 1) == Uploader.files.length) {
	        	<?php 
	        		if( strlen( $this->getVar( 'album' ) ) > 0 ):
	        	?>
	            window.location = 'upload.php?page=UKM_images&c=pictures&a=name&album=<?=$this->getVar('album')?>&images='+serializeArray(filesarr);
	            <?php
					elseif (isset($_GET['band'])):
					?>
						window.location = 'upload.php?page=UKM_images&c=pictures&a=name&band=<?=$this->getVar('band')?>&images='+serializeArray(filesarr);

					<?php
	            	else:
	           	?>
	           	window.location = 'upload.php?page=UKM_images&c=pictures&a=name&event=<?=$this->getVar('event')?>&images='+serializeArray(filesarr);
	           	<?php
	           		endif;
	           	?>
	           	
	        }
	    });
	}

	$("#uploader").pluploadQueue({
		// General settings
		runtimes : 'html5,gears,flash,silverlight,browserplus'/*'silverlight,gears,flash'*/,
		url : ajaxurl,
		max_file_size : '20mb',
		unique_names : false,
		preinit: attachCallbacks,
		multipart_params : <?php echo json_encode(array( 'action' => 'my_action' )); ?>,
		multipart: true,
		file_data_name : 'async-upload',

		// Resize images on clientside if we can
		resize : {width : 3600, height : 3600, quality : 100}, // 100%,A3@220DPI

		// Specify what files to browse for
		filters : [
			{title : "Image files", extensions : "jpg,gif,png,bmp,wbmp"},
		],

		// Flash settings
		flash_swf_url : '/wp-content/plugins/UKMNorge/plupload/js/plupload.flash.swf',

		// Silverlight settings
		silverlight_xap_url : '/wp-content/plugins/UKMNorge/plupload/js/plupload.silverlight.xap'
	});

	// Client side form validation
	$('form').submit(function(e) {
		var uploader = $('#uploader').pluploadQueue();

		// Validate number of uploaded files
		if (uploader.total.uploaded == 0) {
			// Files in queue upload them first
			if (uploader.files.length > 0) {
				// When all files are uploaded submit form
				uploader.bind('UploadProgress', function() {
					if (uploader.total.uploaded == uploader.files.length)
						$('form').submit();
				});

				uploader.start();
			} else
				alert('Du m&aring; velge minst en fil.');

			e.preventDefault();
		}
	});

	//$.post( ajaxurl, { 'action': 'my_action' } , function(data) {
	//	alert(data);
	//});
	

});
</script>			

<form>
	<div id="uploader">
		<p>Vennligst vent, laster inn opplaster...<br /><br />
		Hvis opplasteren ikke dukker opp i l&oslash;pet av kort tid, betyr det at nettleseren din har ikke st&oslash;tte for Silverlight, Gears eller Flash.<br />
		<a href="http://www.mozilla.org/nb-NO/firefox/new/">P&aring; tide med noe nytt?</a></p>
	</div>
</form>