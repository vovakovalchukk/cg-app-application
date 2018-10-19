import React from 'react';
import Select from 'Common/Components/Select';
    

    export default class extends React.Component {
        static defaultProps = {
            categories: [],
            disabled: true,
            selectedCategory: null
        };

        state = {};

        getSelectOptions = () => {
            var options = [];
            if (!this.props.categories) {
                return options;
            }
            $.each(this.props.categories, function(id, name) {
                options.push({name: name.title, value: id});
            });
            return options;
        };

        getSelectedCategory = () => {
            for (var category in this.props.categories) {
                if (category.id == this.props.selectedCategory) {
                    return category;
                }
            }
            return {name: '', value: ''};
        };

        render() {
            return <Select
                name="category"
                options={this.getSelectOptions()}
                autoSelectFirst={false}
                selectedOption={this.getSelectedCategory()}
                disabled={this.props.disabled}
                onOptionChange={this.props.getSelectCallHandler('category')}
            />
        }
    }

