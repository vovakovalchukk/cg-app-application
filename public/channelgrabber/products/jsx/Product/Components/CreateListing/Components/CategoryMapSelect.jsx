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
                addNewCategoryMapButtonVisible: true,
                onAddNewCategoryClick: function() {},
                options: {}
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
        renderAddNewCategoryMapButton: function() {
            if (!this.props.addNewCategoryMapButtonVisible) {
                return null;
            }
            return <a href="#" onClick={this.props.onAddNewCategoryClick} className="add-new-category-map-button">Add new</a>;
        },
        render: function() {
            return (<label className="form-input-container">
                <span className={"inputbox-label"}>Category</span>
                <div className={"order-inputbox-holder"}>
                    <MultiSelect
                        options={this.getCategorySelectOptions()}
                        onOptionChange={this.onCategorySelected.bind(this, this.props.input)}
                        filterable={true}
                    />
                </div>
                {this.renderAddNewCategoryMapButton()}
            </label>);
        }
    });

    return CategoryMapSelectComponent;
});
