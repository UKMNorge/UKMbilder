var UKMbilder = function($) {

    var self = {
        init: function() {}
    }

    return self;
}(jQuery);


jQuery(document).ready(function() {
    UKMbilder.uploader.init();
    UKMbilder.converter.init();
    UKMbilder.tagger.init();
});