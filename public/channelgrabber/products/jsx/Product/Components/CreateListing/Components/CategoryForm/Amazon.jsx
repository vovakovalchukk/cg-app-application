define([
    'react',
    'redux-form',
    './Amazon/ItemSpecifics',
    './Amazon/Subcategories',
    'Product/Components/CreateListing/Components/CreateListing/VariationTable',
    'Common/Components/Select',
    'Common/Components/Input'
], function(
    React,
    ReduxForm,
    ItemSpecifics,
    Subcategories,
    VariationsTable,
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
                </div>
            );
        },
        renderTableCellSelect: function(field) {
            console.log('renderTableCellSelect with field: ', field);

            let selected = {
                name: field.input.value,
                value: field.input.value
            };
            return (
                <Select
                    autoSelectFirst={false}
                    options={field.options}
                    selectedOption={selected}
                    onOptionChange={(option) => {
                        return field.input.onChange(option.name);
                    }}
                    classNames={'u-width-120px'}
                />
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
        getThemeVariationSelectJSX: function(value, sku) {
            console.log('in getTHemeVariationSelectJsx');
            let formattedOptions = Object.keys(value.options).map((key) => {
                return {
                    name: value.options[key],
                    value: value.options[key]
                };
            });
            return (
                <span className={'u-width-120px'}>
                   <Field
                       name={"theme." + sku + "." + value.name}
                       component={this.renderTableCellSelect}
                       options={formattedOptions}
                       autoSelectFirst={false}
                   />
                </span>
            );
        },
        renderTableCellDisplayNameInput: function(value, sku) {
            console.log('in renderTableCellInput with value: ', value, ' and sku ', sku);
            return(
                <Input
                    name={"theme." + sku + "." + value.name+ ".displayName"}
                />
            )
        },
        getThemeVariationInputJSX: function(value, sku) {
            console.log('getTHemeVariationInputJSX');
            return (
                <Field
                    name={"theme." + sku + "." + value.name +".choice"}
                    component={this.renderTableCellDisplayNameInput}
                />
            );
        },
        renderThemeColumns: function(variation) {
            console.log('in renderThemeCOlumns with variation : ', variation);

            let themeColumns = [];
            let themeData = this.getThemeDataByName(this.state.themeSelected);

            themeData.validValues.forEach((value) => {
                themeColumns.push(this.getThemeVariationSelectJSX(value, variation.sku));
                //todo - create an input field for the displayName here
                themeColumns.push(this.getThemeVariationInputJSX(value, variation.sku));
            });

            return themeColumns.map((column) => {
                return <td className={'u-overflow-initial'}>{column}</td>
            });
        },
        renderThemeTable: function() {
            console.log('this.state.themeSelected: ', this.state.themeSelected);

            return (
                <div className={'u-margin-top-small'}>
                    <VariationsTable
                        sectionName={'theme'}
                        variationsDataForProduct={this.props.variationsDataForProduct}
                        product={this.props.product}

                        //todo fix bug relating to static images not showing
                        showImages={true}
                        renderImagePicker={false}

                        renderCustomTableHeaders={this.renderThemeHeaders}
                        renderCustomTableRows={this.renderThemeColumns}
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
                            console.log('in onchange with newValue:  ', newValue);
                            this.setState({
                                'themeSelected': newValue
                            })
                        }}
                        validate={value => (value ? undefined : 'Required')}
                    />
                    {this.state.themeSelected ? this.renderThemeTable() : ''}
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
                    {this.isSimpleProduct() ? this.renderVariationThemeContent() : ''}
                </div>
            );
        }
    });

    return AmazonCategoryFormComponent;
});
