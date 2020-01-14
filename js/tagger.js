UKMbilder = UKMbilder || {};

UKMbilder.tagger = function($) {
    var emitter = UKMresources.emitter('tagger');

    var tagQueue = [];
    var currentIndex = 0;
    var saving = false;

    var self = {
        init: function() {
            self.bind();

            $(document).ready(function() {
                $('#hendelseSelector').on('change', function(event) {
                    self.renderInnslagListe($(this).val());
                });
                $('#nextImage').on('click', self.nextImage);
                $('#prevImage').on('click', self.prevImage);
                $('#doTag').on('click', self.applyTag);
                $('#doTrash').on('click', self.doTrash);

                if ("nonTaggedImages" in window && nonTaggedImages) {
                    tagQueue = nonTaggedImages;
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
            tagQueue.push(imageData);
            self.updateTagView();
        },
        nextImage: function() {
            if (saving) {
                alert('Kan ikke bla til neste bilde mens lagring pågår');
                return false;
            }
            if (currentIndex + 1 < tagQueue.length) {
                currentIndex++;
            }
            self.updateTagView();
        },
        prevImage: function() {
            if (saving) {
                alert('Kan ikke bla til forrige bilde mens lagring pågår');
                return false;
            }
            if (currentIndex > 0) {
                currentIndex--;
            }
            self.updateTagView();
        },
        doTrash: function(e) {
            e.preventDefault();
            var sure = confirm('Er du sikker på at du vil slette dette bildet?');
            if (sure) {
                console.log('Slett skiten');
                console.log(tagQueue[currentIndex]);
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        'action': 'UKMbilder_ajax',
                        'controller': 'slettBilde',
                        'bildeId': tagQueue[currentIndex].imageId
                    },
                    success: self.deletedImage,
                    error: self.deletedImageFailed
                });
            }
        },
        deletedImage: function(data, xhr, res) {
            if (res.success) {
                $('#tagWindowInnslagListe').html(data.innslagInputs);
                tagQueue.splice(currentIndex, 1);
                return self.updateTagView();
            }
            self.deletedImage(data, xhr, res);
        },
        deletedImageFailed: function(data, xhr, res) {
            alert('Beklager, klarte ikke å slette bildet');
        },
        updateTagView: function() {
            if (currentIndex < 0 || tagQueue.length < currentIndex) return;
            var currentImage = tagQueue[currentIndex];
            if (!currentImage) {
                $('#noneToTag').slideDown();
                $('#tagger').slideUp();
                return;
            } else {
                $('#noneToTag').slideUp();
                $('#tagger').slideDown();
            }

            // TODO: optimize queries to use $('tagger').find(), redusing raw data parsed by selector
            $('#current').text(currentIndex + 1);
            $('#tagQueueCount').text(tagQueue.length);

            $('#current_name').text(currentImage.originalFilename);


            $('#prevImage').prop('disabled', currentIndex <= 0);
            $('#nextImage').prop('disabled', currentIndex >= tagQueue.length - 1);
            $('#tagWindowImage').attr('src', currentImage.imageUrl);
            if (currentImage.storedTag) {
                // $('#hendelseSelector').val(currentImage.);

                // $('#fotografSelector[value=86]').prop('selected', true);

                $('#fotografSelector').val(currentImage.storedTag.fotografId);
                $('#tagWindowInnslagListe').find('input[value="' + currentImage.storedTag.innslagId + '"]').attr('checked', true);

            }



        },
        renderInnslagListe(hendelseId) {
            $.ajax({
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
        saveTag: function(tagData) {
            self.saving();
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'UKMbilder_ajax',
                    controller: 'tagger',
                    tagData: tagData
                },
                success: self.tagSuccessFunc,
                error: self.tagErrorFunc
            });
        },
        tagSuccessFunc: function(data, xhr, res) {
            tagQueue[currentIndex].storedTag = data.storedTag;
            self.doneSaving();
            self.nextImage();
        },
        tagErrorFunc: function(data, xhr, res) {
            alert("Ukjent feil oppsto");
            self.doneSaving();
        },
        saving: function() {
            saving = true;
            $('#doTag').text('Lagrer...').attr('disabled', true);
        },
        doneSaving: function() {
            $('#doTag').text('Lagre').attr('disabled', false).removeAttr('disabled');
            saving = false;
        },
        applyTag: function() {
            var currentImage = tagQueue[currentIndex];

            var tagData = {
                innslagId: $('#tagWindow input[name=bildeTaggerInnslag]:checked').val(),
                imageId: currentImage.imageId,
                fotografId: $('#fotografSelector').val(),
                hendelseId: $('#hendelseSelector').val()
            };

            if (tagData.innslagId && tagData.imageId && tagData.fotografId) {
                self.saveTag(tagData);
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