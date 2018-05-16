define([
    'react',
    'redux-form',
    'react-redux',
    'Product/Components/CreateProduct/functions/stateFilters',
    'Common/Components/ImageUploader/ImageUploaderRoot',
    'Common/Components/ImagePicker',
    'Common/Components/FormRow',
    'Product/Components/VatView',
    'Product/Components/CreateProduct/VariationsTable/Root',
    'Product/Components/CreateProduct/DimensionsTable/Root'
], function(
    React,
    reduxForm,
    ReactRedux,
    stateFilters,
    ImageUploader,
    ImagePicker,
    FormRow,
    VatView,
    VariationsTable,
    DimensionsTable
) {
    var Field = reduxForm.Field;
    var Form = reduxForm.Form;

    var inputColumnRenderMethods = {
        renderNewProductName: function() {
            return (
                <Field type="text" name="title" className={'form-row__input'} component="input"/>
            )
        },
        renderMainImagePickerComponent: function(props) {
            var uploadedImages = props.uploadedImages.images;
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
        renderMainImage: function() {
            return (
                <div>
                    <Field model="main-image"
                           type="text"
                           name="Main Image"
                           uploadedImages={this.props.uploadedImages}
                           component={inputColumnRenderMethods.renderMainImagePickerComponent}
                    />
                    <ImageUploader />
                </div>
            );
        },
        renderVatViewComponent: function(props) {
            return <VatView
                parentProduct={{
                    taxRates: props.taxRates
                }}
                fullView={true}
                onVatChanged={props.input.onChange}
                variationCount={0}
            />
        },
        renderTaxRates: function() {
            return (<Field
                name="taxRates"
                taxRates={this.props.taxRates}
                component={inputColumnRenderMethods.renderVatViewComponent}
            />);
        }
    };

    var createFormComponent = React.createClass({
        getDefaultProps: function() {
            return {
                handleSubmit: null,
                addImage: null,
                uploadedImages: {},
                taxRates: null,
                newVariationRowRequest: null
            };
        },
        render: function() {
            return (
                <Form id="create-product-form" className={"form-root margin-bottom--small"}>
                    <fieldset className={'form-root__fieldset margin-bottom--small'}>
                        <FormRow
                            label={'New Product Name'}
                            inputColumnContent={inputColumnRenderMethods.renderNewProductName.call(this)}
                        />
                        <FormRow
                            label={'Main Image'}
                            inputColumnContent={inputColumnRenderMethods.renderMainImage.call(this)}
                        />
                    </fieldset>
                    <fieldset className={'form-root__fieldset margin-bottom--small'}>
                        <FormRow
                            label={'Tax Rates'}
                            inputColumnContent={inputColumnRenderMethods.renderTaxRates.call(this)}
                        />
                    </fieldset>
                    <fieldset className={'margin-bottom--small'}>
                        <VariationsTable
                            resetSection={this.props.resetSection}
                        />
                        <DimensionsTable
                            stateSelectors={{
                                fields: ['variationsTable', 'fields'],
                                rows: ['variationsTable', 'variations'],
                                values: ['form', 'createProductForm', 'variations']
                            }}
                            stateFilters={{
                                fields: stateFilters.filterFields.bind(2)
                            }}
                            formName='createProductForm'
                            formSectionName='dimensionsTable'
                            classNames={['u-margin-top-small']}
                            fieldChange={this.props.change}
                        />
                    </fieldset>
                </Form>
            );
        }
    });

    return reduxForm.reduxForm({
        form: 'createProductForm',
        initialValues: {
            variations: {}
        }
    })(createFormComponent);
});
