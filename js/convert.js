UKMbilder = UKMbilder || {};

UKMbilder.converter = function($) {
    var emitter = UKMresources.emitter('converter');

    var convertQueue = [];
    var isRunning = false;

    var self = {
        init: function() {
            jQuery('#convertQueue ol li').each(function(el) {
                var dataImageId = jQuery(this).data('image-id');
                convertQueue.push(dataImageId);
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
            var convertQueueList = $('#convertQueue ol');
            convertQueueList.append(`<li class="list-group-item" data-image-id=${imageData.id}>${imageData.originalFilename}</li>`);

            convertQueue.push(imageData.id);
            if (!isRunning) {
                self.convert();
            }
        },
        hide: function() {
            $('#convertQueue').slideUp();
        },
        show: function() {
            $('#convertQueue').slideDown();
        },

        /**
         * Does actual conversion
         * 
         * @param {function} callback 
         */
        convert: function() {
            if (convertQueue.length > 0) {
                self.show();
                isRunning = true;
                var imageId = convertQueue[0];
                convertQueue = convertQueue.splice(1);
                jQuery.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        'action': 'UKMbilder_ajax',
                        'controller': 'convert',
                        'imageId': imageId
                    },
                    success: function(data, xhr, res) {
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
                        emitter.emit('converted', data.imageData);
                    },
                    error: function() {
                        //TODO: handle errors
                    }
                });
            } else {
                isRunning = false;
                self.hide();
            }
            return;

        },
    };

    return self;
}(jQuery);