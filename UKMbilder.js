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
		jQuery('#uploadprogress').parent().slideUp();
		console.warn('På tide å oppdatere listen over opplastede bilder');
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
	jQuery.post(ajaxurl,
				{action: 'UKMbilder_tagme'},
				function(response){
					tagme_response(response);
				});
}

function tagme_response( response ) {
	console.log('TagMe');
	console.log( response );
	var template_tagme = Handlebars.compile(jQuery('#handlebars-image-tag').html());
	jQuery('#tag_images').html( template_tagme( response ) );
}