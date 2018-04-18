define([
    'react',
    'redux-form',
    'Common/Components/ImageUploader/ImageUploaderRoot',
    'Common/Components/ImagePicker',
    'Common/Components/FormRow',
    'Product/Components/VatView'
], function(
    React,
    reduxForm,
    ImageUploader,
    ImagePicker,
    FormRow,
    VatView
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
                    <Field model="main-image" type="text" name="Main Image" component={function(props) {
                        return (
                            <ImagePicker
                                images={
                                    uploadedImages
                                }
                                onImageSelected={props.input.onChange}
                                multiSelect={false}
                            />
                        );
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
                uploadedImages: {},
                taxRates:null
            };
        },
        render: function() {
            return (
                <Form id="create-product-form" onSubmit={this.props.handleSubmit}>
                    <FormRow
                        label={'New Product Name'}
                        inputColumnContent={inputColumnRenderMethods.newProductName.call(this)}
                    />
                    <FormRow
                        label={'Main Image'}
                        inputColumnContent={inputColumnRenderMethods.mainImage.call(this)}
                    />
                    <Field component={function(props) {
                        return <VatView
                            parentProduct={{
                                taxRates:props.taxRates
                            }}
                            fullView={true}
                            onVatChanged={props.onChange}
                            variationCount={0}
                        />
                    }}/>
                </Form>
            );
        }
    });

    return reduxForm.reduxForm({
        form: 'createProductForm'
    })(createFormComponent);
});
