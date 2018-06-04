define([
    'react',
    'redux-form',
    'Common/Components/Select',
    'Common/Components/MultiSelect',
    'Common/Components/Input',
    '../../../Validators'
], function(
    React,
    ReduxForm,
    Select,
    MultiSelect,
    Input,
    Validators
) {
    "use strict";

    var Field = ReduxForm.Field;
    var FieldArray = ReduxForm.FieldArray;

    const TYPE_TEXT = "text";
    const TYPE_SELECT = "select";
    const TYPE_CHOICE = "choice";
    const TYPE_SEQUENCE = "sequence";

    var AmazonItemSpecifics = React.createClass({
        getInitialState: function() {
            return {
                optionalItemSpecificsSelectOptions: null
            }
        },
        getDefaultProps: function() {
            return {
                categoryId: 0
            }
        },
        componentDidMount: function() {
            var optionalItems = this.props.itemSpecifics.optional;
            if (!optionalItems || Object.keys(optionalItems).length ==  0) {
                return null;
            }
            this.setState({
                optionalItemSpecificsSelectOptions: this.buildOptionalItemSpecificsSelectOptions(optionalItems)
            });
        },
        renderRequiredItemSpecificInputs: function() {
            var requiredItems = this.props.itemSpecifics.required;
            if (!requiredItems || Object.keys(requiredItems).length ==  0) {
                return null;
            }

            return this.renderItemSpecificsInputsFromOptions(requiredItems, true);
        },
        renderItemSpecificsInputsFromOptions: function(items, required) {
            var inputs = [],
                options;
            for (var name in items) {
                options = items[name];
                inputs.push(this.renderItemSpecificFromOptions(name, options, required));
            }
            return <span>{inputs}</span>;
        },
        renderItemSpecificFromOptions: function(name, options, required) {
            if (this.shouldRenderTextFieldArray(options)) {
                return this.renderFieldArray(name, this.renderTextInputArray, required);
            }
            return this.renderItemSpecificField(name, this.renderItemSpecificInput, options, required);
        },
        renderOptionsItemSpecificInputs: function() {
            var optionalItems = this.props.itemSpecifics.optional;
            if (!optionalItems || Object.keys(optionalItems).length ==  0) {
                return null;
            }

            return <FieldArray
                component={this.renderOptionsItemSpecificComponents}
                name={"optionalItemSpecifics"}
                itemSpecifics={optionalItems}
            />;
        },
        renderOptionsItemSpecificComponents: function(input) {
            var options = this.getOptionalItemSpecificsSelectOptions(input.itemSpecifics);
            var fields = [<label>
                <span className={"inputbox-label"}>{this.formatDisplayTitle(input.displayTitle)}</span>
                <div className={"order-inputbox-holder"}>
                    <Select
                        name="optionalItemSpecifics"
                        options={options}
                        autoSelectFirst={false}
                        title="Item Specifics (Optional)"
                        onOptionChange={this.onOptionalItemSpecificSelected.bind(this, input)}
                    />
                </div>
            </label>];
            var optionalItemSpecifics = input.fields.map((name) => {
                return <Field
                    name={name}
                    component={this.renderOptionalItemSpecific}
                    renderInput={this.renderItemSpecificFromOptions}
                />;
            });
            fields.push(optionalItemSpecifics);
            return <span>
                {fields}
            </span>
        },
        onOptionalItemSpecificSelected: function(input, selected) {
            if (-1 === this.state.optionalItemSpecificsSelectOptions.findIndex(option => selected.value == option.value)) {
                return;
            }
            input.fields.push({
                name: selected.name,
                options: selected.value
            });
            this.removeSelectedOptionFromOptions(selected);
        },
        removeSelectedOptionFromOptions: function(selectedOption) {
            var index = this.state.optionalItemSpecificsSelectOptions.findIndex(option => selectedOption.value == option.value);
            var newSelectOptions = this.state.optionalItemSpecificsSelectOptions.slice();
            newSelectOptions.splice(index, 1);
            this.setState({
                optionalItemSpecificsSelectOptions: newSelectOptions
            });
        },
        buildOptionalItemSpecificsSelectOptions: function(itemSpecifics) {
            var options = [];
            for (var index in itemSpecifics) {
                var itemSpecific = itemSpecifics[index];
                options.push({
                    "name": this.formatDisplayTitle(itemSpecific.name),
                    "value": itemSpecific.name
                })
            }
            return options;
        },
        formatDisplayTitle: function(name) {
            // Convert camel case to space separated words
            var name = name.replace(/([A-Z])/g, ' $1');
            return name.replace('-', ' ');
        },
        getOptionalItemSpecificsSelectOptions: function(itemSpecifics) {
            if (this.state.optionalItemSpecificsSelectOptions instanceof Array) {
                return this.state.optionalItemSpecificsSelectOptions;
            }
            return this.buildOptionalItemSpecificsSelectOptions(itemSpecifics);
        },
        renderOptionalItemSpecific: function(field) {
            return field.renderInput(field.input.value.name, field.input.value.options);
        },
        shouldRenderTextFieldArray: function(options) {
            return options.type == TYPE_TEXT && this.isMultiOption(options);
        },
        renderFieldArray: function(name, component, required) {
            var validator = (required ? Validators.required : null);
            return <FieldArray name={name} component={component} displayTitle={name} validate={validator}/>;
        },
        renderItemSpecificField: function(name, component, options, required) {
            var validator = (required ? Validators.required : null);
            return <Field
                name={name}
                displayTitle={name}
                component={component}
                options={options}
                validate={validator}
            />
        },
        renderItemSpecificInput: function(field) {
            if (field.options.type == TYPE_TEXT) {
                return this.renderTextInput(field);
            } else if (field.options.type == TYPE_SELECT) {
                return this.renderSelectInput(field);
            }
            return null;
        },
        renderTextInput: function(field) {
            return <label className="input-container">
                <span className={"inputbox-label"}>{!field.hideLabel ? this.formatDisplayTitle(field.displayTitle) : ''}</span>
                <div className={"order-inputbox-holder"}>
                    <Input
                        {...field.input}
                        className={Validators.shouldShowError(field) ? 'error' : null}
                    />
                </div>
                {Validators.shouldShowError(field) && (
                    <span className="input-error">{field.meta.error}</span>
                )}
                {this.getActionButtonForInput(field)}
            </label>;
        },
        getActionButtonForInput: function(field) {
            if (!('index' in field) || !field.fields) {
                return null;
            }
            if (field.index === field.fields.length - 1) {
                return this.renderPlusButton(() => field.fields.push(""));
            }
            return this.renderRemoveButton(() => field.fields.remove(field.index));
        },
        renderTextInputArray: function(input) {
            var fields = input.fields;
            if (fields.length === 0) {
                fields.push("");
            }
            return <span>
                {fields.map((name, index, fields) => {
                    return <Field
                        name={name}
                        component={this.renderTextInput}
                        displayTitle={input.displayTitle}
                        index={index}
                        fields={fields}
                        hideLabel={(index > 0)}
                    />;
                })}
                {input.meta.error && input.meta.dirty && (
                    <span className="input-error input-array-error">{input.meta.error}</span>
                )}
            </span>;
        },
        renderSelectInput: function(field) {
            var SelectComponent = this.isMultiOption(field.options) ? MultiSelect : Select;

            var options = this.buildSelectOptionsForItemSpecific(field.displayTitle, field.options.options);

            return <label className="input-container">
                <span className={"inputbox-label"}>{field.displayTitle}</span>
                <div className={"order-inputbox-holder"}>
                    <SelectComponent
                        autoSelectFirst={false}
                        title={this.formatDisplayTitle(field.displayTitle)}
                        options={options}
                        onOptionChange={this.onOptionSelected.bind(this, field.input)}
                        selectedOptions={field.input.value ? field.input.value : []}
                        selectedOption={this.findSelectedOption(field.input.value)}
                        className={Validators.shouldShowError(field) ? 'error' : null}
                        filterable={true}
                    />
                </div>
                {Validators.shouldShowError(field) && (
                    <span className="input-error">{field.meta.error}</span>
                )}
            </label>;
        },
        buildSelectOptionsForItemSpecific: function(title, options) {
            if (this.state[title]) {
                return this.state[title];
            }

            return Object.keys(options).map(value => {
                return {
                    name: options[value],
                    value: value
                }
            });
        },
        onOptionSelected: function(input, selectedOptions) {
            if (selectedOptions instanceof Array) {
                input.onChange(selectedOptions.map(option => option.value));
            }
            input.onChange(selectedOptions.value);
        },
        findSelectedOption: function(value) {
            value = value instanceof Array ? null : value;
            return {
                name: value || '',
                value: value || ''
            };
        },
        renderPlusButton: function (onClick) {
            return <span className="refresh-icon">
                <i
                    className='fa fa-2x fa-plus-square icon-create-listing'
                    aria-hidden='true'
                    onClick={onClick}
                />
            </span>;
        },
        renderRemoveButton: function (onClick) {
            return <span className="remove-icon">
                <i
                    className='fa fa-2x fa-minus-square icon-create-listing'
                    aria-hidden='true'
                    onClick={onClick}
                />
            </span>;
        },
        isMultiOption: function (options) {
            return (options.maxValues && options.maxValues > 1);
        },
        renderItemSpecifics: function(itemSpecifics, path = []) {
            var elements = [],
                optional = [],
                itemSpecificPath;

            for (var key in itemSpecifics) {
                var itemSpecific = itemSpecifics[key];
                itemSpecificPath = path.slice();
                itemSpecificPath.push(itemSpecific.name);
                if (itemSpecific.type == TYPE_SEQUENCE) {
                    elements = elements.concat(this.renderItemSpecifics(itemSpecific.children, itemSpecificPath));
                } else if (itemSpecific.type == TYPE_CHOICE) {
                    elements.push(this.renderOptionalItemSpecificSelect(itemSpecific.children, itemSpecificPath));
                } else {
                    if (itemSpecific.required) {
                        elements.push(this.renderItemSpecific(itemSpecific, itemSpecificPath));
                    } else {
                        optional.push(itemSpecific);
                    }
                }
            }

            if (optional.length > 0) {
                elements.push(this.renderOptionalItemSpecificSelect(optional, path));
            }

            return elements;
        },
        renderOptionalItemSpecificSelect: function(itemSpecifics, path) {
            return <FieldArray
                component={this.renderOptionsItemSpecificComponents}
                name={path.join('.')}
                itemSpecifics={itemSpecifics}
                displayTitle={path[path.length -1]}
            />;
        },
        renderItemSpecific: function(itemSpecific, path) {
            return this.renderItemSpecificFromOptions(path[path.length - 1], itemSpecific, true);
        },
        render: function () {
            if (Object.keys(this.props.itemSpecifics).length === 0 || !(0 in this.props.itemSpecifics)) {
                return null;
            }

            return <span>
                {this.renderItemSpecifics(this.props.itemSpecifics)}
            </span>
        }
    });

    return AmazonItemSpecifics;
});
