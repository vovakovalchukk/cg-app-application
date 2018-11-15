import React from 'react';
import {Field, Form, reduxForm} from 'redux-form';
import stateFilters from 'Product/Components/CreateProduct/functions/stateFilters';
import ImageUploader from 'Common/Components/ImageUploader/ImageUploaderRoot';
import EditableText from 'Common/Components/EditableText';
import ImagePicker from 'Common/Components/ImagePicker';
import FormRow from 'Common/Components/FormRow';
import VatView from 'Product/Components/VatView';
import VariationsTable from 'Product/Components/CreateProduct/VariationsTable/Root';
import DimensionsTable from 'Product/Components/CreateProduct/DimensionsTable/Root';
import ProductIdentifiers from 'Product/Components/CreateListing/Components/CreateListing/ProductIdentifiers';

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
        return (<VatView
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
            tableCssClassNames={'u-width-600px'}
        />);
    },
    renderTaxRates: function() {
        return (<Field
            name="taxRates"
            taxRates={this.props.taxRates}
            component={inputColumnRenderMethods.renderVatViewComponent}
        />);
    }
};

class createFormComponent extends React.Component {
    static defaultProps = {
        handleSubmit: null,
        addImage: null,
        uploadedImages: {},
        taxRates: null,
        newVariationRowRequest: null,
        showVAT: true,
        massUnit: null,
        lengthUnit: null
    };

    renderEditableText = (reduxFormFieldsProps) => {
        return (<EditableText
                fieldId={reduxFormFieldsProps.fieldId}
                classNames={reduxFormFieldsProps.classNames}
                onChange={(e) => {
                    return reduxFormFieldsProps.input.onChange(e.target.textContent);
                }}
            />
        );
    };

    componentWillReceiveProps() {
        if (!this.props.initialized) {
            var defaultValues = this.getDefaultValues();
            this.props.initialize(defaultValues);
        }
    }

    getDefaultValues = () => {
        return {
            taxRates: this.getDefaultTaxRates()
        }
    };

    getDefaultTaxRates = () => {
        var defaultTaxRates = {};
        for (var taxRate in this.props.taxRates) {
            for (var taxCodes in this.props.taxRates[taxRate]) {
                var firstOption = this.props.taxRates[taxRate][taxCodes]
                defaultTaxRates[taxRate] = firstOption['taxRateId'];
                break;
            }
        }
        return defaultTaxRates;
    };

    formatVariationImagesForProductIdentifiersComponent = (formVariations) => {
        if (!this.props.uploadedImages || !this.props.uploadedImages.images.length || !this.props.uploadedImages.images) {
            return formVariations;
        }

        let uploadedImages = this.props.uploadedImages.images;

        let formattedVariations = formVariations;

        formVariations.forEach((variation, i) => {
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
    };

    getAttributeNamesFromFormValues = () => {
        let customAttributes = this.props.formValues.attributes;
        if (!customAttributes) {
            return [];
        }
        let attributeNames = [];
        for (let customAttributeKey in customAttributes) {
            attributeNames.push(customAttributes[customAttributeKey]);
        }

        return attributeNames;
    };

    formatAttributeValuesForVariation = (customAttributesObject, attributeNames, variation) => {
        let formattedAttributeValues = {};

        for (let attributeKey in customAttributesObject) {
            for (let attributeName of attributeNames) {
                if (customAttributesObject[attributeKey] !== attributeName) {
                    continue;
                }
                formattedAttributeValues[attributeName] = variation[attributeKey];
            }
        }
        variation.attributeValues = formattedAttributeValues;
        return variation;
    };

    addAttributeValuesToVariationsData = (variationsData) => {
        let attributeNames = this.getAttributeNamesFromFormValues();
        let customAttributesObject = this.props.formValues.attributes;
        let variations = this.props.formValues.variations;

        if (!attributeNames.length || !customAttributesObject || !variations) {
            return variationsData;
        }

        let formattedVariationData = [];

        for (let variation of variationsData) {
            let variationDataWithAttributes = this.formatAttributeValuesForVariation(customAttributesObject, attributeNames, variation);
            formattedVariationData.push(variationDataWithAttributes);
        }

        return formattedVariationData
    };

    formatReduxFormValuesForProductIdentifiersComponent = () => {
        let formVariations = this.props.formValues.variations;
        formVariations = Object.keys(formVariations).map((variation, index) => {
            return Object.assign(formVariations[variation], {
                id: 'variation-' + index
            });
        });
        formVariations = this.formatVariationImagesForProductIdentifiersComponent(formVariations);
        formVariations = this.addAttributeValuesToVariationsData(formVariations);
        return formVariations;
    };

    variationsDataExistsInRedux = () => {
        if (
            this.props.formValues &&
            this.props.formValues.variations
        ) {
            return true;
        }
    };

    renderProductIdentifiers = () => {
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
        let attributeNames = [];

        if (this.variationsDataExistsInRedux()) {
            variationsData = this.formatReduxFormValuesForProductIdentifiersComponent();
            attributeNames = this.getAttributeNamesFromFormValues();
        }

        return (
            <fieldset className={'u-margin-bottom-small u-margin-top-small'}>
                <legend className={'u-heading-text'}>Product Identifiers</legend>
                <ProductIdentifiers
                    variationsDataForProduct={variationsData}
                    product={product}
                    renderImagePicker={false}
                    shouldRenderStaticImagesFromVariationValues={true}
                    containerCssClasses={'u-margin-top-none u-max-width-80'}
                    tableCssClasses={'u-min-width-50 u-width-inherit'}
                    attributeNames={attributeNames}
                    renderStaticImageFromFormValues={true}
                />
            </fieldset>
        );
    };

    renderVatTable = (renderTaxRates) => {
        if (!this.props.showVAT) {
            return;
        }
        return (
            <fieldset className={'u-margin-bottom-small'}>
                <legend className={'u-heading-text'}>VAT</legend>
                <div className={'u-max-width-80'}>
                    {renderTaxRates.call(this)}
                </div>
            </fieldset>
        );
    };

    render() {
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
}

export default reduxForm({
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

