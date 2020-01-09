UKMbilder = UKMbilder || {};

UKMbilder.tagger = function($) {
    var emitter = UKMresources.emitter('tagger');



    var self = {
        init: function() {
            self.bind();

            $(document).ready(function() {
                jQuery('#hendelseSelector').on('change', function(event) {
                    self.renderInnslagListe( $(this).val() );
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
            var tagContainer = $('#tagWindow');

        }
    };

    return self;
}(jQuery);