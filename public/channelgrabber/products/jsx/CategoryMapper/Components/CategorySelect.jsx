define([
    'react',
    'Common/Components/Select',
], function(
    React,
    Select
) {
    "use strict";

    var CategorySelectComponent = React.createClass({
        getDefaultProps: function() {
            return {
                categories: [],
                onOptionChange: null,
                className: 'category-select',
                selectedCategory: null
            }
        },
        onOptionChange: function(category) {
            this.props.input.onChange(category.value);
            if (this.props.onOptionChange) {
                this.props.onOptionChange(category);
            }
        },
        getSelectedCategory: function() {
            if (!this.props.selectedCategory) {
                // Resets the input value
                this.props.input.onChange(null);
                return {name: '', value: ''};
            }

            var categories = this.props.categories;
            for (var i = 0; i < categories.length; i++) {
                if (categories[i].value == this.props.selectedCategory) {
                    return categories[i];
                }
            }
            return null;
        },
        render: function() {
            return <Select
                name={this.props.name}
                options={this.props.categories}
                autoSelectFirst={false}
                onOptionChange={this.onOptionChange}
                selectedOption={this.getSelectedCategory()}
                className={this.props.className}
            />
        }
    });

    return CategorySelectComponent;
});
