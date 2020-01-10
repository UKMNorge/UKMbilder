UKMbilder = UKMbilder || {};

UKMbilder.imageList = function($) {

    var self = {
        init: function () {
            jQuery(document).ready(function() {
                jQuery('.visBilder').on('click', self.getByClick);
                jQuery(document).on('click', 'a.endreInnslag', function(e){
                    e.preventDefault();
                    var wrapper = $( this ).closest('.listImageEditor');
                    wrapper.find('.endreInnslagFelt').show();
                    wrapper.find('.lagreBildeInfo').show();
                });
                jQuery(document).on('click', 'a.endreFotograf', function(e){
                    e.preventDefault();
                    var wrapper = $( this ).closest('.listImageEditor');
                    wrapper.find('.endreFotografFelt').show();
                    wrapper.find('.lagreBildeInfo').show();
                });
                jQuery(document).on('click', 'button.lagreBildeInfo', function(e){
                    e.preventDefault();
                    var wrapper = $( this ).closest('.listImageEditor');
                    var fotografId = wrapper.find('.endreFotografFelt select').val();
                    var innslagId = wrapper.find('.endreInnslagFelt select').val();
                    var bildeId = wrapper.data('bilde-id');
                    var oldInnslagId = wrapper.data('innslag-id');

                    self.updateBilde( {
                        innslagId: innslagId,
                        imageId: bildeId,
                        fotografId: fotografId,
                        oldInnslagId: oldInnslagId
                    }, wrapper);
                });
            });

        },
        updateBilde: function( inData, wrapper ) {
            console.log('save data', inData);
            UKMbilder.tagger.saveTag(inData, 
                function(data, xhr, response) {
                    console.log(inData.oldInnslagId, inData.innslagId, wrapper);
                    if (inData.oldInnslagId !== inData.innslagId) wrapper.remove();
                }, 
                function(data, xhr, response) {

                }
            );

            //TODO: implement bildeListeSave.ajax.php
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
                }
            });
        }
    };
    return self;

}(jQuery);