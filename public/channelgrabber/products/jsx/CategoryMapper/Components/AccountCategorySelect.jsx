import React from 'react';
import {Field} from 'redux-form';
import RefreshIcon from 'Common/Components/RefreshIcon';
import RemoveIcon from 'Common/Components/RemoveIcon';
import CategorySelect from 'CategoryMapper/Components/CategorySelect';

class AccountCategorySelect extends React.Component {
    static defaultProps = {
        categories: [],
        accountId: 0,
        refreshable: false,
        refreshing: false,
        selectedCategories: [],
        onCategorySelected: function() {},
        onRefreshClick: function() {},
        onRemoveButtonClick: function() {},
    };

    renderCategorySelects = () => {
        return this.props.categories ? this.getCategorySelectsByLevel(this.props.categories) : [];
    };

    getCategorySelectsByLevel = (categories, selects = [], categoryLevel = 0) => {
        var selectedCategory = this.props.selectedCategories[categoryLevel] ? this.props.selectedCategories[categoryLevel] : null,
            resetSelection = (!selectedCategory && categoryLevel == 0);

        selects.push(
            this.getCategorySelect(categoryLevel, categories, selectedCategory, resetSelection)
        );

        if (selectedCategory
            && categories[selectedCategory]
            && categories[selectedCategory].categoryChildren
            && Object.keys(categories[selectedCategory].categoryChildren).length > 0
        ) {
            this.getCategorySelectsByLevel(categories[selectedCategory].categoryChildren, selects, ++categoryLevel);
        }

        return selects;
    };

    getCategorySelect = (categoryLevel, categories, selectedCategory, resetSelection) => {
        return <Field
            name={this.props.fields.name + '.' + this.props.accountId}
            component={CategorySelect}
            categories={this.getCategorySelectOptionsForAccount(categoryLevel, categories)}
            accountId={this.props.accountId}
            onOptionChange={this.onCategorySelected}
            selectedCategory={selectedCategory}
            resetSelection={resetSelection}
        />
    };

    getCategorySelectOptionsForAccount = (categoryLevel, categories) => {
        var options = [];
        for (var categoryId in categories) {
            options.push({
                'name': categories[categoryId].title,
                'value': categoryId,
                'level': categoryLevel
            });
        }
        return options;
    };

    onCategorySelected = (category) => {
        this.props.onCategorySelected(this.props.accountId, category.value, category.level, this.props.selectedCategories);
    };

    onRefreshClick = () => {
        this.props.onRefreshClick(this.props.accountId);
    };

    onRemoveButtonClick = () => {
        this.props.onRemoveButtonClick(this.props.accountId);
    };

    renderRemoveButton = () => {
        return <RemoveIcon
            onClick={this.onRemoveButtonClick}
            className='remove-icon icon-small-margin'
        />
    };

    renderRefreshButton = () => {
        return <RefreshIcon
            onClick={this.onRefreshClick}
            disabled={this.props.refreshing}
            className='refresh-icon icon-small-margin'
        />
    };

    renderActionButtons = () => {
        var actions = [this.renderRemoveButton()];
        if (this.props.refreshable) {
            actions.push(this.renderRefreshButton());
        }
        return <span className={'actions-container'}>
            {actions}
        </span>
    };

    render() {
        return <span className={'account-category-container'}>
            <div className={"select-and-actions-container u-float-left"}>
                {this.renderCategorySelects()}
            </div>
            {this.renderActionButtons()}
        </span>
    }
}

export default AccountCategorySelect;

