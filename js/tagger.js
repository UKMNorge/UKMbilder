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
                    self.renderInnslagListe($(this).val());
                });
                jQuery('#nextImage').on('click', self.nextImage);
                jQuery('#prevImage').on('click', self.prevImage);
                jQuery('#doTag').on('click', self.applyTag);

                if ("nonTaggedImages" in window && nonTaggedImages) {
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
            if (!imageData) return;
            self.tagQueue.push(imageData);
            self.updateTagView();
        },
        nextImage: function() {
            self.currentIndex = (self.currentIndex + 1 >= self.tagQueue.length ? self.currentIndex : self.currentIndex++);
            self.updateTagView();
        },
        prevImage: function() {
            self.currentIndex = (self.currentIndex - 1 < 0 ? self.currentIndex : self.currentIndex--);
            self.updateTagView(self.currentIndex);
        },

        updateTagView: function(index) {
            index = index || self.currentIndex || 0;
            if (index < 0 || self.tagQueue.length < index) return;

            var currentImage = self.tagQueue[index];
            if (!currentImage) {
                jQuery('#noneToTag').show();
                jQuery('#tagger').hide();
                return;
            } else {
                jQuery('#noneToTag').hide();
                jQuery('#tagger').show();
            }

            // TODO: optimize queries to use jQuery('tagger').find(), redusing raw data parsed by selector
            jQuery('#current').text(index + 1);
            jQuery('#tagQueueCount').text(self.tagQueue.length);

            jQuery('#prevImage').prop('disabled', index <= 0);
            jQuery('#nextImage').prop('disabled', index >= self.tagQueue.length - 1);
            jQuery('#tagWindowImage').attr('src', currentImage.imageUrl);
            if (currentImage.storedTag) {
                // jQuery('#hendelseSelector').val(currentImage.);

                // jQuery('#fotografSelector[value=86]').prop('selected', true);

                jQuery('#fotografSelector').val(currentImage.storedTag.fotografId);
                jQuery('#tagWindowInnslagListe').find('input[value="' + currentImage.storedTag.innslagId + '"]').attr('checked', true);

            }



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
        saveTag: function(tagData, successFunc, errorFunc) {

            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'UKMbilder_ajax',
                    controller: 'tagger',
                    tagData: tagData
                },
                success: successFunc,
                error: errorFunc

            });

        },
        applyTag: function() {
            var currentImage = self.tagQueue[self.currentIndex];

            var tagData = {
                innslagId: $('#tagWindow input[name=bildeTaggerInnslag]:checked').val(),
                imageId: currentImage.imageId,
                fotografId: jQuery('#fotografSelector').val(),
                hendelseId: jQuery('#hendelseSelector').val()
            };

            if (tagData.innslagId && tagData.imageId && tagData.fotografId) {
                self.saveTag(tagData,
                    function(data, xhr, res) { // success function
                        self.tagQueue[self.currentIndex].storedTag = data.storedTag;
                        self.nextImage();
                    },
                    function(data, xhr, res) { // error function
                        alert("Ukjent feil oppsto")
                    }
                );
            } else {
                if (tagData.hendelseId == undefined || tagData.hendelseId == null) {
                    alert('Du må velge innslag og fotograf før du kan lagre. Start med å velge hendelse, så får du opp en liste over innslag.');
                } else {
                    alert('Du må velge innslag og fotograf før du kan lagre.');
                }
            }


        }
    };

    return self;
}(jQuery);