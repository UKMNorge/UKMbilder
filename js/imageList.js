UKMbilder = UKMbilder || {};

UKMbilder.imageList = function($) {

    var self = {
        init: function () {
            jQuery(document).ready(function() {
                jQuery('.visBilder').on('click', self.getByClick);
                jQuery(document).on('click', '.saveImageChanges', function(){


                    $(this).data('innslags-id');
                });
            });

        },
        getByClick(event) {
            event.preventDefault();
            var innslagId = jQuery(this).data('innslag-id');
            console.log('INNSLAG ID', innslagId, jQuery(this).data());


            jQuery.ajax({
                url: ajaxurl,
                method: 'GET',
                data: {
                    'action': 'UKMbilder_ajax',
                    'controller': 'bildeListe',
                    'innslagId': innslagId
                },
                success: function(data, xhr, res) {
                    console.log(data, xhr, res);
                    var container = jQuery('.bildeContainer[data-innslag-id=' + innslagId + ']');
                    container.show();
                    container.html(data.bilderHtml);

                    // TODO: attach working eventlistener for saving photographer/innslag-info

                }
            });
        }
    };
    return self;

}(jQuery);