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
                <Field type="text" name="title" className={'form-row__input'} component="input"/>
            )
        },
        mainImagePickerComponent: function(props){
            var uploadedImages = this.props.uploadedImages.images;
            return (
                <ImagePicker
                    images={
                        uploadedImages
                    }
                    onImageSelected={props.input.onChange}
                    multiSelect={false}
                />
            );
        },
        mainImage: function() {
            return (
                <div>
                    <Field model="main-image" type="text" name="Main Image" component={inputColumnRenderMethods.mainImagePickerComponent.bind(this)}/>
                    <ImageUploader/>
                </div>
            );
        },
        vatViewComponent:function(props) {
            return <VatView
                parentProduct={{
                    taxRates: this.props.taxRates
                }}
                fullView={true}
                onVatChanged={props.input.onChange}
                variationCount={0}
            />
        },
        taxRates: function() {
            return (<Field name="taxRates" component={inputColumnRenderMethods.vatViewComponent.bind(this)}/>);
        }
    };

    var createFormComponent = React.createClass({
        getDefaultProps: function() {
            return {
                handleSubmit: null,
                addImage: null,
                uploadedImages: {},
                taxRates: null
            };
        },
        render: function() {
            return (
                <Form id="create-product-form" className={"form-root"} onSubmit={this.props.handleSubmit}>
                    <fieldset className={'form-root__fieldset'}>
                        <FormRow
                            label={'New Product Name'}
                            inputColumnContent={inputColumnRenderMethods.newProductName.call(this)}
                        />
                        <FormRow
                            label={'Main Image'}
                            inputColumnContent={inputColumnRenderMethods.mainImage.call(this)}
                        />
                    </fieldset>
                    <fieldset className={'form-root__fieldset'}>
                        <FormRow
                            label={'Tax Rates'}
                            inputColumnContent={inputColumnRenderMethods.taxRates.call(this)}
                        />
                    </fieldset>

                </Form>
            );
        }
    });

    return reduxForm.reduxForm({
        form: 'createProductForm'
    })(createFormComponent);
});
