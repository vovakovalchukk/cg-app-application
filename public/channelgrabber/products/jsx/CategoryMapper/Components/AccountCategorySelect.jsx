define([
    'react',
    'redux-form',
    'CategoryMapper/Components/CategorySelect',
    'Product/Components/CreateListing/Form/Shared/RefreshIcon'
], function(
    React,
    ReduxForm,
    CategorySelect,
    RefreshIcon
) {
    "use strict";

    var Field = ReduxForm.Field;
    var AccountCategorySelect = React.createClass({
        getDefaultProps: function() {
            return {
                categories: [],
                accountId: 0,
                refreshable: false,
                refreshing: false,
                resetSelection: null,
                onCategorySelected: function() {},
                onRefreshClick: function() {},
                onRemoveButtonClick: function() {},
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
                        onOptionChange={this.onCategorySelected}
                        resetSelection={this.props.resetSelection}
                    />
                )
                if (categoryLevel === 0) {
                    if (this.props.refreshable) {
                        selects.push(
                            <RefreshIcon
                                onClick={this.onRefreshClick}
                                disabled={this.props.refreshing}
                            />
                        );
                    }
                    selects.push(this.renderRemoveButton());
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
        onCategorySelected: function (category) {
            this.props.onCategorySelected(this.props.accountId, category.value, category.level);
        },
        onRefreshClick: function () {
            this.props.onRefreshClick(this.props.accountId);
        },
        onRemoveButtonClick: function () {
            this.props.onRemoveButtonClick(this.props.accountId);
        },
        renderRemoveButton: function (index) {
            return <span className="remove-icon">
                <i
                    className='fa fa-2x fa-times icon-create-listing'
                    aria-hidden='true'
                    onClick={this.onRemoveButtonClick}
                />
            </span>;
        },
        render: function() {
            return <span>
                {this.getCategoryOptions()}
            </span>
        }
    });

    return AccountCategorySelect;
});
