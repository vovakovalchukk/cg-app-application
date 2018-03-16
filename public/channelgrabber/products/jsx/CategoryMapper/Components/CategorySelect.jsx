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
                categories: []
            }
        },
        onOptionChange: function (category) {
            this.props.input.onChange(category.value);
        },
        render: function() {
            return <Select
                name="category"
                options={this.props.categories}
                autoSelectFirst={false}
                onOptionChange={this.onOptionChange}
            />
        }
    });
});
