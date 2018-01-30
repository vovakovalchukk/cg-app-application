define([
    'react',
    'Common/Components/Select',
], function(
    React,
    Select
) {
    "use strict";

    return React.createClass({
        getDefaultProps: function() {
            return {
                categories: [],
                disabled: true
            }
        },
        getInitialState: function() {
            return {}
        },
        getSelectOptions: function() {
            var options = [];
            if (!this.props.categories) {
                return options;
            }
            $.each(this.props.categories, function(id, name) {
                options.push({name: name, value: id})
            });
            return options;
        },
        getSelectedCategoryName: function() {
            for (var categoryId in this.props.categories) {
                if (categoryId == this.props.selectedCategory) {
                    return {name: this.props.categories[categoryId]}
                }
            }

            return null;
        },
        render: function() {
            return <Select
                name="category"
                options={this.getSelectOptions()}
                autoSelectFirst={false}
                selectedOption={this.getSelectedCategoryName()}
                disabled={this.props.disabled}
                onOptionChange={this.props.getSelectCallHandler('category')}
            />
        }
    });
});
