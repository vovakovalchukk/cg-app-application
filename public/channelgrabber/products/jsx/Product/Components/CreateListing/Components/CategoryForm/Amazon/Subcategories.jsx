define([
    'react',
    'redux-form',
    'Common/Components/Select',
    'CategoryMapper/Actions/ApiHelper'
], function(
    React,
    ReduxForm,
    Select,
    ApiHelper
) {
    "use strict";

    var FieldArray = ReduxForm.FieldArray;
    var Field = ReduxForm.Field;

    var SubcategoriesComponent = React.createClass({
        getDefaultProps: function () {
            return {
                rootCategories:  {},
                accountId: 0
            };
        },
        renderSubCategorySelectComponents: function (input, values) {
            if (input.fields.length === 0) {
                input.fields.push({options: this.formatCategoryOptions(this.props.rootCategories)});
            }
            console.log(input, values);
            var test = input.fields.map(name => {
                return <Field
                    name={name}
                    component={this.renderCategorySelect}
                />;
            });
            return <span>{test}</span>;
        },
        formatCategoryOptions: function (categories) {
            return Object.keys(categories).map(categoryId => {
                var category = categories[categoryId];
                return {
                    name: category.title,
                    value: categoryId,
                    listable: category.listable
                }
            });
        },
        renderCategorySelect: function (field) {
            console.log(field);
            return <Select
                name={field.input.name}
                options={field.input.value.options}
                autoSelectFirst={false}
                onOptionChange={this.onCategorySelected}
            />;
        },
        onCategorySelected: function (category) {
            $.get(
                ApiHelper.buildCategoryChildrenUrl(this.props.accountId, category.value),
                function(response) {
                    console.log(response);
                }
            );
        },
        render: function () {
            console.log(this.props);
            return <label className="input-container">
                <span className={"inputbox-label"}>Subcategory</span>
                <div className={"order-inputbox-holder"}>
                    <FieldArray name="subcategory" component={this.renderSubCategorySelectComponents}/>
                </div>
            </label>;
        }
    });

    return SubcategoriesComponent;
});
