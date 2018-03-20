define([
    'react',
    'redux-form',
    'CategoryMapper/Components/CategorySelect',
    'Common/Components/Select',
], function(
    React,
    ReduxForm,
    CategorySelect,
    Select
) {
    "use strict";

    var Field = ReduxForm.Field;
    return React.createClass({
        getDefaultProps: function() {
            return {
                categories: [],
                accountId: 0,
            }
        },
        getCategoryOptions: function () {
            var selects = [], options = [], categories;
            for (var categoryLevel = 0; categoryLevel < this.props.categories.length; categoryLevel++) {
                if (Object.keys(this.props.categories[categoryLevel]).length === 0) {
                    continue;
                }
                categories = this.props.categories[categoryLevel];
                options = [];
                for (var categoryId in categories) {
                    options.push({
                        'name': categories[categoryId].title,
                        'value': categoryId,
                        'level': categoryLevel
                    });
                }
                selects.push(
                    <Field
                        name={"category." + this.props.accountId}
                        component={CategorySelect}
                        categories={options}
                        accountId={this.props.accountId}
                        onOptionChange={this.onOptionChange}
                    />
                )
            }
            return selects;
        },
        onOptionChange: function (category) {
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
