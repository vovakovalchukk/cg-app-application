define([
    'react',
    'Common/Components/MultiSelect',
], function (
    React,
    MultiSelect
) {
    "use strict";

    var CategoryMapSelectComponent = React.createClass({
        getDefaultProps: function() {
            return {
                options: {},
                onAddNewCategoryClick: function() {}
            }
        },
        getCategorySelectOptions: function() {
            var options = [];
            for (var categoryId in this.props.options) {
                options.push({
                    name: this.props.options[categoryId],
                    value: categoryId
                });
            }
            return options;
        },
        onCategorySelected: function(input, categories) {
            input.onChange(categories.map(function(category) {
                return category.value;
            }));
        },
        render: function() {
            return (<label>
                <span className={"inputbox-label"}>Category</span>
                <div className={"order-inputbox-holder"}>
                    <MultiSelect
                        options={this.getCategorySelectOptions()}
                        onOptionChange={this.onCategorySelected.bind(this, this.props.input)}
                        filterable={true}
                    />
                </div>
                <a href="#" onClick={this.props.onAddNewCategoryClick}>Add new category map</a>
            </label>);
        }
    });

    return CategoryMapSelectComponent;
});
