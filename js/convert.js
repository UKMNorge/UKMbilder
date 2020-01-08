UKMbilder.converter = function($) {
    var emitter = UKMresources.emitter('converter');

    var self = {
        init: function() {},
        bind: function() {
            UKMbilder.uploader.on('uploaded', self.receive);
        }
    }

    return self;
}(jQuery);