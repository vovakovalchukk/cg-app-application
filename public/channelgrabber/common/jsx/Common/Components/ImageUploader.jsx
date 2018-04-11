define([
    'react',
    'element/FileUpload'
], function(
    React,
    FileUpload
) {
    "use strict";

    var ImageUploaderComponent = React.createClass({
        getDefaultProps: function() {
            return {
                input: {
                    onChange: null
                }
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
                        binaryData: data
                    });
                }
            })
        },
        passDataChangeToContainerComponent: function(data) {
            this.props.input.onChange(data);
        },
        onFileChange: function(e) {
            var file = e.target.files[0];
            if (!file) return;
            this.getImageBinaryFromFile(file).then(
                this.passDataChangeToContainerComponent
            );
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