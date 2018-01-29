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
                accountId: null,
                categories: null,
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
        getDefaultSelectedOption: function() {
            return {
                name: null
            };
        },
        render: function() {
            return <Select
                name="shopify-category"
                options={this.getSelectOptions()}
                autoSelectFirst={false}
                selectedOption={this.getDefaultSelectedOption()}
                disabled={this.props.disabled}
            />
        }
    });
});
