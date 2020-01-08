UKMbilder = UKMbilder || {};

UKMbilder.converter = function($) {
    var emitter = UKMresources.emitter('converter');

    var self = {
        init: function() {
            //TODO: add pageload-retrieval of non-converted files
            $(document).ready(function() {
                jQuery('#convertQueue ol li').each(function(el) {
                    console.log( jQuery(this).data('image-id') );
                });
            });

            self.bind();
        },
        bind: function() {
            UKMbilder.uploader.on('uploaded', self.receive);
        },
        receive: function(imageData) {
            console.log('recieved', imageData);
            var convertQueueList = $('#convertQueue ol');
            convertQueueList.append(`<li class="list-group-item" data-image-id=${imageData.id}>${imageData.originalFilename}</li>`);
        }
    };

    return self;
}(jQuery);