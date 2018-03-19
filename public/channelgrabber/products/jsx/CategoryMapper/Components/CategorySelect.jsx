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
                onOptionChange: null
            }
        },
        onOptionChange: function (category) {
            this.props.input.onChange(category.value);
            if (this.props.onOptionChange) {
                this.props.onOptionChange(this.props.accountId, category.value);
            }
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
