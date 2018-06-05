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
    var FormSection = ReduxForm.FormSection;

    const TYPE_TEXT = "text";
    const TYPE_SELECT = "select";
    const TYPE_CHOICE = "choice";
    const TYPE_SEQUENCE = "sequence";

    var AmazonItemSpecifics = React.createClass({
        getDefaultProps: function() {
            return {
                itemSpecifics: {}
            };
        },
        getInitialState: function () {
            return {
                selectedChoices: {}
            };
        },
        renderRoot: function() {
            if (Object.keys(this.props.itemSpecifics).length === 0 || !(0 in this.props.itemSpecifics)) {
                return null;
            }
            var rootItemSpecific = this.props.itemSpecifics[0];
            return this.renderItemSpecifics(rootItemSpecific.children, rootItemSpecific.name);
        },
        renderItemSpecifics: function (itemSpecifics, name) {
            var optional = [],
                fields = [];

            itemSpecifics.forEach((itemSpecific) => {
                var field;

                if (!itemSpecific.required) {
                    optional.push(itemSpecific);
                    return;
                }

                switch (itemSpecific.type) {
                    case TYPE_TEXT:
                        field = this.renderTextField(itemSpecific);
                        break;
                    case TYPE_SELECT:
                        field = this.renderSelectField(itemSpecific);
                        break;
                    case TYPE_CHOICE:
                        field = this.renderChoiceField(itemSpecific);
                        break;
                    case TYPE_SEQUENCE:
                        field = this.renderSequence(itemSpecific);
                        break;
                    default:
                        field = null;
                }
                fields.push(field);
            });

            if (optional.length > 0) {
                // Add the optional IS selector in here
            }

            return <FormSection name={name}>
                {fields}
            </FormSection>;
        },
        renderTextField: function(itemSpecific) {
            return <Field
                name={itemSpecific.name}
                displayTitle={this.formatDisplayTitle(itemSpecific.name)}
                component={this.renderTextInput}
            />
        },
        renderSelectField: function(itemSpecific) {
            return <Field
                name={itemSpecific.name}
                displayTitle={this.formatDisplayTitle(itemSpecific.name)}
                component={this.renderSelectInput}
                options={itemSpecific.options}
            />
        },
        renderChoiceField: function(itemSpecific) {
            var fields = [this.renderChoiceSelectField(itemSpecific)];
            return <span>
                {fields}
            </span>
        },
        renderChoiceSelectField: function(itemSpecific) {
            var options = itemSpecific.children.map((itemSpecific) => {
                return itemSpecific.name;
            });
            return <Field
                name={itemSpecific.name + '.selectedChoice'}
                displayTitle={itemSpecific.name}
                component={this.renderChoiceSelectComponent}
                options={options}
            />;
        },
        renderChoiceSelectComponent: function (field) {
            var options = this.buildSelectOptionsForItemSpecific(field.options);
            return <label className="input-container">
                <span className={"inputbox-label"}>{this.formatDisplayTitle(field.displayTitle)}</span>
                <div className={"order-inputbox-holder"}>
                    <Select
                        autoSelectFirst={false}
                        title={this.formatDisplayTitle(field.displayTitle)}
                        options={options}
                        onOptionChange={this.onChoiceOptionSelected.bind(this, field.input)}
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
        onChoiceOptionSelected: function(input, selectedOption) {
            input.onChange(selectedOption.value);
        },
        renderSequence: function(itemSpecific) {
            return this.renderItemSpecifics(itemSpecific.children, itemSpecific.name);
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
        renderSelectInput: function(field) {
            var SelectComponent = this.isMultiOption(field.options) ? MultiSelect : Select;

            var options = this.buildSelectOptionsForItemSpecific(field.options);

            return <label className="input-container">
                <span className={"inputbox-label"}>{this.formatDisplayTitle(field.displayTitle)}</span>
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
        isMultiOption: function (options) {
            return (options.maxValues && options.maxValues > 1);
        },
        buildSelectOptionsForItemSpecific: function(options) {
            return Object.keys(options).map(value => {
                var optionValue =  options[value];
                return {
                    name: this.formatDisplayTitle(optionValue),
                    value: optionValue
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
                name: value ? this.formatDisplayTitle(value) : '',
                value: value || ''
            };
        },
        formatDisplayTitle: function(name) {
            // Convert camel case space separated words
            name = name.replace(/([A-Z])/g, ' $1');
            // Convert underscores to spaces
            name = name.replace(/_/g, ' ');
            // Ensure single space between words
            return name.replace(/^\s+|\s+$/g, "");
        },
        render: function () {
            return <span>
                {this.renderRoot()}
            </span>
        }
    });

    return AmazonItemSpecifics;
});
