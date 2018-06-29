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
                product: {}
            };
        },
        getInitialState: function() {
            return {
                themeSelected: null
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
        renderTableCellSelect: function(field) {
            let selected = {
                name: field.input.value,
                value: field.input.value
            };
            return (
                <div>
                    <Select
                        autoSelectFirst={false}
                        options={field.options}
                        selectedOption={selected}
                        onOptionChange={(option) => {
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
            let fieldName = "theme." + sku + "." + attributeIndex + "." + value.name
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
                       component={this.renderTableCellSelect}
                       options={formattedOptions}
                       autoSelectFirst={false}
                       validate={Validators.required}
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
                            })
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
