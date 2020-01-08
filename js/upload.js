UKMbilder.uploader = function($) {
    var emitter = UKMresources.emitter('uploader');

    var self = {
        on: function(event, callback) {
            emitter.on(event, callback);
        },
        once: function(event, callback) {
            emitter.once(event, callback);
        }
    }

    return self;
}(jQuery);