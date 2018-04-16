define([
    'react',
    'redux-form',
    'Common/Components/ImageUploader/ImageUploaderRoot',
    'Common/Components/ImagePicker',
    'Common/Components/FormRow'
], function(
    React,
    reduxForm,
    ImageUploader,
    ImagePicker,
    FormRow
) {
    var Field = reduxForm.Field;
    var Form = reduxForm.Form;

    var inputColumnRenderMethods = {
        newProductName: function() {
            return (
                <Field type="text" name="title" component="input"/>
            )
        },
        mainImage: function() {
            var uploadedImages = this.props.uploadedImages.images;
            return (
                <div>
                    <Field type="text" name="title" component={function(props) {
                        return <ImagePicker
                            images={
                                uploadedImages
                            }
                            onImageSelected={props.onChange}
                        />;
                    }}/>
                    <ImageUploader/>
                </div>
            );
        }
    };

    var createFormComponent = React.createClass({
        getDefaultProps: function() {
            return {
                handleSubmit: null,
                addImage: null,
                uploadedImages: {}
            };
        },
        render: function() {
            return (
                <Form id="create-product-form" onSubmit={this.props.handleSubmit}>
                    <FormRow
                        label={'Main Image'}
                        inputColumnContent={inputColumnRenderMethods.newProductName.call(this)}
                    />
                    <FormRow
                        label={'Main Image'}
                        inputColumnContent={inputColumnRenderMethods.mainImage.call(this)}
                    />
                </Form>
            );
        }
    });

    return reduxForm.reduxForm({
        form: 'createProductForm'
    })(createFormComponent);
});
