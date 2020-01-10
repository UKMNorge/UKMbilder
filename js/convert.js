UKMbilder = UKMbilder || {};

UKMbilder.converter = function($) {
    var emitter = UKMresources.emitter('converter');


    var self = {
        convertQueue: [],
        isRunning: false,
        init: function() {
            jQuery('#convertQueue ol li').each(function(el) {

                var dataImageId = jQuery(this).data('image-id');
                console.log( 'parsed', dataImageId );
                self.convertQueue.push( dataImageId );

            });
            self.bind();
            self.convert();

        },
        bind: function() {
            UKMbilder.uploader.on('uploaded', self.receive);
        },
        on: function(event, callback) {
            emitter.on(event, callback);
        },
        once: function(event, callback) {
            emitter.once(event, callback);
        },
        receive: function(imageData) {
            console.log('recieved', imageData);
            var convertQueueList = $('#convertQueue ol');
            convertQueueList.append(`<li class="list-group-item" data-image-id=${imageData.id}>${imageData.originalFilename}</li>`);

            self.convertQueue.push(imageData.id);
            if (!self.isRunning) self.convert();
        },

        /**
         * Does actual conversion
         * 
         * @param {function} callback 
         */
        convert: function() {
            if ( self.convertQueue.length > 0 ){
                self.isRunning = true;
                var imageId = self.convertQueue[0];
                self.convertQueue = self.convertQueue.splice(1);
                jQuery.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        'action': 'UKMbilder_ajax',
                        'controller': 'convert',
                        'imageId': imageId
                    },
                    success: function (data, xhr, res) {
                        // debugger;
                        var convertQueueElement = $('#convertQueue ol').find(`[data-image-id='${imageId}']`);
                        convertQueueElement.remove();
                        self.convert();
                        /**
                         * imagedata: {
                         *      imageUrl,
                         *      imageId,
                         *      originalFilename
                         * }
                         */
                        emitter.emit('converted', data.imageData );
                    },
                    error: function() {
                        //TODO: handle errors
                    }
                });
            } else {
                self.isRunning = false;
            }
            return;

        },
    };

    return self;
}(jQuery);