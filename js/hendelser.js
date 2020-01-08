UKMbilder.hendelser = function($) {
    var emitter = UKMresources.emitter('hendelser');

    var self = {
        bind: function() {},
        on: function(event, callback) {
            emitter.on(event, callback);
        },
        once: function(event, callback) {
            emitter.once(event, callback);
        },
    }

    return self;
}(jQuery);