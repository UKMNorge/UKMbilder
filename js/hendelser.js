UKMbilder.hendelser = function($) { // TODO: concider deleting this file, as it is not used
    var emitter = UKMresources.emitter('hendelser');

    var self = {
        init: function() {},
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