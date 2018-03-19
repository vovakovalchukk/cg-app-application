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
        getCategoryOptions: function () {
            var selects = [], options = [], categories;
            for (var categoryLevel = 0; categoryLevel < this.props.categories.categories.length; categoryLevel++) {
                categories = this.props.categories.categories[categoryLevel];
                options = [];
                for (var categoryId in categories) {
                    options.push({
                        'name': categories[categoryId].title,
                        'value': categoryId,
                        'level': categoryLevel
                    });
                }
                selects.push(
                    <Select
                        name="category"
                        options={options}
                        autoSelectFirst={false}
                        onOptionChange={this.onOptionChange}
                    />
                )
            }
            return selects;
        },
        onOptionChange: function (category) {
            this.props.input.onChange(category.value);
            if (this.props.onOptionChange) {
                this.props.onOptionChange(this.props.accountId, category.value, category.level);
            }
        },
        render: function() {
            return <span>
                {this.getCategoryOptions()}
            </span>
        }
    });
});
