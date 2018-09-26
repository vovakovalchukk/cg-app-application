import React from 'react';
import MultiSelect from 'Common/Components/MultiSelect';


class CategoryMapSelectComponent extends React.Component {
    static defaultProps = {
        addNewCategoryMapButtonVisible: true,
        onAddNewCategoryClick: function() {},
        onCategorySelected: function() {},
        options: {},
    };

    getCategorySelectOptions = () => {
        var options = [];
        for (var categoryId in this.props.options) {
            options.push({
                name: this.props.options[categoryId].name,
                value: categoryId
            });
        }
        return options;
    };

    onCategorySelected = (input, categories) => {
        var categoryIds = categories.map(function(category) {
            return category.value;
        });
        input.onChange(categoryIds);
        input.onFocus();
        this.props.onCategorySelected(categoryIds);
    };

    getSelectedOptions = () => {
        var categoryIds = [];
        for (var categoryId in this.props.options) {
            if (this.props.options[categoryId].selected) {
                categoryIds.push(categoryId);
            }
        }
        return categoryIds;
    };

    renderAddNewCategoryMapButton = () => {
        if (!this.props.addNewCategoryMapButtonVisible) {
            return null;
        }
        return <a href="#" onClick={this.props.onAddNewCategoryClick} className="add-new-category-map-button">Add new</a>;
    };

    renderErrorMessage = (meta) => {
        return (meta.visited || meta.touched) && meta.invalid && meta.error && (
            <span className="input-error categories-error">
                {meta.error}
            </span>
        );
    };

    render() {
        return (<label className="form-input-container">
            <span className={"inputbox-label"}>Category</span>
            <div className={"order-inputbox-holder"}>
                <MultiSelect
                    filterable={true}
                    onOptionChange={this.onCategorySelected.bind(this, this.props.input)}
                    options={this.getCategorySelectOptions()}
                    selectedOptions={this.getSelectedOptions()}
                    disabled={this.props.disabled}
                    classNames={'u-width-300px'}
                />
                {this.renderErrorMessage(this.props.meta)}
            </div>
            {this.renderAddNewCategoryMapButton()}
        </label>);
    }
}

export default CategoryMapSelectComponent;

