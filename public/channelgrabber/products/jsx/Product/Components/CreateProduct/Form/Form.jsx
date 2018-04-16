define([
    'react',
    'redux-form',
    'Common/Components/ImageUploader/ImageUploaderRoot',
    'Common/Components/ImagePicker'
], function(
    React,
    reduxForm,
    ImageUploader,
    ImagePicker
) {
    var Field = reduxForm.Field;
    var Form = reduxForm.Form;

    console.log('in connected Form');

    var createFormComponent = React.createClass({
        getDefaultProps: function() {
            return {
                handleSubmit: null,
                addImage: null,
                uploadedImages: {}
            };
        },

        render: function() {

            var uploadedImages = this.props.uploadedImages.images;

            return (
                <Form id="create-product-form" onSubmit={this.props.handleSubmit}>

                        <label>
                            <span className={"inputbox-label"}>New Product Name:</span>
                            <div className={"order-inputbox-holder"}>
                                <Field type="text" name="title" component="input"/>
                            </div>
                        </label>




                        <div className={"form-row"}>
                            <label className={"form-row__label-column"}>Main Image:</label>
                            <div className={"form-row__input-column"}>
                                <ImagePicker
                                    images={
                                        uploadedImages
                                    }
                                    onImageSelected={this.props.onChange}
                                />
                                <ImageUploader/>
                            </div>
                        </div>




                </Form>
            );
        }
    });

    return reduxForm.reduxForm({
        form: 'createProductForm'
    })(createFormComponent);
});
