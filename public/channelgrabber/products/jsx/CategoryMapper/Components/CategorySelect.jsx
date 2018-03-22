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
                onOptionChange: null,
                resetSelection: null
            }
        },
        onOptionChange: function(category) {
            this.props.input.onChange(category.value);
            if (this.props.onOptionChange) {
                this.props.onOptionChange(category);
            }
        },
        getSelectedCategory: function() {
            if (this.props.resetSelection) {
                return {
                    name: '',
                    value: ''
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
            />
        }
    });
});
