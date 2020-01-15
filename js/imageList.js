UKMbilder = UKMbilder || {};

UKMbilder.imageList = function($) {

    var self = {
        init: function() {
            jQuery(document).ready(function() {
                jQuery('.visBilder').on('click', self.getByClick);
                jQuery(document).on('click', 'a.visBildeLink', self.clickClickButton);
                jQuery(document).on('click', 'a.avbrytLagreBildeInfo', self.avbryt);
                jQuery(document).on('click', 'a.endreInnslag', self.flytt.show);
                jQuery(document).on('click', 'a.endreFotograf', self.fotograf.show);
                jQuery(document).on('click', 'a.slettBilde', self.slett);
                jQuery(document).on('click', '.submitChanges', self.endre.save);

            });

        },
        clickClickButton: function(e) {
            console.log('clickClickButton');
            e.preventDefault();
            $(this).parents('li.innslag').find('a.visBilder').click();
        },
        slett: function(e) {
            e.preventDefault();
            var sure = confirm('Er du sikker på at du vil slette dette bildet?');
            if (sure) {
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        'action': 'UKMbilder_ajax',
                        'controller': 'slettTaggetBilde',
                        'bildeId': self.getBildeRad(e).data('bilde-id'),
                        'innslagId': self.getBildeRad(e).data('innslag-id')
                    },
                    success: self.deletedImage,
                    error: self.deletedImageFailed
                });
            }
        },
        deletedImage: function(data, xhr, res) {
            if (data.success) {
                // Skjul slettet bilde
                $('#bilde-' + data.POST.innslagId + '-' + data.POST.bildeId).slideUp(
                    function() {
                        $(this).remove();
                    }
                );
                return true;
            }
            self.deletedImageFailed(data, xhr, res);
        },
        deletedImageFailed: function(data, xhr, res) {
            if (data.message) {
                alert(data.message);
            } else {
                alert('Beklager, klarte ikke å slette bildet.');
            }
        },
        getBildeRad: function(e) {
            return $(e.target).parents('.listImageEditor');
        },
        avbryt: function(e) {
            e.preventDefault();
            self.endre.hide(e);
        },
        flytt: {
            getElement: function(e) {
                return self.getBildeRad(e).find('.endreInnslagFelt');
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
                return self.getBildeRad(e).find('.endreFotografFelt');
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
                return self.getBildeRad(e).find('.lagreBildeInfo');
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
                var fotografId = self.getBildeRad(e).find('.endreFotografFelt select').val();
                var innslagId = self.getBildeRad(e).find('.endreInnslagFelt select').val();
                var bildeId = self.getBildeRad(e).data('bilde-id');
                var oldInnslagId = self.getBildeRad(e).data('innslag-id');

                self.updateBilde({
                    innslagId: innslagId,
                    imageId: bildeId,
                    fotografId: fotografId,
                    oldInnslagId: oldInnslagId
                }, self.endre.getElement(e));
            }
        },
        updateBilde: function(inData, wrapper) {
            UKMbilder.tagger.saveTag(inData,
                function(data, xhr, response) {
                    // Hvis bildet er fjernet, skjul det
                    if (data.POST.tagData.oldInnslagId != data.storedTag.innslagId) {
                        // Skjul mottakerbilder (slik at denne listen ikke lenger står oppe og viser feil bilder)
                        mottakerInnslag = $('li.innslag[data-innslag-id=' + data.storedTag.innslagId + ']');
                        if (mottakerInnslag.find('.bildeContainer').is(':visible')) {
                            mottakerInnslag.find('.visBilder').click();
                        }
                        // Skjul flyttet bilde
                        wrapper.parents('.listImageEditor').slideUp(
                            function() {
                                $(this).remove();
                            }
                        );
                    }
                    wrapper.parents('.listImageEditor').find('.avbrytLagreBildeInfo').click();
                },
                function(data, xhr, response) {
                    alert("Ukjent feil oppsto");
                }
            );
        },
        getByClick(event) {
            event.preventDefault();
            var innslagId = $(this).data('innslag-id');
            jQuery.ajax({
                url: ajaxurl,
                method: 'GET',
                data: {
                    'action': 'UKMbilder_ajax',
                    'controller': 'bildeListe',
                    'innslagId': innslagId
                },
                success: function(data, xhr, res) {
                    $('.bildeContainer[data-innslag-id=' + innslagId + ']').html(data.bilderHtml);
                }
            });
        }
    };
    return self;

}(jQuery);