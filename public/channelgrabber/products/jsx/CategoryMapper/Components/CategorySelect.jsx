import React from 'react';
import Select from 'Common/Components/Select';


class CategorySelectComponent extends React.Component {
    static defaultProps = {
        categories: [],
        onOptionChange: null,
        className: 'category-select',
        selectedCategory: null,
        resetSelection: null
    };

    onOptionChange = (category) => {
        this.props.input.onChange(category.value);
        if (this.props.onOptionChange) {
            this.props.onOptionChange(category);
        }
    };

    getSelectedCategory = () => {
        if (!this.props.selectedCategory) {
            return {name: '', value: ''};
        }

        var categories = this.props.categories;
        for (var i = 0; i < categories.length; i++) {
            if (categories[i].value == this.props.selectedCategory) {
                return categories[i];
            }
        }
        return {name: '', value: ''};
    };

    render() {
        console.log('in Category select');
        
        
        if (this.props.resetSelection) {
            this.props.input.onChange(null);
        }
        return <Select
            name={this.props.name}
            options={this.props.categories}
            autoSelectFirst={false}
            onOptionChange={this.onOptionChange}
            selectedOption={this.getSelectedCategory()}
            className={this.props.className}
            filterable={true}
            key={this.props.name}
        />
    }
}

export default CategorySelectComponent;

