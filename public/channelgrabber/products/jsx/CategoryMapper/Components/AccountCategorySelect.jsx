define([
    'react',
    'redux-form',
    'Common/Components/RefreshIcon',
    'Common/Components/RemoveIcon',
    'CategoryMapper/Components/CategorySelect',
], function(
    React,
    ReduxForm,
    RefreshIcon,
    RemoveIcon,
    CategorySelect
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
        renderRemoveButton: function () {
            return <RemoveIcon
                onClick={this.onRemoveButtonClick}
                class='remove-icon icon-small-margin'
            />
        },
        renderRefreshButton: function () {
            return <RefreshIcon
                onClick={this.onRefreshClick}
                disabled={this.props.refreshing}
                class='refresh-icon icon-small-margin'
            />
        },
        renderActionButtons: function () {
            var actions = [this.renderRemoveButton()];
            if (this.props.refreshable) {
                actions.push(this.renderRefreshButton());
            }
            return <span className={'actions-container'}>
                {actions}
            </span>
        },
        render: function() {
            return <span className={'account-category-container'}>
                <div className={"order-inputbox-holder"}>
                    {this.getCategoryOptions()}
                </div>
                {this.renderActionButtons()}
            </span>
        }
    });

    return AccountCategorySelect;
});
