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
    'Product/Components/CreateProduct/DimensionsTable/Root',
    'Product/Components/CreateListing/Components/CreateListing/ProductIdentifiers'
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
    DimensionsTable,
    ProductIdentifiers
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
                        name="mainImage"
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
                newVariationRowRequest: null,
                showVAT: true,
                massUnit: null,
                lengthUnit: null
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
        formatVariationImagesForProductIdentifiersComponent: function(formVariations) {
            if (!this.props.uploadedImages || !this.props.uploadedImages.images.length || !this.props.uploadedImages.images) {
                return formVariations;
            }

            let uploadedImages = this.props.uploadedImages.images;

            let formattedVariations = formVariations;

            formVariations.forEach((variation,i) => {
                let matchedUploadedImage = uploadedImages.find(uploadedImage => {
                    return uploadedImage.id === variation.imageId;
                });
                if (!matchedUploadedImage) {
                    return
                }
                formattedVariations[i].images = [{
                    id: matchedUploadedImage.id,
                    url: matchedUploadedImage.url
                }];
            });
            return formattedVariations;
        },
        formatReduxFormValuesForProductIdentifiersComponent: function() {
            let formVariations = this.props.formValues.variations;
            formVariations = Object.keys(formVariations).map(variation => {
                return formVariations[variation];
            });

            formVariations = this.formatVariationImagesForProductIdentifiersComponent(formVariations);
            return formVariations;
        },
        variationsDataExistsInRedux: function() {
            if (
                this.props.formValues &&
                this.props.formValues.variations
            ) {
                return true;
            }
        },
        renderProductIdentifiers: function() {
            let product = {
                images: this.props.uploadedImages.images
            };
            let variationsData = [
                {
                    sku: '',
                    images: [
                        {url: ''}
                    ]
                }
            ];

            if (this.variationsDataExistsInRedux()) {
                variationsData = this.formatReduxFormValuesForProductIdentifiersComponent()
            }

            return (
                <fieldset className={'u-margin-bottom-small u-margin-top-small'}>
                    <legend className={'u-heading-text'}>Product Identifiers</legend>
                        <ProductIdentifiers
                            variationsDataForProduct={variationsData}
                            product={product}
                            renderImagePicker={false}
                            renderStaticImagesFromVariationValues={true}
                            containerCssClasses={'u-margin-top-none'}
                        />
                </fieldset>
            );
        },
        renderVatTable: function(renderTaxRates) {
            if (!this.props.showVAT) {
                return;
            }
            return (
                <fieldset className={'u-margin-bottom-small'}>
                    <legend className={'u-heading-text'}>VAT</legend>
                    <div className={'u-max-width-60'}>
                        {renderTaxRates.call(this)}
                    </div>
                </fieldset>
            );
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
                        <legend className={'u-heading-text'}>Product Details</legend>
                        <VariationsTable
                            resetSection={this.props.resetSection}
                            untouch={this.props.untouch}
                            fieldChange={this.props.change}
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
                            massUnit={this.props.massUnit}
                            lengthUnit={this.props.lengthUnit}
                        />
                    </fieldset>
                    {this.renderVatTable(inputColumnRenderMethods.renderTaxRates)}
                    {this.renderProductIdentifiers()}
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
