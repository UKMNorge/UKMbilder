jQuery(document).ready(function(){
	jQuery('#fileupload').fileupload({
	    url: ajaxurl,
		fileTypes: /^image\/(gif|jpeg|png)$/,
	    autoUpload: true,
	    formData: {action: 'UKMbilder_upload'},
	    progressall: function (e, data) {
	        var progress = parseInt(data.loaded / data.total * 100, 10);
	        jQuery('#uploadprogress').css('width', progress+'%');
	        jQuery('#uploadprogress').parent().slideDown();
	        if(progress == 100)
       			jQuery('#uploadprogress').parent().slideUp();
	   		else
   				jQuery('#uploadprogress').parent().slideDown();
	    }
	}).bind('fileuploaddone', function(e, data){
		tagme_reload();
	});
	tagme_reload();
	
	jQuery(document).on('change', '#innslag_selector', function(){tagme_list_selector()});
	jQuery(document).on('click', 'img.tagme', function(){jQuery(this).toggleClass('active');});
	jQuery(document).on('click', '#tag_selected', function(){tagImages()});
	
	jQuery(document).on('click', '.details_show', function(){showBandImages(jQuery(this).parents('li').attr('id'))});
	jQuery(document).on('click', '.details_hide', function(){hideBandImages(jQuery(this).parents('li').attr('id'))});
});


///////////////////////////////////////////////////////////
// LISTE OVER INNSLAG

function showBandImages(selector) {
	jQuery('#'+selector).find('.details_show').hide();
	jQuery('#'+selector).find('.details_hide').show();
	
	jQuery('#'+selector).find('.details').slideDown();
	jQuery('#'+selector).find('.details .loader').slideDown();
	
	jQuery.post(ajaxurl,
				{action: 'UKMbilder_band_images',	
				 band: jQuery('#'+selector).attr('data-innslag')
				},
				function (response) {
					if(response.images.length == 0) {
						selector = '#innslag_'+response.b_id;
						jQuery(selector).find('ol.band_images').html('<li class="alert alert-info">Det er ikke lastet opp noen bilder til dette innslaget</li>');
					} else {
						var template_band_images = Handlebars.compile( jQuery('#handlebars-image-edit').html() );
						jQuery(selector).find('ol.band_images').html( template_band_images(response) );
						jQuery(selector).find('.details .loader').slideUp();
					}
				});
	
}

function hideBandImages(selector) {
	jQuery('#'+selector).find('.details_hide').hide();
	jQuery('#'+selector).find('.details_show').show();
	
	jQuery('#'+selector).find('.details').slideUp();
}


///////////////////////////////////////////////////////////
// LAST OPP BILDER


function tagImages() {
	selected_images = jQuery('.tagme.active');
	if(selected_images.length == 0)
		return alert('Du må velge hvilke bilder som skal merkes først!');

	selected_band = parseInt(jQuery('input[name="innslag"]:checked').val());
	if(selected_band == undefined || isNaN(selected_band))
		return alert('Du må velge hvilket innslag du skal merke bildene med');

	var image_ids = new Array();
	selected_images.each(function() {
		image_ids.push( jQuery(this).attr('id') );
	});

	jQuery.post(ajaxurl,
				{action: 'UKMbilder_do_tag',
				 images: image_ids,
				 band: selected_band},
				function response(response) {
					if(response.success) {
						jQuery('.tagme.active').remove();
						jQuery('input[name="innslag"]:checked').removeProp('checked');
						if(jQuery('.tagme').length == 0)
							jQuery('#container_ukmbilder_steg2').slideUp();
						
					} else
						alert('Beklager, en feil oppsto ved merking av innslag!');
				}
		)
}


function tagme_list_selector() {
	jQuery.post(ajaxurl,
				{action: 'UKMbilder_innslag', 'c_id': jQuery('#innslag_selector').val()},
				function(response){
					var template_innslag = Handlebars.compile(jQuery('#handlebars-innslag').html());
					jQuery('#innslag').html(template_innslag(response));
				});	
}






function tagme_reload() {
	console.info('Request images for tagging');
	jQuery.post(ajaxurl,
				{action: 'UKMbilder_tagme'},
				function(response){
					tagme_response(response);
				});
}

function tagme_response( response ) {
	console.log( response );
	var template_tagme = Handlebars.compile(jQuery('#handlebars-image-tag').html());
	jQuery('#tag_images').html( template_tagme( response ) );
	if(response.images.length == 0)
		jQuery('#container_ukmbilder_steg2').slideUp();
	else
		jQuery('#container_ukmbilder_steg2').slideDown();
	console.log('Images loaded to DOM');
	images_compress();
}

function images_compress() {
	console.info('Request new compression job');
	jQuery.post(ajaxurl,
				{action: 'UKMbilder_compress'},
				function(response){
					console.log('Compression status:');
					console.log(response);
					console.log('Reload status: ' + response.reload + ' => ' + parseInt(response.reload));
					if(parseInt(response.reload) > 0) {
						console.warn('Reload tagging list');
						tagme_reload();
					}
				});
}