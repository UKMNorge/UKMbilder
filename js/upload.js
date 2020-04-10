UKMbilder = UKMbilder || {};

UKMbilder.uploader = function($) {
    var emitter = UKMresources.emitter('uploader');

    var self = {
        init: function() {

            if (jQuery('#bildeOpplaster').length === 0) return;

            // Håndter klikk også på tekst
            jQuery(document).ready(function() {
                jQuery("#bildeOpplaster").click(function(event) {
                    if(event.target.tagName != "DIV") {
                        jQuery(event.target).closest("div").click();    
                    }
                });
            });

            var myDropzone = new Dropzone('#bildeOpplaster', {
                url: ajaxurl,
                method: 'POST',
                timeout: 30 * 1000,
                acceptedFiles: 'image/*',
                parallelUploads: 1,
                previewTemplate: jQuery('#bildeOpplasterPreviewTemplate').html(),
                sending: function(file, xhr, formData) {
                    formData.append('action', 'UKMbilder_ajax');
                    formData.append('controller', 'upload');
                },

                success: function(file, xhrData, progress) {
                    if (!xhrData.imageData || !Array.isArray(xhrData.imageData)) {} // TODO: Error handling for missing image data
                    emitter.emit('uploaded', xhrData.imageData);
                    myDropzone.removeFile(file);
                },
                error: function(file, data, xhr) {

                    var originalFilename = file.upload.filename;
                    var errorMsg = 'Feil oppsto på ' + originalFilename;
                    $(file.previewElement).find('.dz-error-message').text(errorMsg); // Should work when Dropzone CSS is loaded
                    // $('#noneToTag').text(errorMsg).style('color', 'red'); // Temp solution until Dropzone CSS


                },
                complete: function(file) {}
            });

        },
        on: function(event, callback) {
            emitter.on(event, callback);
        },
        once: function(event, callback) {
            emitter.once(event, callback);
        }
    };

    return self;
}(jQuery);