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
    var FieldArray = ReduxForm.FieldArray;

    var AccountCategorySelect = React.createClass({
        getDefaultProps: function() {
            return {
                categories: [],
                accountId: 0,
                refreshable: false,
                refreshing: false,
                selectedCategories: [],
                onCategorySelected: function() {},
                onRefreshClick: function() {},
                onRemoveButtonClick: function() {},
            }
        },
        getCategoryOptions: function () {
            var selects = [],
                index = 0,
                selectedCategory;

            if (this.props.categories) {
                selects = this.getCategorySelects(this.props.categories, selects, index);
            }
            return selects;
        },
        getCategorySelects: function (categories, selects, index) {
            var selectedCategory = this.props.selectedCategories[index] ? this.props.selectedCategories[index] : null;
            selects.push(
                this.getCategorySelect(index, categories, selectedCategory)
            );
            if (selectedCategory
                && categories[selectedCategory]
                && categories[selectedCategory].categoryChildren
                && Object.keys(categories[selectedCategory].categoryChildren).length > 0
            ) {
                this.getCategorySelects(categories[selectedCategory].categoryChildren, selects, ++index);
            }
            return selects;
        },
        getCategorySelect: function (index, categories, selectedCategory) {
            return <Field
                name={this.props.fields.name + '.' + this.props.accountId}
                component={CategorySelect}
                categories={this.getCategorySelectOptionsForAccount(index, categories)}
                accountId={this.props.accountId}
                onOptionChange={this.onCategorySelected}
                selectedCategory={selectedCategory}
            />
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
            this.props.onCategorySelected(this.props.accountId, category.value, category.level, this.props.selectedCategories);
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
                className='remove-icon icon-small-margin'
            />
        },
        renderRefreshButton: function () {
            return <RefreshIcon
                onClick={this.onRefreshClick}
                disabled={this.props.refreshing}
                className='refresh-icon icon-small-margin'
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
