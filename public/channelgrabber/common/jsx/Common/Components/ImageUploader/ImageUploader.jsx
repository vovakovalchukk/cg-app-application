define([
    'react'
], function(
    React
) {
    "use strict";

    var ImageUploaderComponent = React.createClass({
        getDefaultProps: function() {
            return {
                uploadImageHandler:null
            };
        },
        onUploadButtonClick: function(e) {
            e.target.value = null;
        },

        render: function() {
            return (
                <div>

                    <input type="file" onChange={this.props.uploadImageHandler} onClick={this.onUploadButtonClick} name="fileupload"
                           id="fileupload"/>

                </div>
            );

        }
    });

    return ImageUploaderComponent;
});