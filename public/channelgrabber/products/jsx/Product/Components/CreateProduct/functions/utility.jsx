define([], function() {
    "use strict";
    var utility = {
        optionExistsAlready: function(optionToLookFor, options) {
            return options.find(option => {
                if (optionToLookFor.value == option.value) {
                    return true;
                }
            })
        },
        getUploadedImageById: function(id, uploadedImages) {
            return uploadedImages.find(function(image) {
                if (image.id == id) {
                    return image;
                }
            })
        },
        isEmptyObject: function(obj) {
            for (var key in obj) {
                if (obj.hasOwnProperty(key)) {
                    return false;
                }
            }
            return true;
        }
    };

    return utility;
});