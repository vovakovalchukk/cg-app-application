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
                disabled: true,
                selectedCategory: null
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
                options.push({name: name, value: name})
            });
            return options;
        },
        render: function() {
            return <Select
                name="category"
                options={this.getSelectOptions()}
                autoSelectFirst={false}
                selectedOption={{name: this.props.selectedCategory}}
                disabled={this.props.disabled}
                onOptionChange={this.props.getSelectCallHandler('category')}
            />
        }
    });
});
