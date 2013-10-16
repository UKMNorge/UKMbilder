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
	    }
	}).bind('fileuploaddone', function(e, data){
		tagme_reload();
		jQuery('#uploadprogress').parent().slideUp();
	});
	tagme_reload();
	jQuery(document).on('change', '#innslag_selector', function(){tagme_list_selector()});
});



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