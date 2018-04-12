define([
    'react'
], function(
    React
) {
    "use strict";

    var ImageUploaderComponent = React.createClass({
        getDefaultProps: function() {
            return {
                onImageUploadStart: null,
                onImageUploadSuccess: null,
                onImageUploadFailure: null
            };
        },
        onUploadButtonClick: function(e) {
            e.target.value = null;
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
            this.props.onImageUploadStart();
            return $.ajax({
                url: '/products/create/imageUpload',
                data: bas64EncodedData,
                type: 'POST',
                context: this
            })
        },
        uploadFileResponseHandler: function(file, response) {
            if (response.valid) {
                this.props.onImageUploadSuccess(response);
            } else {
                this.props.onImageUploadFailure(response);
            }
        },
        onFileChange: function(e) {
            var file = e.target.files[0];
            if (!file) return;
            this.getImageBinaryFromFile(file)
                .then(this.uploadFile)
                .then(this.uploadFileResponseHandler.bind(this, file))
        },
        render: function() {
            return (
                <div>

                    <input type="file" onChange={this.onFileChange} onClick={this.onUploadButtonClick} name="fileupload"
                           id="fileupload"/>

                </div>
            );

        }
    });

    return ImageUploaderComponent;
});