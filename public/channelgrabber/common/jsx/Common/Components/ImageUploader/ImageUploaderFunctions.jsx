define([
    'react'
], function(
    React
) {

    var imageUploaderFunctions =  (function(){

        var self = {
            uploadImageHandler: function(event) {
                return new Promise(function(resolve, reject) {
                    var file = event.target.files[0];
                    if (!file) return;
                    self.getImageBinaryFromFile(file)
                        .then(self.uploadFile)
                        .then(function(response) {
                            resolve({
                                response: response,
                                imageRequestedForUpload: file
                            })
                        })
                        .catch(function(response) {
                            console.trace();
                            reject({
                                response: response,
                                imageRequestedForUpload: file
                            })
                        });
                });
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
                    data: bas64EncodedData,
                    type: 'POST',
                    context: this
                })
            }
        };

        return self;

    }());

    return imageUploaderFunctions;

});