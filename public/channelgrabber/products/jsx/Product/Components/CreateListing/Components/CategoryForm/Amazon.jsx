define([
    'react',
    'redux-form',
    './Amazon/ItemSpecifics',
    './Amazon/Subcategories',
    'Product/Components/CreateListing/Components/CreateListing/VariationTable',
    'Common/Components/Select'
], function(
    React,
    ReduxForm,
    ItemSpecifics,
    Subcategories,
    VariationsTable,
    Select
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
                themeSelected: false
            }
        },
        isSimpleProduct: function(){
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
        renderThemeTable: function() {
            return (
                <div className={'u-margin-top-small'}>
                    <VariationsTable
                        sectionName={'theme'}
                        variationsDataForProduct={this.props.variationsDataForProduct}
                        product={this.props.product}
                        showImages={true}
                        renderImagePicker={false}
                        //todo - use the below attributes for the following ticket - LIS-242
//                        renderCustomTableHeaders={this.renderIdentifierHeaders}
//                        renderCustomTableRows={this.renderIdentifierColumns}
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
                        onChange={() => {
                            this.setState({
                                'themeSelected': true
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
