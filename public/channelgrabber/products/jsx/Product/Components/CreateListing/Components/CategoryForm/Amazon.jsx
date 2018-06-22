define([
    'react',
    'redux-form',
    './Amazon/ItemSpecifics',
    './Amazon/Subcategories',
    'Product/Components/CreateListing/Components/CreateListing/VariationTable',
    'Product/Components/CreateListing/Validators',
    'Common/Components/Select',
    'Common/Components/Input'
], function(
    React,
    ReduxForm,
    ItemSpecifics,
    Subcategories,
    VariationsTable,
    Validators,
    Select,
    Input
) {
    "use strict";

    const FormSection = ReduxForm.FormSection;
    const Field = ReduxForm.Field;

    var AmazonCategoryFormComponent = React.createClass({
        getDefaultProps: function() {
            return {
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
        },
        getInitialState: function() {
            return {
                themeSelected: null,
                lastChangedThemeAttributeSelect: null,
                autopopulateMethods: [],
                autopopulated: false,
                numberOfSelectFieldsRendered: 0
            }
        },
        shouldComponentUpdate: function(prevProps) {
            if (!this.state.autopopulateMethods || this.state.autopopulated || !this.state.autopopulateMethods.length) {
                return true;
            }
            if (this.getNumberOfSelectFieldsToBeRendered() === this.state.numberOfSelectFieldsRendered) {
                for (let autopopulate of this.state.autopopulateMethods) {
                    autopopulate.method();
                }
                this.setState({
                    autopopulateMethods: [],
                    numberOfSelectFieldsRendered: 0,
                    autopopulated: true
                });
            }
            return true;
        },
        isSimpleProduct: function() {
            return this.props.product.variationCount > 0
        },
        formatVariationThemesAsSelectOptions: function() {
            return this.props.variationThemes.map((variationTheme) => {
                return {
                    name: variationTheme.name,
                    value: variationTheme.name
                }
            });
        },
        renderVariationThemesSelectComponent: function(field) {
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
        },
        getVariationDataFromSku: function(sku) {
            return this.props.variationsDataForProduct.find((variation) => {
                return variation.sku === sku
            });
        },
        equalisedWordSpellingsMatch(word1, word2) {
            word1 = word1.toLowerCase();
            word2 = word2.toLowerCase();
            const americanEnglishWordMap = {
                color: 'colour'
            };
            if (americanEnglishWordMap[word1] === word2 || americanEnglishWordMap[word2] === word1) {
                return true;
            }
            return false;
        },
        variationAttributeNameMatchesAmazonThemeAttributeName: function(variationAttributeName, amazonThemeAttributeName) {
            if (variationAttributeName == amazonThemeAttributeName) {
                return true;
            }
            if (this.equalisedWordSpellingsMatch(variationAttributeName, amazonThemeAttributeName)) {
                return true;
            }
            return false;
        },
        findVariationAttributeNameThatMatchedWithThemeAttributeName(variationData, themeAttributeName) {
            let variationAttributeNames = Object.keys(variationData.attributeValues);
            let matchedName;
            for (let variationAttributeName of variationAttributeNames) {
                if (this.variationAttributeNameMatchesAmazonThemeAttributeName(variationAttributeName, themeAttributeName)) {
                    matchedName = variationAttributeName;
                }
            }
            return matchedName;
        },
        findOptionContainingMatchedAttributeValue: function(options, matchedAttributeValueOfVariation) {
            return options.find((option) => {
                if (option.name.toLowerCase() === matchedAttributeValueOfVariation.toLowerCase()) {
                    return option;
                }
            });
        },
        findThemeOptionThatMatchesVariationAttribute: function(field) {
            let variationData = this.getVariationDataFromSku(field.variationSku);

            let sharedAttributeName = this.findVariationAttributeNameThatMatchedWithThemeAttributeName(variationData, field.themeAttributeName);
            if (!sharedAttributeName) {
                return undefined;
            }

            let matchedAttributeValueOfVariation = variationData.attributeValues[sharedAttributeName];
            let matchedOption = this.findOptionContainingMatchedAttributeValue(field.options, matchedAttributeValueOfVariation);
            if (!matchedOption) {
                return undefined;
            }

            return matchedOption;
        },
        hasAlreadyBeenAutopopulated: function(fieldName) {
            return this.state.lastChangedThemeAttributeSelect === fieldName;
        },
        generateAutoPopulateMethod(field, fieldNamePrefix, themeOptionThatMatchesVariationAttribute) {
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
        },
        updateAutoPopulateMethods: function(newAutoPopulateMethod) {
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
        },
        renderThemeAttributeSelect: function(field) {
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
        },
        getThemeDataByName: function(name) {
            return this.props.variationThemes.find((theme) => {
                return theme.name == name;
            });
        },
        getThemeHeadersByName: function(name) {
            let themeData = this.getThemeDataByName(name);
            let headers = [];
            themeData.validValues.forEach((header) => {
                headers.push(header.name);
            });
            return headers;
        },
        renderThemeHeaders: function() {
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
        },
        getThemeVariationSelectJSX: function(value, sku, attributeIndex) {
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
        },
        renderTableCellDisplayNameInput: function(field) {
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
        },
        getThemeVariationDisplayNameInputJSX: function(value, sku, attributeIndex) {
            let fieldName = "theme." + sku + "." + attributeIndex + ".displayName";
            return (
                <Field
                    name={fieldName}
                    component={this.renderTableCellDisplayNameInput}
                    validate={Validators.required}
                />
            );
        },
        getNumberOfSelectFieldsToBeRendered: function() {
            if (!this.state.themeSelected) {
                return;
            }
            let themeData = this.getThemeDataByName(this.state.themeSelected);
            return this.props.variationsDataForProduct.length * themeData.validValues.length;
        },
        renderThemeColumns: function(variation) {
            let themeColumns = [];
            let themeData = this.getThemeDataByName(this.state.themeSelected);

            themeData.validValues.forEach((value, index) => {
                themeColumns.push(this.getThemeVariationSelectJSX(value, variation.sku, index));
                themeColumns.push(this.getThemeVariationDisplayNameInputJSX(value, variation.sku, index));
            });

            return themeColumns.map((column) => {
                return <td className={'u-overflow-initial'}>{column}</td>
            });
        },
        renderThemeTable: function() {
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
        },
        resetThemeTable: function() {
            let themeSection = 'category.' + this.props.categoryId + '.theme';
            this.props.resetSection(themeSection);
            this.setState({
                numberOfSelectFieldsRendered: 0,
                autopopulateMethods: [],
                autopopulated: false
            })
        },
        renderVariationThemeContent: function() {
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
        },
        render: function() {
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
    });

    return AmazonCategoryFormComponent;
});
