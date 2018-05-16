define([
    'react'
], function(
    React
) {
    "use strict";

    var ImageUploaderComponent = React.createClass({
        getDefaultProps: function() {
            return {
                uploadImageHandler: null,
                className: ''
            };
        },
        onUploadButtonClick: function(event) {
            event.target.value = null;
        },
        render: function() {
            return (
                <input type="file"
                       onChange={this.props.uploadImageHandler}
                       onClick={this.onUploadButtonClick}
                       name="fileupload"
                       className={this.props.className}
                       id="fileupload"
                />
            );
        }
    });

    return ImageUploaderComponent;
});