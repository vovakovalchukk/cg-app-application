define([
    'react'
], function(
    React
) {

    var imageUploaderFunctions = (function() {
        var self = {
            uploadImageHandler: function(image) {
                return new Promise(function(resolve, reject) {
                    if (!image) return;
                    self.getImageBinaryFromFile(image)
                        .then(self.uploadFile)
                        .then(function(response) {
                            resolve(response)
                        })
                        .catch(function(response) {
                            reject({
                                response: response,
                                imageRequestedForUpload: image
                            })
                        });
                });
            },
            getImageFromOnChangeEvent: function(event) {
                return event.target.files[0];
            },
            getImageBinaryFromFile: function(file) {
                return new Promise(function(resolve) {
                    var reader = new FileReader();
                    reader.readAsDataURL(file)
                    reader.onloadend = function(event) {
                        var dataUrl = event.target.result;
                        var encodedData = dataUrl.split(',')[1];
                        var data = atob(encodedData);
                        resolve({
                            filename: file.name,
                            size: file.size,
                            type: file.type,
                            binaryDataString: data
                        });
                    }
                })
            },
            uploadFile: function(file) {
                var bas64EncodedData = btoa(file.binaryDataString);
                return $.ajax({
                    url: '/products/create/imageUpload',
                    data: {
                        filename: file.filename,
                        image: bas64EncodedData
                    },
                    filename: file.filename,
                    type: 'POST',
                    context: this
                })
            }
        };
        return self;
    }());

    return imageUploaderFunctions;
});