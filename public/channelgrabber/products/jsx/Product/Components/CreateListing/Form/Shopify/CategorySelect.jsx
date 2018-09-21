import React from 'react';
import Select from 'Common/Components/Select';
    

    export default React.createClass({
        getDefaultProps: function() {
            return {
                categories: [],
                disabled: true,
                selectedCategory: null
            }
        },
        getInitialState: function() {
            return {}
        },
        getSelectOptions: function() {
            var options = [];
            if (!this.props.categories) {
                return options;
            }
            $.each(this.props.categories, function(id, name) {
                options.push({name: name.title, value: id});
            });
            return options;
        },
        getSelectedCategory: function() {
            for (var category in this.props.categories) {
                if (category.id == this.props.selectedCategory) {
                    return category;
                }
            }
            return {name: '', value: ''};
        },
        render: function() {
            return <Select
                name="category"
                options={this.getSelectOptions()}
                autoSelectFirst={false}
                selectedOption={this.getSelectedCategory()}
                disabled={this.props.disabled}
                onOptionChange={this.props.getSelectCallHandler('category')}
            />
        }
    });

