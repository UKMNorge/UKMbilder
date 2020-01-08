UKMbilder = UKMbilder || {};

UKMbilder.converter = function($) {
    var emitter = UKMresources.emitter('converter');

    var self = {
        init: function() {
            //TODO: add pageload-retrieval of non-converted files

            self.bind();
        },
        bind: function() {
            UKMbilder.uploader.on('uploaded', self.receive);
        },
        receive: function(imageData) {
            console.log('recieved', imageData);
            var convertQueueList = $('#convertQueue ol');
            convertQueueList.append(`<li class="list-group-item">${imageData.originalFilename}</li>`);
        }
    };

    return self;
}(jQuery);