import React from 'react';
import {Field, FieldArray} from 'redux-form';
import Select from 'Common/Components/Select';
import RemoveIcon from 'Common/Components/RemoveIcon';
import ApiHelper from 'CategoryMapper/Actions/ApiHelper';

    var SubcategoriesComponent = React.createClass({
        getDefaultProps: function () {
            return {
                rootCategories:  {},
                accountId: 0
            };
        },
        getInitialState: function () {
            return {
                disabled: false
            }
        },
        renderSubCategorySelectComponents: function (input) {
            if (input.fields.length === 0) {
                this.pushInputInFieldsArray(input.fields, this.props.rootCategories);
            }

            var inputs = input.fields.map((name, index, fields) => {
                return <Field
                    name={name}
                    component={this.renderCategorySelect}
                    fields={fields}
                    index={index}
                />;
            });

            return <span>{inputs}</span>;
        },
        pushInputInFieldsArray: function(fields, categories) {
            fields.push({
                options: this.formatCategoryOptions(categories),
                selected: {name: '', value: ''}
            });
        },
        formatCategoryOptions: function (categories) {
            return Object.keys(categories).map(categoryId => {
                var category = categories[categoryId];
                return {
                    name: category.title,
                    value: categoryId,
                    listable: category.listable
                }
            });
        },
        renderCategorySelect: function (field) {
            return <span>
                <Select
                    name={field.input.name}
                    options={field.input.value.options}
                    autoSelectFirst={false}
                    onOptionChange={this.onCategorySelected.bind(this, field.input, field.fields, field.index)}
                    selectedOption={field.input.value.selected}
                    className={"sub-category-select"}
                />
                {field.index === 0 && this.renderRemoveButton(field.fields)}
            </span>
        },
        onCategorySelected: function (input, fields, index, category) {
            if (this.state.disabled) {
                return;
            }
            this.setState({
                disabled: true
            });
            this.setSelectedCategoryOnInput(input, category);
            this.removeCategorySelectsFromFieldArray(fields, index + 1);
            var self = this;
            $.get(
                ApiHelper.buildCategoryChildrenUrl(this.props.accountId, category.value),
                function(response) {
                    self.setState({
                        disabled: false
                    });
                    if (!response.categories || Object.keys(response.categories).length === 0) {
                        return;
                    }
                    self.pushInputInFieldsArray(fields, response.categories);
                }
            );
        },
        setSelectedCategoryOnInput: function (input, category) {
            input.onChange(Object.assign({}, input.value, {
                selected: category
            }));
        },
        removeCategorySelectsFromFieldArray: function (fields, deleteIndex) {
            var length = fields.length;
            while (deleteIndex < length) {
                fields.remove(deleteIndex);
                length--;
            }
        },
        renderRemoveButton: function (fields) {
            return <RemoveIcon
                onClick={this.onRemoveButtonClick.bind(this, fields)}
                className='remove-icon icon-small-margin'
            />;
        },
        onRemoveButtonClick: function (fields) {
            if (this.state.disabled) {
                return;
            }
            fields.removeAll();
            this.pushInputInFieldsArray(fields, this.props.rootCategories);
        },
        render: function () {
            return <label className="input-container">
                <span className={"inputbox-label"}>Subcategory</span>
                <div className={"order-inputbox-holder sub-category-select-container"}>
                    <FieldArray
                        name="subcategory"
                        component={this.renderSubCategorySelectComponents}
                        rerenderOnEveryChange={true}
                    />
                </div>
            </label>;
        }
    });

    export default SubcategoriesComponent;

