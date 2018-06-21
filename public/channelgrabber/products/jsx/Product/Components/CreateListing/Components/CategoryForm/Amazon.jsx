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
                lastChangedThemeAttributeSelect: null
            }
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
        renderThemeAttributeSelect: function(field) {
            const fieldNamePrefix = "category." + this.props.categoryId + ".";
            let optionToShowAsSelected = null;

            let selectedOption = {
                name: field.input.value,
                value: field.input.value
            };

            let themeOptionThatMatchesVariationAttribute=null;
            console.log('lastChangedThemeAttributeSelect: ' , this.state.lastChangedThemeAttributeSelect);
            console.log('field.input.name: ', field.input.name);
            
            
            if(this.state.lastChangedThemeAttributeSelect !== field.input.name){
                themeOptionThatMatchesVariationAttribute = this.findThemeOptionThatMatchesVariationAttribute(field);
            }
            console.log('themeOptionThatMatchesVariationAttribute: ', themeOptionThatMatchesVariationAttribute ,' for : ' , field.input.name);

            if (
                themeOptionThatMatchesVariationAttribute
            ) {
                console.log('about to run .change and set selected option on ' , field.input.name, ' themeOptionThatMatched...', themeOptionThatMatchesVariationAttribute);
                // change it's own value to be that of the matched variation attribute
                setTimeout(()=>{
                    console.log('firitng onchange with themeOptionThatMatchesVariationAttribute.value: ' , themeOptionThatMatchesVariationAttribute.value);
                    //this is setting the rendered field below to be the matchedOption
                    this.props.fieldChange(
                        field.input.name,
                        themeOptionThatMatchesVariationAttribute.value
                    );
                        this.setState({
                            lastChangedThemeAttributeSelect: field.input.name
                        });

                }, 0);



                optionToShowAsSelected = themeOptionThatMatchesVariationAttribute;
            }else{
                optionToShowAsSelected = selectedOption;
                
            }
            //todo - you can call the onChange here & set the onChange event at field level and only change if newVal does not match oldVal

            return (
                <div>
                    {/*<div> arbitary state {this.state.arbitary} </div>*/}
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
//                       onChange={(event, newValue, previousValue) => {
//                           console.log('---IN ONCHANGEwith themeVariationSelectJSX event: ', event, ' newValue: ', newValue , ' previousValue: ' , previousValue);
//                           //todo prevent running onChange using prevent default if newValue==previousValue
//                            if(newValue==previousValue){
//                                return;
//                            }
//                            if(nameOfCorrespondingDisplayNameField !== this.state.lastChangedThemeAttributeSelect){
//                                this.setState({
//                                    lastChangedThemeAttributeSelect: nameOfCorrespondingDisplayNameField
//                                });
//                            }
//
////
////                            console.log('newValue: ', newValue);
////
////
////                                this.props.fieldChange(
////                                    nameOfCorrespondingDisplayNameField,
////                                    newValue.value
////                                )
//
//
//                       }}
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
                    onChange={(e,newValue,oldValue)=>{
                        console.log('-------INPUT FIELD CHANGE');
                        
                        
                    }}
                />
            );
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
        renderVariationThemeContent: function() {
            return (
                <div>
                    <Field
                        name="variationTheme"
                        component={this.renderVariationThemesSelectComponent}
                        displayTitle={"Variation Theme"}
                        options={this.formatVariationThemesAsSelectOptions()}
                        onChange={(e, newValue) => {
                            this.setState({
                                'themeSelected': newValue
                            });
                            let themeSection = 'category.' + this.props.categoryId + '.theme';
                            this.props.resetSection(themeSection);
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
