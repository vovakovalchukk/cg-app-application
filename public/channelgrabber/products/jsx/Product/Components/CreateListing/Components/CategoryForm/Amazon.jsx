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
                    <label className="inputbox-label">{field.displayTitle}</label>
                    <Select
                        autoSelectFirst={false}
                        options={field.options}
                        selectedOption={selected}
                        onOptionChange={function(option) {
                            console.log('in onOptionChange of select');
                            
                            
                            return field.input.onChange(option.name);
                        }.bind(this)}
                    />
                </div>
            );
        },
        renderThemeTable: function() {
            console.log('in renderThemeTable with this.props: ', this.props);

            return (
                <VariationsTable
                    variationsDataForProduct={this.props.variationsDataForProduct}
                    product={this.props.product}
                    showImages={true}
                />
            );

            {/*<VariationTable*/
            }
            {/*sectionName={"identifiers"}*/
            }
            {/*variationsDataForProduct={this.props.variationsDataForProduct}*/
            }
            {/*product={this.props.product}*/
            }
            {/*showImages={true}*/
            }
            {/*attributeNames={this.props.attributeNames}*/
            }
            {/*attributeNameMap={this.props.attributeNameMap}*/
            }
            {/*renderCustomTableHeaders={this.renderIdentifierHeaders}*/
            }
            {/*renderCustomTableRows={this.renderIdentifierColumns}*/
            }
            {/*/>*/
            }

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
                            console.log('in onChange on outside(this should only be hit once');
                            this.setState({
                                'themeSelected': true
                            })
                        }}
                    />
                    {this.renderThemeTable()}
                </div>
            );
        },
        render: function() {
            console.log('in CategoryFOrm/render method with this.props', this.props);
            return (
                <div className="amazon-category-form-container">
                    <Subcategories rootCategories={this.props.rootCategories} accountId={this.props.accountId}/>
                    <FormSection
                        name="itemSpecifics"
                        component={ItemSpecifics}
                        categoryId={this.props.categoryId}
                        itemSpecifics={this.props.itemSpecifics}
                    />
                    {/*//todo prevent this from showing if simple product*/}
                    {this.props.variationCount > 0 ? this.renderVariationThemeContent() : ''}


                </div>
            );
        }
    });

    return AmazonCategoryFormComponent;
});
