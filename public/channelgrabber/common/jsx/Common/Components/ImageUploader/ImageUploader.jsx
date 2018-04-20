define([
    'react'
], function(
    React
) {
    "use strict";

    var ImageUploaderComponent = React.createClass({
        getDefaultProps: function() {
            return {
<<<<<<< HEAD
                uploadImageHandler: null,
                className: ''
=======
                uploadImageHandler: null
>>>>>>> LIS-189-image-upload-on-product-creation-form
            };
        },
        onUploadButtonClick: function(e) {
            e.target.value = null;
        },
        render: function() {
            return (
                <div>

<<<<<<< HEAD
                    <input type="file" onChange={this.props.uploadImageHandler} onClick={this.onUploadButtonClick}
                           name="fileupload" className={this.props.className}
                           id="fileupload"/>
=======
                    <input
                        type="file"
                        onChange={this.props.uploadImageHandler}
                        onClick={this.onUploadButtonClick}
                        name="fileupload"
                        id="fileupload"
                    />
>>>>>>> LIS-189-image-upload-on-product-creation-form

                </div>
            );
        }
    });

    return ImageUploaderComponent;
});