UKMbilder = UKMbilder || {};

UKMbilder.imageList = function($) {

    var self = {
        init: function() {
            jQuery(document).ready(function() {
                jQuery('.visBilder').on('click', self.getByClick);
                jQuery(document).on('click', 'a.avbrytLagreBildeInfo', self.avbryt);
                jQuery(document).on('click', 'a.endreInnslag', self.flytt.show);
                jQuery(document).on('click', 'a.endreFotograf', self.fotograf.show);
                jQuery(document).on('click', 'a.slettBilde', self.slett);
                jQuery(document).on('click', '.submitChanges', self.endre.save);

            });

        },
        slett: function(e) {
            e.preventDefault();
            alert('Beklager, kunne ikke slette bilde');
        },
        getInnslag: function(e) {
            return $(e.target).parents('.listImageEditor');
        },
        avbryt: function(e) {
            e.preventDefault();
            self.endre.hide(e);
        },
        flytt: {
            getElement: function(e) {
                return self.getInnslag(e).find('.endreInnslagFelt');
            },
            hide: function(e) {
                self.flytt.getElement(e).slideUp();
            },
            show: function(e) {
                e.preventDefault();
                self.endre.show(e);
                self.flytt.getElement(e).slideDown();
            }
        },
        fotograf: {
            getElement: function(e) {
                return self.getInnslag(e).find('.endreFotografFelt');
            },
            hide: function(e) {
                self.fotograf.getElement(e).slideUp();
            },
            show: function(e) {
                e.preventDefault();
                self.endre.show(e);
                self.fotograf.getElement(e).slideDown();
            }
        },
        endre: {
            getElement: function(e) {
                return self.getInnslag(e).find('.lagreBildeInfo');
            },
            hide: function(e) {
                self.endre.getElement(e).hide();
                self.fotograf.hide(e);
                self.flytt.hide(e);
            },
            show: function(e) {
                self.endre.getElement(e).show();
            },
            save: function(e) {
                e.preventDefault();
                var fotografId = self.getInnslag(e).find('.endreFotografFelt select').val();
                var innslagId = self.getInnslag(e).find('.endreInnslagFelt select').val();
                var bildeId = self.getInnslag(e).data('bilde-id');
                var oldInnslagId = self.getInnslag(e).data('innslag-id');

                self.updateBilde({
                    innslagId: innslagId,
                    imageId: bildeId,
                    fotografId: fotografId,
                    oldInnslagId: oldInnslagId
                }, wrapper);
            }
        },
        updateBilde: function(inData, wrapper) {
            UKMbilder.tagger.saveTag(inData,
                function(data, xhr, response) {
                    if (inData.oldInnslagId !== inData.innslagId) wrapper.remove();
                },
                function(data, xhr, response) {
                    alert("Ukjent feil oppsto");
                }
            );
        },
        getByClick(event) {
            event.preventDefault();
            var innslagId = jQuery(this).data('innslag-id');
            jQuery.ajax({
                url: ajaxurl,
                method: 'GET',
                data: {
                    'action': 'UKMbilder_ajax',
                    'controller': 'bildeListe',
                    'innslagId': innslagId
                },
                success: function(data, xhr, res) {
                    var container = jQuery('.bildeContainer[data-innslag-id=' + innslagId + ']');
                    container.show();
                    container.html(data.bilderHtml);
                }
            });
        }
    };
    return self;

}(jQuery);