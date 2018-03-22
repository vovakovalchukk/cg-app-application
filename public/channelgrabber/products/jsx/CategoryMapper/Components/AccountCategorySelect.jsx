define([
    'react',
    'redux-form',
    'CategoryMapper/Components/CategorySelect',
    'Common/Components/Select',
    'Product/Components/CreateListing/Form/Shared/RefreshIcon'
], function(
    React,
    ReduxForm,
    CategorySelect,
    Select,
    RefreshIcon
) {
    "use strict";

    var Field = ReduxForm.Field;
    return React.createClass({
        getDefaultProps: function() {
            return {
                categories: [],
                accountId: 0,
                onOptionChange: null,
                onRefreshClick: null,
                refreshing: false
            }
        },
        getCategoryOptions: function () {
            var selects = [];
            for (var categoryLevel = 0; categoryLevel < this.props.categories.length; categoryLevel++) {
                if (Object.keys(this.props.categories[categoryLevel]).length === 0) {
                    continue;
                }
                selects.push(
                    <Field
                        name={"category." + this.props.accountId}
                        component={CategorySelect}
                        categories={this.getCategorySelectOptionsForAccount(categoryLevel, this.props.categories[categoryLevel])}
                        accountId={this.props.accountId}
                        onOptionChange={this.onOptionChange}
                    />
                )
                if (categoryLevel === 0) {
                    selects.push(
                        <RefreshIcon
                            onClick={this.onRefreshClick}
                            disabled={this.props.refreshing}
                        />
                    )
                }
            }
            return selects;
        },
        getCategorySelectOptionsForAccount: function (categoryLevel, categories) {
            var options = [];
            for (var categoryId in categories) {
                options.push({
                    'name': categories[categoryId].title,
                    'value': categoryId,
                    'level': categoryLevel
                });
            }
            return options;
        },
        onOptionChange: function (category) {
            if (this.props.onOptionChange) {
                this.props.onOptionChange(this.props.accountId, category.value, category.level);
            }
        },
        onRefreshClick: function () {
            if (this.props.onRefreshClick) {
                this.props.onRefreshClick(this.props.accountId);
            }
        },
        render: function() {
            return <span>
                {this.getCategoryOptions()}
            </span>
        }
    });
});
