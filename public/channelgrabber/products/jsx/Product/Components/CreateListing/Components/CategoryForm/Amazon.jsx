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
        itemSpecifics: {},
        rootCategories: {},
        variationThemes: {},
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
        numberOfSelectFieldsRendered: 0
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
    }

    isSimpleProduct = () => {
        return this.props.product.variationCount > 1
    };

    formatVariationThemesAsSelectOptions = () => {
        return this.props.variationThemes.map((variationTheme) => {
            return {
                name: variationTheme.name,
                value: variationTheme.name
            }
        });
    };

    renderVariationThemesSelectComponent = (field) => {
        let selected = {
            name: field.input.value,
            value: field.input.value
        };
        return (
            <div className={'u-defloat u-display-flex'}>
                <label className="inputbox-label u-font-large">{field.displayTitle}</label>
                <Select
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
        return this.props.variationThemes.find((theme) => {
            return theme.name == name;
        });
    };

    getThemeHeadersByName = (name) => {
        let themeData = this.getThemeDataByName(name);
        let headers = [];
        themeData.validValues.forEach((header) => {
            headers.push(header.name);
        });
        return headers;
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
        return this.props.variationsDataForProduct.length * themeData.validValues.length;
    };

    renderThemeColumns = (variation) => {
        let themeColumns = [];
        let themeData = this.getThemeDataByName(this.state.themeSelected);

        themeData.validValues.forEach((value, index) => {
            themeColumns.push(this.renderThemeVariationSelect(value, variation.sku, index));
            themeColumns.push(this.renderThemeVariationDisplayNameInput(value, variation.sku, index));
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

    renderVariationThemeContent = () => {
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

    render() {
        return (
            <div className="amazon-category-form-container">
                <Subcategories rootCategories={this.props.rootCategories} accountId={this.props.accountId}/>
                <FormSection
                    name="itemSpecifics"
                    component={ItemSpecifics}
                    categoryId={this.props.categoryId}
                    itemSpecifics={this.props.itemSpecifics}
                />
                {this.isSimpleProduct() ? this.renderVariationThemeContent(false) : ''}
            </div>
        );
    }
}

export default AmazonCategoryFormComponent;

