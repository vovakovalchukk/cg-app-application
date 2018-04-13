define([
    'redux',
    'react-redux',
    'Common/Components/ImageUploader/ImageUploader',
    'Common/Components/ImageUploader/ImageUploaderActions'
//
], function(
    Redux,
    ReactRedux,
    ImageUploader,
    ImageUploaderActions
) {
    "use strict";
    const mapStateToProps = function(state){
        return{
            uploadedImages: state.images
        }
    };
    const mapDispatchToProps = function(dispatch) {
        return Redux.bindActionCreators( ImageUploaderActions, dispatch);
    };

    var FormConnector = ReactRedux.connect(mapStateToProps, mapDispatchToProps);
    return FormConnector(ImageUploader);
});