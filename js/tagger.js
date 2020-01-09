UKMbilder = UKMbilder || {};

UKMbilder.tagger = function($) {
    var emitter = UKMresources.emitter('tagger');

    var self = {
        init: function() {
            self.bind();

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
        },
        applyTag: function() {
            
        }
    };

    return self;
}(jQuery);