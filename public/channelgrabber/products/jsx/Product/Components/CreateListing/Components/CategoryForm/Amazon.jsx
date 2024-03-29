import React from 'react';
import {FormSection, Field} from 'redux-form';
import ItemSpecifics from './Amazon/ItemSpecifics';
import Subcategories from './Amazon/Subcategories';
import VariationsTable from 'Product/Components/CreateListing/Components/CreateListing/VariationTable';
import Validators from 'Product/Components/CreateListing/Validators';
import Select from 'Common/Components/Select';
import Input from 'Common/Components/Input';

class AmazonCategoryFormComponent extends React.Component {
    static defaultProps = {
        categoryId: null,
        accountId: 0,
        rootCategories: {},
        amazonCategories: {},
        variationsDataForProduct: [],
        product: {},
        fieldChange: null,
        resetSection: null
    };

    state = {
        themeSelected: null,
        lastChangedThemeAttributeSelect: null,
        autopopulateMethods: [],
        autopopulated: false,
        numberOfSelectFieldsRendered: 0,
        selectedAmazonCategory: null,
        itemSpecifics: [],
        variationThemes: [],
        availableVariationThemes: [],
        selectedProductType: null,
        availableProductTypes: [],
        productTypesFromVariationThemes: []
    };

    shouldComponentUpdate() {
        // stacking and then calling these methods here was a means of getting around the 2nd render ReduxForm issue, https://github.com/erikras/redux-form/issues/621
        if (!this.state.autopopulateMethods || this.state.autopopulated || !this.state.autopopulateMethods.length) {
            return true;
        }
        if (this.getNumberOfSelectFieldsToBeRendered() !== this.state.numberOfSelectFieldsRendered) {
           return true;
        }
        for (let autopopulate of this.state.autopopulateMethods) {
            autopopulate.method();
        }
        this.setState({
            autopopulateMethods: [],
            numberOfSelectFieldsRendered: 0,
            autopopulated: true
        });
        return true;
    };

    isSimpleProduct = () => {
        return this.props.product.variationCount <= 1;
    };

    formatVariationThemesAsSelectOptions = () => {
        return this.state.availableVariationThemes.map((variationTheme) => {
            return {
                name: variationTheme.name,
                value: variationTheme.name
            }
        });
    };

    renderVariationThemesSelectComponent = (field) => {
        if (this.shouldEnforceSelectionOfPrerequisitesBeforeRenderingVariationThemes()) {
            return this.renderVariationPrerequisitesWarning();
        }
        let selected = {
            name: field.input.value,
            value: field.input.value
        };
        return (
            <div className={'u-defloat u-display-flex'}>
                <label className="inputbox-label u-font-large">{field.displayTitle}</label>
                <Select
                    filterable={true}
                    autoSelectFirst={false}
                    options={field.options}
                    selectedOption={selected}
                    onOptionChange={(option) => {
                        this.setState({
                            autopopulateMethods: [],
                            numberOfSelectFieldsRendered: 0
                        });
                        return field.input.onChange(option.name);
                    }}
                />
                {Validators.shouldShowError(field) && (
                    <span className="input-error u-margin-left-small">{field.meta.error}</span>
                )}
            </div>
        );
    };

    getVariationDataFromSku = (sku) => {
        return this.props.variationsDataForProduct.find((variation) => {
            return variation.sku === sku
        });
    };

    doWordSpellingsMatch = (word1, word2) => {
        word1 = word1.toLowerCase();
        word2 = word2.toLowerCase();
        const americanEnglishWordMap = {
            color: 'colour'
        };
        return americanEnglishWordMap[word1] === word2 || americanEnglishWordMap[word2] === word1;
    };

    variationAttributeNameMatchesAmazonThemeAttributeName = (variationAttributeName, amazonThemeAttributeName) => {
        if (variationAttributeName == amazonThemeAttributeName) {
            return true;
        }
        return this.doWordSpellingsMatch(variationAttributeName, amazonThemeAttributeName);
    };

    findVariationAttributeNameThatMatchedWithThemeAttributeName = (variationData, themeAttributeName) => {
        let variationAttributeNames = Object.keys(variationData.attributeValues);
        let matchedName;
        for (let variationAttributeName of variationAttributeNames) {
            if (this.variationAttributeNameMatchesAmazonThemeAttributeName(variationAttributeName, themeAttributeName)) {
                matchedName = variationAttributeName;
            }
        }
        return matchedName;
    };

    findOptionContainingMatchedAttributeValue = (options, matchedAttributeValueOfVariation) => {
        return options.find((option) => {
            if (option.name.toLowerCase() === matchedAttributeValueOfVariation.toLowerCase()) {
                return option;
            }
        });
    };

    findThemeOptionThatMatchesVariationAttribute = (field) => {
        let variationData = this.getVariationDataFromSku(field.variationSku);

        let sharedAttributeName = this.findVariationAttributeNameThatMatchedWithThemeAttributeName(variationData, field.themeAttributeName);
        if (!sharedAttributeName) {
            return null;
        }

        let matchedAttributeValueOfVariation = variationData.attributeValues[sharedAttributeName];
        let matchedOption = this.findOptionContainingMatchedAttributeValue(field.options, matchedAttributeValueOfVariation);
        if (!matchedOption) {
            return null;
        }

        return matchedOption;
    };

    hasAlreadyBeenAutopopulated = (fieldName) => {
        return this.state.lastChangedThemeAttributeSelect === fieldName;
    };

    generateAutoPopulateMethod = (field, fieldNamePrefix, themeOptionThatMatchesVariationAttribute) => {
        let newAutoPopulateMethod = {
            field: field.input.name,
            method: () => {
                this.props.fieldChange(
                    field.input.name,
                    themeOptionThatMatchesVariationAttribute.value
                );
                this.props.fieldChange(
                    fieldNamePrefix + field.nameOfCorrespondingDisplayNameField,
                    themeOptionThatMatchesVariationAttribute.value
                );
                this.setState({
                    lastChangedThemeAttributeSelect: field.input.name
                });
            }
        };
        return newAutoPopulateMethod;
    };

    updateAutoPopulateMethods = (newAutoPopulateMethod) => {
        this.setState(prevState => {
            let newAutoPopulateMethods = prevState.autopopulateMethods;
            let methodExistsAlready = prevState.autopopulateMethods.find((method) => {
                return method.field === newAutoPopulateMethod.field;
            });
            if (newAutoPopulateMethod.field && !methodExistsAlready) {
                newAutoPopulateMethods.push(newAutoPopulateMethod);
            }
            return {
                autopopulateMethods: newAutoPopulateMethods,
                numberOfSelectFieldsRendered: prevState.numberOfSelectFieldsRendered + 1
            }
        });
    };

    renderThemeAttributeSelect = (field) => {
        const fieldNamePrefix = "category." + this.props.categoryId + ".";
        let newAutoPopulateMethod = {
            field: undefined,
            method: () => {
            }
        };

        let selectedOption = {
            name: field.input.value,
            value: field.input.value
        };
        let optionToShowAsSelected = selectedOption;

        if (!this.state.autopopulated && !this.hasAlreadyBeenAutopopulated(field.input.name)) {
            let themeOptionThatMatchesVariationAttribute = this.findThemeOptionThatMatchesVariationAttribute(field);
            if (themeOptionThatMatchesVariationAttribute) {
                newAutoPopulateMethod = this.generateAutoPopulateMethod(field, fieldNamePrefix, themeOptionThatMatchesVariationAttribute);
                optionToShowAsSelected = themeOptionThatMatchesVariationAttribute;
            }
        }

        this.updateAutoPopulateMethods(newAutoPopulateMethod);

        return (
            <div>
                <Select
                    autoSelectFirst={false}
                    options={field.options}
                    selectedOption={optionToShowAsSelected}
                    filterable={true}
                    customOptions={true}
                    onOptionChange={(option) => {
                        this.setState({
                            lastChangedThemeAttributeSelect: field.input.name
                        });
                        this.props.fieldChange(
                            fieldNamePrefix + field.nameOfCorrespondingDisplayNameField,
                            option.value
                        );
                        return field.input.onChange(option.name);
                    }}
                    classNames={'u-width-120px'}
                />
                {Validators.shouldShowError(field) && (
                    <span className="input-error">{field.meta.error}</span>
                )}
            </div>
        );
    };

    getThemeDataByName = (name) => {
        return this.state.variationThemes.find((theme) => {
            return theme.name == name;
        });
    };

    getThemeHeadersByName = (name) => {
        let themeData = this.getThemeDataByName(name);
        if (themeData === undefined) {
            return [];
        }
        let headers = [];
        themeData.attributes.forEach((header) => {
            headers.push(header);
        });
        return headers;
    };

    getThemeValidValuesByName = (name, themeData) => {
        return themeData.validValues.find((validValue) => {
            return validValue.name == name;
        });
    };

    renderThemeHeaders = () => {
        let themeSelected = this.state.themeSelected;
        let themeHeaders = this.getThemeHeadersByName(themeSelected);
        let themeHeadersWithDisplayNames = [];
        themeHeaders.forEach((header) => {
            themeHeadersWithDisplayNames.push(header);
            themeHeadersWithDisplayNames.push(header + ' (Display Name)');
        });
        return themeHeadersWithDisplayNames.map((header) => {
            return <th> {header} </th>;
        });
    };

    renderThemeVariationSelect = (value, sku, attributeIndex) => {
        let fieldName = "theme." + sku + "." + attributeIndex + "." + value.name;
        let nameOfCorrespondingDisplayNameField = "theme." + sku + "." + attributeIndex + ".displayName";

        let formattedOptions = Object.keys(value.options).map((key) => {
            return {
                name: value.options[key],
                value: value.options[key]
            };
        });

        return (
            <span className={'u-width-120px'}>
               <Field
                   name={fieldName}
                   nameOfCorrespondingDisplayNameField={nameOfCorrespondingDisplayNameField}
                   component={this.renderThemeAttributeSelect}
                   options={formattedOptions}
                   autoSelectFirst={false}
                   validate={Validators.required}
                   variationSku={sku}
                   themeAttributeName={value.name}
               />
            </span>
        );
    };

    renderTableCellDisplayNameInput = (field) => {
        return (
            <div>
                <Input
                    name={field.input.name}
                    value={field.input.value}
                    onChange={field.input.onChange}
                    inputFieldSpecificClassNames={'u-transition-global-short'}
                />
                {Validators.shouldShowError(field) && (
                    <span className="input-error">{field.meta.error}</span>
                )}
            </div>
        )
    };

    renderThemeVariationDisplayNameInput = (value, sku, attributeIndex) => {
        let fieldName = "theme." + sku + "." + attributeIndex + ".displayName";
        return (
            <Field
                name={fieldName}
                component={this.renderTableCellDisplayNameInput}
                validate={Validators.required}
            />
        );
    };

    getNumberOfSelectFieldsToBeRendered = () => {
        if (!this.state.themeSelected) {
            return;
        }
        let themeData = this.getThemeDataByName(this.state.themeSelected);
        return this.props.variationsDataForProduct.length * themeData.attributes.length;
    };

    renderThemeColumns = (variation) => {
        let themeColumns = [];
        let themeData = this.getThemeDataByName(this.state.themeSelected);

        themeData.attributes.forEach((attribute, index) => {
            let validValues = this.getThemeValidValuesByName(attribute, themeData) || {name: attribute, options: []};
            themeColumns.push(this.renderThemeVariationSelect(validValues, variation.sku, index));
            themeColumns.push(this.renderThemeVariationDisplayNameInput(validValues, variation.sku, index));
        });

        return themeColumns.map((column) => {
            return <td className={'u-overflow-initial'}>{column}</td>
        });
    };

    renderThemeTable = () => {
        return (
            <div className={'u-margin-top-small'}>
                <VariationsTable
                    sectionName={'theme'}
                    variationsDataForProduct={this.props.variationsDataForProduct}
                    product={this.props.product}
                    renderImagePicker={true}
                    showImages={true}
                    imageDropdownsDisabled={true}
                    renderCustomTableHeaders={this.renderThemeHeaders}
                    renderCustomTableRows={this.renderThemeColumns}
                    attributeNames={this.props.product.attributeNames}
                />
            </div>
        );
    };

    resetThemeTable = () => {
        let themeSection = 'category.' + this.props.categoryId + '.theme';
        this.props.resetSection(themeSection);
        this.setState({
            numberOfSelectFieldsRendered: 0,
            autopopulateMethods: [],
            autopopulated: false
        })
    };

    renderItemSpecifics = () => {
        if (this.state.itemSpecifics.length == 0) {
            return;
        }
        return (
            <FormSection
                name="itemSpecifics"
                component={ItemSpecifics}
                categoryId={this.props.categoryId}
                itemSpecifics={this.state.itemSpecifics}
                selectedProductType={this.state.selectedProductType ? this.state.selectedProductType.name : null}
                currentVariationThemeAttributes={this.state.themeSelected ? this.getThemeDataByName(this.state.themeSelected).attributes : null}
                isSimpleProduct={this.isSimpleProduct()}
                isVariationThemeSelected={this.state.themeSelected !== null}
            />
        );
    };

    renderVariationThemeContent = () => {
        if (this.isSimpleProduct()) {
            return;
        }
        return (
            <div>
                <Field
                    name="variationTheme"
                    component={this.renderVariationThemesSelectComponent}
                    displayTitle={"Variation Theme"}
                    options={this.formatVariationThemesAsSelectOptions()}
                    onChange={(e, newValue, oldValue) => {
                        this.setState({
                            'themeSelected': newValue
                        });
                        if (oldValue && newValue !== oldValue) {
                            this.resetThemeTable();
                        }
                    }}
                    validate={Validators.required}
                />
                {this.state.themeSelected && this.props.variationsDataForProduct ? this.renderThemeTable() : ''}
            </div>
        );
    };


    shouldEnforceSelectionOfPrerequisitesBeforeRenderingVariationThemes = () => {
        let amazonCategoryIsSelected = this.state.selectedAmazonCategory != null;
        let amazonCategorySupportsVariations = this.state.variationThemes.length > 0;
        if (amazonCategoryIsSelected === true && amazonCategorySupportsVariations === false) {
            return true;
        }
        let productTypeIsSelected = this.state.selectedProductType != null;
        let categoryHasRootVariationThemes = this.state.variationThemes.filter(variationTheme => variationTheme.supportsRootCategory == true).length > 0;
        if (productTypeIsSelected === false && categoryHasRootVariationThemes === false) {
            return true;
        }
        let productTypesWithVariationOverridesExist = this.state.variationThemes.filter(variationTheme => variationTheme.productTypes.length > 0).length > 0;
        return !categoryHasRootVariationThemes && (productTypesWithVariationOverridesExist && !productTypeIsSelected);
    };

    renderVariationPrerequisitesWarning = () => {
        let message = this.getVariationPrerequisitesMessage();
        return (
            <div className={'order-inputbox-holder u-defloat u-display-flex'}>
                <label className="inputbox-label u-font-large">Variation Theme</label>
                <div>{message}</div>
            </div>
        );
    };

    getVariationPrerequisitesMessage = () => {
        if (this.state.selectedAmazonCategory === null) {
            return "Please select an Amazon Category";
        }
        if (this.state.variationThemes.length == 0) {
            return "Amazon do not support variation listings in the selected Amazon Category. Please select another Amazon Category.";
        }
        return "Please select a Product Type";
    };

    renderAmazonCategorySelect = () => {
        return (
            <div>
                <Field
                    name="amazonCategoryId"
                    component={this.renderAmazonCategorySelectComponent}
                    validate={Validators.required}
                />
            </div>
        );
    };

    renderAmazonCategorySelectComponent = (field) => {
        return (
            <div className={'order-inputbox-holder u-defloat u-display-flex'}>
                <label className="inputbox-label u-font-large">Amazon Category</label>
                <Select
                    autoSelectFirst={false}
                    filterable={true}
                    priorityOptions={this.props.amazonCategories.priorityOptions}
                    options={this.props.amazonCategories.options}
                    selectedOption={this.state.selectedAmazonCategory}
                    onOptionChange={(option) => {
                        field.input.onChange(option.value);
                        this.setState({
                            selectedAmazonCategory: option,
                            selectedProductType: null,
                            themeSelected: null,
                            availableVariationThemes: []
                        });
                        this.resetThemeTable();
                        this.fetchAndSetAmazonCategoryDependentValues(option.value);
                    }}
                />
                {Validators.shouldShowError(field) && (
                    <span className="input-error u-margin-left-small">{field.meta.error}</span>
                )}
            </div>
        );
    };

    isCategorySelected = () => {
        return this.state.selectedAmazonCategory !== null;
    };

    renderAmazonProductTypeSelect = () => {
        return (
            <div>
                <Field
                    name="productType"
                    component={this.renderAmazonProductTypeSelectComponent}
                    onChange={() => {} }
                />
            </div>
        );
    };

    renderAmazonProductTypeSelectComponent = (field) => {
        if (this.isCategorySelected() === false) {
            return (
                <div className={'order-inputbox-holder u-defloat u-display-flex'}>
                    <label className="inputbox-label u-font-large">Product Type</label>
                    <div>Please select an Amazon Category</div>
                </div>
            );
        }
        return (
            <div className={'order-inputbox-holder u-defloat u-display-flex'}>
                <label className="inputbox-label u-font-large">Product Type</label>
                <Select
                    autoSelectFirst={false}
                    options={this.getAvailableProductTypes()}
                    selectedOption={this.state.selectedProductType}
                    onOptionChange={(option) => {
                        this.setAvailableVariationThemes(option.value)
                        this.setState({selectedProductType: option});
                        field.input.onChange(option.value);
                    }}
                />
                {Validators.shouldShowError(field) && (
                    <span className="input-error u-margin-left-small">{field.meta.error}</span>
                )}
            </div>
        );
    };

    getAvailableProductTypes = () => {
        if (this.state.itemSpecifics.length == 0) {
            return [];
        }
        let rootItemSpecific = this.state.itemSpecifics[0];
        if (this.isSimpleProduct() || this.state.variationThemes.filter(variationTheme => variationTheme.supportsRootCategory == true).length > 0) {
            return this.getProductTypesFromItemSpecifics(rootItemSpecific);
        }
        return this.state.productTypesFromVariationThemes.map(productType => ({name: productType, value: productType}) );
    };

    getProductTypesFromItemSpecifics = (rootItemSpecific) => {
        let productTypeItemSpecific = rootItemSpecific.children.find(itemSpecific => itemSpecific.name == 'ProductType');
        if (productTypeItemSpecific === undefined) {
            return [];
        }
        if (productTypeItemSpecific.options) {
            return productTypeItemSpecific.options.map(productType => ({name: productType, value: productType}) );
        }
        if (productTypeItemSpecific.children) {
            return productTypeItemSpecific.children.map(productType => ({name: productType.name, value: productType.name}) );
        }
    };

    setAvailableVariationThemes = (productType) => {
        let categoryRootVariationThemes = this.state.variationThemes.filter(variationTheme => variationTheme.supportsRootCategory == true)
        if (productType === null) {
            this.setState({availableVariationThemes: categoryRootVariationThemes});
            return;
        }
        let productTypeVariationThemes = this.state.variationThemes.filter(variationTheme => variationTheme.productTypes.includes(productType));
        if (productTypeVariationThemes.length > 0) {
            this.setState({availableVariationThemes: productTypeVariationThemes});
            return;
        }
        this.setState({availableVariationThemes: categoryRootVariationThemes});
        return;
    };

    fetchAndSetAmazonCategoryDependentValues = (amazonCategoryId) => {
        $.ajax({
            context: this,
            url: '/products/create-listings/' + this.props.accountId + '/amazon-category-dependent-field-values/' + amazonCategoryId,
            type: 'GET',
            success: function (response) {
                this.setState({
                    itemSpecifics: response.itemSpecifics,
                    variationThemes: response.variationThemes,
                    productTypesFromVariationThemes: response.productTypesFromVariationThemes,
                    availableVariationThemes: response.variationThemes
                });
            }
        });
    };

    render() {
        return (
            <div className="amazon-category-form-container">
                <Subcategories rootCategories={this.props.rootCategories} accountId={this.props.accountId}/>
                {this.renderAmazonCategorySelect()}
                {this.renderAmazonProductTypeSelect()}
                {this.renderItemSpecifics()}
                {this.renderVariationThemeContent()}
            </div>
        );
    };
}

export default AmazonCategoryFormComponent;

