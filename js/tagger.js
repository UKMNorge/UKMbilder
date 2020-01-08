UKMbilder.tagger = function($) {
    var emitter = UKMresources.emitter('tagger');

    var self = {
        init: function() {},
        bind: function() {
            UKMbilder.uploader.on('converted', self.receive);
        },
        on: function(event, callback) {
            emitter.on(event, callback);
        },
        once: function(event, callback) {
            emitter.once(event, callback);
        }
    }

    return self;
}(jQuery);