UKMbilder = UKMbilder || {};

UKMbilder.uploader = function($) {
    var emitter = UKMresources.emitter('uploader');

    var self = {
        init: function() {

            var myDropzone = new Dropzone('#bildeOpplaster', { 
                url: ajaxurl ,
                method: 'POST',
                timeout: 30 * 1000,
                acceptedFiles: 'image/*' ,
                parallelUploads: 1,
                sending: function(file, xhr, formData) {
                    formData.append('action', 'UKMbilder_ajax');
                    formData.append('controller', 'upload');
                },

                success: function(file, xhrData, progress) {
                    if (!xhrData.imageData || !Array.isArray(xhrData.imageData)) {} // TODO: Erorrhandling for missing image data
                    xhrData.imageData.forEach(function( imageData ) {
                        emitter.emit('uploaded', imageData  );
                    });
                },
                complete: function(file) {
                    myDropzone.removeFile(file);
                }
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