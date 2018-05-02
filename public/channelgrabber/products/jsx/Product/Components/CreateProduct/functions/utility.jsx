define([], function() {
    "use strict";
    var utility = {

        optionExistsAlready: function(option, options) {
            for (var i = 0; i < options.length; i++) {
                if (option.value == options[i].value) {
                    return true;
                }
            }
        },

        getUploadedImageById: function(id, uploadedImages) {
            for (var i = 0; i < uploadedImages.length; i++) {
                if (uploadedImages[i].id == id) {
                    return uploadedImages[i];
                }
            }
        }

    }

    return utility;

});