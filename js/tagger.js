UKMbilder = UKMbilder || {};

UKMbilder.tagger = function($) {
    var emitter = UKMresources.emitter('tagger');


    
    var self = {
        tagQueue: [],
        currentIndex: 0,
        init: function() {
            self.bind();

            $(document).ready(function() {
                jQuery('#hendelseSelector').on('change', function(event) {
                    self.renderInnslagListe( $(this).val() );
                });
                jQuery('#nextImage').on('click', self.nextImage);
                jQuery('#prevImage').on('click', self.prevImage);
                jQuery('#doTag').on('click', self.applyTag);

                if (nonTaggedImages) {
                    self.tagQueue = nonTaggedImages;
                    self.updateTagView();
                }

            });
        },
        bind: function() {
            UKMbilder.converter.on('converted', self.receive);
        },
        on: function(event, callback) {
            emitter.on(event, callback);
        },
        once: function(event, callback) {
            emitter.once(event, callback);
        },
        receive: function(imageData) {
            console.log('Tagger recived', imageData);
            if (!imageData) return;
            self.tagQueue.push( imageData );
            self.updateTagView();
        },
        nextImage: function() {
            self.updateTagView(++self.currentIndex);
        },
        prevImage: function() {
            console.log('prevImg', self.currentIndex);
            self.currentIndex--;
            self.updateTagView(self.currentIndex);
        },

        updateTagView: function(index) {
            index = index || self.currentIndex || 0;
            if (index < 0 || self.tagQueue.length < index) return;

            console.log('updateTagView', index, self.tagQueue);

            jQuery('#current').text( index+1 );
            jQuery('#tagQueueCount').text( self.tagQueue.length );

            jQuery('#prevImage').prop('disabled', index <= 0 ); 
            jQuery('#nextImage').prop('disabled', index >= self.tagQueue.length - 1 );
            jQuery('#tagWindowImage').attr('src', self.tagQueue[index].imageUrl );
        },
        renderInnslagListe(hendelseId) {
            jQuery.ajax({
                url: ajaxurl,
                method: 'GET',
                data: {
                    'action': 'UKMbilder_ajax',
                    'controller': 'innslagListe',
                    'hendelseId': hendelseId
                },
                success: function(data, xhr, res) {
                    $('#tagWindowInnslagListe').html(data.innslagInputs);
                }

            });
        },
        applyTag: function() {
            var currentImage = self.tagQueue[self.currentIndex];

            var tagData = {
                innslagId: $('#tagWindow input[name=bildeTaggerInnslag]:checked').val(),
                imageId: currentImage.imageId,
                fotografId: jQuery('#fotografSelector').val()
            };


            console.log('data for tag', tagData);
            if ( tagData.innslagId && tagData.imageId && tagData.fotografId ) {
                console.log('GOT ALL DATA');
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'UKMbilder_ajax',
                        controller: 'tagger',
                        tagData: tagData
                    },
                    success: function(data, xhr, res) {
                        
                    },
                    error: function(data, xhr, res) {

                    }

                });

            } else {
                console.log('MISSING DATA');
            } 


        }
    };

    return self;
}(jQuery);