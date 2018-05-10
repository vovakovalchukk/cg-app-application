define([
    'react',
    'redux-form',
    'Common/Components/Select',
    'Common/Components/MultiSelect',
    'Common/Components/Input',
    './CustomItemSpecific',
    '../../../Validators'
], function(
    React,
    ReduxForm,
    Select,
    MultiSelect,
    Input,
    CustomItemSpecific,
    Validators
) {
    "use strict";

    var Field = ReduxForm.Field;
    var FieldArray = ReduxForm.FieldArray;

    const TYPE_TEXT = "text";
    const TYPE_SELECT = "select";
    const TYPE_TEXT_SELECT = "textselect";
    const TYPE_CUSTOM = "custom";

    return React.createClass({
        getInitialState: function() {
            return {
                selectOptions: {},
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
                return this.renderFieldArray(name, this.renderTextInputArray);
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
                <span className={"inputbox-label"}><b>Item Specifics (Optional)</b></span>
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
            for (var name in itemSpecifics) {
                options.push({
                    "name": name,
                    "value": itemSpecifics[name]
                })
            }
            options.push({
                "name": "Add Custom Item Specific",
                "value": {type: 'custom'}
            });
            return options;
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
        renderFieldArray: function(name, component) {
            return <FieldArray name={name} component={component} displayTitle={name}/>;
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
            } else if (field.options.type == TYPE_SELECT || field.options.type == TYPE_TEXT_SELECT) {
                return this.renderSelectInput(field);
            } else if(field.options.type == TYPE_CUSTOM) {
                return this.renderCustomItemSpecificField(CustomItemSpecific);
            }
            return null;
        },
        renderTextInput: function(field) {
            return <label className="input-container">
                <span className={"inputbox-label"}>{!field.hideLabel ? field.displayTitle : ''}</span>
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
            </span>;
        },
        renderSelectInput: function(field) {
            var SelectComponent = this.isMultiOption(field.options) ? MultiSelect : Select;
            var customOptionEnabled = field.options.type == TYPE_TEXT_SELECT;

            var options = this.buildSelectOptionsForItemSpecific(field.displayTitle, field.options.options);

            return <label className="input-container">
                <span className={"inputbox-label"}>{field.displayTitle}</span>
                <div className={"order-inputbox-holder"}>
                    <SelectComponent
                        autoSelectFirst={false}
                        title={field.displayTitle}
                        options={options}
                        customOptions={customOptionEnabled}
                        onOptionChange={this.onOptionSelected.bind(this, field.input)}
                        selectedOptions={field.input.value ? field.input.value : []}
                        selectedOption={this.findSelectedOption(field.input.value)}
                        onCustomOption={this.onCustomOption}
                        className={Validators.shouldShowError(field) ? 'error' : null}
                    />
                </div>
                {Validators.shouldShowError(field) && (
                    <span className="input-error">{field.meta.error}</span>
                )}
            </label>;
        },
        renderCustomItemSpecificField: function() {
            return <FieldArray
                name="customItemSpecifics"
                component={this.renderCustomItemSpecificComponent}
            />
        },
        renderCustomItemSpecificComponent: function(input) {
            if (input.fields.length === 0) {
                input.fields.push({name: "", value: ""});
            }
            return <span>
                {input.fields.map((name, index, fields) => {
                    return <label>
                        <Field
                            name={`${name}.name`}
                            component={this.renderCustomSpecificNameComponent}
                            fields={fields}
                            index={index}
                        />
                        <Field
                            name={`${name}.value`}
                            component={this.renderCustomSpecificValueComponent}
                            fields={fields}
                            index={index}
                        />
                        {this.renderRemoveButton(() => fields.remove(index))}
                    </label>;
                })}
            </span>;
        },
        renderCustomSpecificNameComponent: function(field) {
            return (<span className={"inputbox-label container-extra-item-specific"}>
                <Input
                    {...field.input}
                    onChange={this.onCustomItemSpecificChange.bind(this, field)}
                />
            </span>);
        },
        renderCustomSpecificValueComponent: function(field) {
            return (<div className={"order-inputbox-holder"}>
                <Input
                    {...field.input}
                    onChange={this.onCustomItemSpecificChange.bind(this, field)}
                />
            </div>);
        },
        onCustomItemSpecificChange: function(field, event) {
            var value = event.target.value;
            field.input.onChange(value);
            if (field.fields.length === field.index + 1) {
                field.fields.push({name: "", value: ""})
            }
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
        onCustomOption: function(newOption, allOptions, selectTitle) {
            this.setState(Object.assign(this.state.selectOptions, {
                [selectTitle]: allOptions
            }));
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
        render: function () {
            return <span>
                {this.renderRequiredItemSpecificInputs()}
                {this.renderOptionsItemSpecificInputs()}
            </span>
        }
    });
});
