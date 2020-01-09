UKMbilder = UKMbilder || {};

UKMbilder.tagger = function($) {
    var emitter = UKMresources.emitter('tagger');

    var self = {
        init: function() {
            self.bind();

            $(document).ready(function() {
                jQuery('#hendelseSelector').on('change', function(event) {
                    var value = $(this).val();
                    // debugger;

                });
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
            $('#tagWindowImage').attr('src', imageData.imageUrl);
        },
        applyTag: function() {

        }
    };

    return self;
}(jQuery);