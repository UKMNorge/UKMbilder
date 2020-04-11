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

            jQuery(document).on('click', '.convert-queue-remove', self.remove);

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
            var data = {
                image: imageData
            }
            convertQueueList.append(twigJS_konverteringsListeElement.render(data));
            //convertQueueList.append(`<li class="list-group-item" data-image-id=${imageData.id}>${imageData.originalFilename}<a href="#" class="convert-queue-remove pull-right"><span class="dashicons dashicons-trash"></span></a></li>`);

            convertQueue.push(imageData.id);
            if (!isRunning) {
                self.convert();
            }
        },
        remove: function(event) {
            var convertId = jQuery(event.target).closest(".list-group-item").attr('data-image-id')
            var convertQueueElement = jQuery(event.target).closest(".list-group-item");

            // Mark as crashed in DB
            jQuery.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        'action': 'UKMbilder_ajax',
                        'controller': 'cancelConvert',
                        'imageId': convertId
                    },
                    success: function(data, xhr, res) {
                        // Remove visually as well
                        if(data.success == false) {
                            convertQueueElement.append('<span class="label label-danger">Feil: '+data.message+'</span>');
                            return;
                        }
                        convertQueueElement.remove();
                        convertQueue.splice($.inArray(convertId, convertQueue), 1);
                        
                        if (!isRunning) {
                            self.convert();
                        }
                    },
                    error: function() {
                        convertQueueElement.append('<span class="label label-danger">Feil: '+data.message+'</span>');
                        return;
                    }
                });

            
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
                        if(data.success == false) {
                            convertQueueElement.append('<span class="label label-danger">Feil: '+data.message+'</span>');
                            return;
                        }
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