define([
    'react',
    'redux-form',
    'react-redux',
    'Product/Components/CreateProduct/functions/stateFilters',
    'Common/Components/ReduxForm/InputWithValidation',
    'Common/Components/ImageUploader/ImageUploaderRoot',
    'Common/Components/EditableText',
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
    InputWithValidation,
    ImageUploader,
    EditableText,
    ImagePicker,
    FormRow,
    VatView,
    VariationsTable,
    DimensionsTable
) {
    const Field = reduxForm.Field;
    const Form = reduxForm.Form;

    var inputColumnRenderMethods = {
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
                <div className={"o-container-wrap"}>
                    <Field
                        model="main-image"
                        type="text"
                        name="Main Image"
                        uploadedImages={this.props.uploadedImages}
                        component={inputColumnRenderMethods.renderMainImagePickerComponent}
                    />
                    <ImageUploader className={"u-float-none"}/>
                </div>
            );
        },
        renderVatViewComponent: function(props) {
            return <VatView
                parentProduct={{
                    taxRates: props.taxRates
                }}
                fullView={true}
                onVatChangeWithFullSelection={selection => {
                    var currentValueOnState = props.input.value;
                    var newValueForState = Object.assign(currentValueOnState, selection);
                    props.input.onChange(newValueForState);
                }}
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
        renderEditableText: function(reduxFormFieldsProps) {
            return (<EditableText
                    fieldId={reduxFormFieldsProps.fieldId}
                    classNames={reduxFormFieldsProps.classNames}
                    onChange={(e) => {
                        return reduxFormFieldsProps.input.onChange(e.target.textContent);
                    }}
                />
            );
        },
        componentWillReceiveProps: function() {
            if (!this.props.initialized) {
                var defaultValues = this.getDefaultValues();
                this.props.initialize(defaultValues);
            }
        },
        getDefaultValues: function() {
            return {
                taxRates: this.getDefaultTaxRates()
            }
        },
        getDefaultTaxRates: function() {
            var defaultTaxRates = {};
            for (var taxRate in this.props.taxRates) {
                for (var taxCodes in this.props.taxRates[taxRate]) {
                    var firstOption = this.props.taxRates[taxRate][taxCodes]
                    defaultTaxRates[taxRate] = firstOption['taxRateId'];
                    break;
                }
            }
            return defaultTaxRates;
        },
        render: function() {
            return (
                <Form id="create-product-form" className={"form-root margin-bottom-small"}>
                    <fieldset className={'form-root__fieldset margin-bottom-small'}>
                        <Field
                            type="text"
                            name="title"
                            placeholderText={"Enter Product Name"}
                            fieldId={"title"}
                            classNames={['c-editable-field', 'u-heading-text', 'u-margin-top-bottom-small']}
                            component={this.renderEditableText}
                        />
                        <FormRow
                            label={'Main Image'}
                            inputColumnContent={inputColumnRenderMethods.renderMainImage.call(this)}
                        />
                    </fieldset>
                    <fieldset className={'u-margin-bottom-small u-margin-top-small'}>
                        <legend className={'u-heading-text'}>Variations</legend>
                        <VariationsTable
                            resetSection={this.props.resetSection}
                            untouch={this.props.untouch}
                            change={this.props.change}
                            unregister={this.props.unregister}
                        />
                    </fieldset>
                    <fieldset className={'u-margin-bottom-small u-margin-top-small'}>
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
                            legend={'Dimensions'}
                            formSectionName='dimensionsTable'
                            fieldChange={this.props.change}
                        />
                    </fieldset>
                    <fieldset className={'u-margin-bottom-small'}>
                        <legend className={'u-heading-text'}>VAT</legend>
                        <div className={'u-max-width-60'}>
                            {inputColumnRenderMethods.renderTaxRates.call(this)}
                        </div>
                    </fieldset>
                </Form>
            );
        }
    });

    return reduxForm.reduxForm({
        form: 'createProductForm',
        initialValues: {
            variations: {}
        },
        validate: validate
    })(createFormComponent);

    function validate(values) {
        const errors = {};
        if (!values.variations) {
            return;
        }
        const variationIdentifiers = Object.keys(values.variations);
        if (!values.title || values.title === "Enter Product Name") {
            errors.title = 'Required';
        }
        if (variationIdentifiers.length > 0) {
            errors.variations = {};
            for (var i = 0; i < variationIdentifiers.length; i++) {
                var variation = values.variations[variationIdentifiers[i]]
                errors.variations[variationIdentifiers[i]] = {};
                if (!variation.sku) {
                    errors.variations[variationIdentifiers[i]].sku = 'Required'
                }
            }
        }
        return errors;
    }
});
