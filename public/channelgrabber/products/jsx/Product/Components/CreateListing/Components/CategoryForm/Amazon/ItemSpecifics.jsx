define([
    'react',
    'redux-form',
    'Common/Components/Select',
    'Common/Components/MultiSelect',
    'Common/Components/Input',
    '../../../Validators',
    './OptionalItemSpecificsSelect'
], function(
    React,
    ReduxForm,
    Select,
    MultiSelect,
    Input,
    Validators,
    OptionalItemSpecificsSelect
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
                if (!itemSpecific.required) {
                    optional.push(itemSpecific);
                    return;
                }

                fields.push(this.renderItemSpecific(itemSpecific));
            });

            if (optional.length > 0) {
                fields.push(this.renderOptionalItemSpecificSelect(optional))
            }

            return <FormSection name={name}>
                {fields}
            </FormSection>;
        },
        renderItemSpecific: function(itemSpecific) {
            var field;
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
            return field;
        },
        renderTextField: function(itemSpecific) {
            if (this.shouldRenderTextFieldArray(itemSpecific)) {
                return this.renderTextFieldArray(itemSpecific);
            }
            var validator = (itemSpecific.required ? Validators.required : null);
            return <Field
                name={itemSpecific.name}
                displayTitle={this.formatDisplayTitle(itemSpecific.name)}
                component={this.renderTextInput}
                validate={validator}
            />
        },
        shouldRenderTextFieldArray: function (itemSpecific) {
            return this.isMultiOption(itemSpecific);
        },
        renderSelectField: function(itemSpecific) {
            var validator = (itemSpecific.required ? Validators.required : null);
            return <Field
                name={itemSpecific.name}
                displayTitle={this.formatDisplayTitle(itemSpecific.name)}
                component={this.renderSelectInput}
                options={itemSpecific.options}
                validate={validator}
            />
        },
        renderChoiceField: function(itemSpecific) {
            var fields = [this.renderChoiceSelectField(itemSpecific)];
            var selectedItemSpecificName = this.state.selectedChoices[itemSpecific.name];
            if (selectedItemSpecificName) {
                var selectedIndex = itemSpecific.children.findIndex((itemSpecific => {
                    return selectedItemSpecificName == itemSpecific.name;
                }));
                var selectedItemSpecific = itemSpecific.children[selectedIndex];
                fields.push(this.renderItemSpecifics(selectedItemSpecific.children, selectedItemSpecific.name));
            }
            return <FormSection
                name={itemSpecific.name}
                component={this.renderFormSection}
            >
                {fields}
            </FormSection>
        },
        renderFormSection: function (elements) {
            return <div className="form-section-container">
                {elements.children}
            </div>
        },
        renderChoiceSelectField: function(itemSpecific) {
            var options = itemSpecific.children.map((itemSpecific) => {
                return itemSpecific.name;
            });
            return <Field
                name={'selectedChoice'}
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
            this.onOptionSelected(input, selectedOption);
            this.saveChosenValueInState(input, selectedOption);
        },
        saveChosenValueInState: function(input, selectedOption) {
            var name = input.name.split('.').splice(-2, 1);
            var selectedChoices = Object.assign({}, this.state.selectedChoices, {
                [name]: selectedOption.value
            });
            this.setState({
                selectedChoices: selectedChoices
            });
        },
        renderSequence: function(itemSpecific) {
            return <div className="form-section-container">
                <label className="input-container">
                    <span className={"inputbox-label"}>{this.formatDisplayTitle(itemSpecific.name)}</span>
                    <div className={"order-inputbox-holder"}></div>
                </label>
                {this.renderItemSpecifics(itemSpecific.children, itemSpecific.name)}
            </div>
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
        renderTextFieldArray: function(itemSpecific) {
            var validator = (itemSpecific.required ? Validators.required : null);
            return <FieldArray
                name={itemSpecific.name}
                component={this.renderTextFieldArrayComponent}
                displayTitle={this.formatDisplayTitle(itemSpecific.name)}
                validate={validator}
                maxValues={itemSpecific.maxValues}
            />;
        },
        renderTextFieldArrayComponent: function (input) {
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
                        maxValues={input.maxValues}
                    />;
                })}
                {input.meta.error && input.meta.dirty && (
                    <span className="input-error input-array-error">{input.meta.error}</span>
                )}
            </span>;
        },
        getActionButtonForInput: function(field) {
            if (!('index' in field) || !field.fields) {
                return null;
            }
            if (field.index === field.fields.length - 1 && (field.maxValues ? field.maxValues > field.fields.length : true)) {
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
        renderOptionalItemSpecificSelect: function(itemSpecifics) {
            return <FieldArray
                component={this.renderOptionsItemSpecificComponents}
                name={'optionalItemSpecifics'}
                itemSpecifics={itemSpecifics}
                displayTitle={'Optional Item Specifics'}
            />;
        },
        renderOptionsItemSpecificComponents: function(input) {
            var fields = [<OptionalItemSpecificsSelect
                displayTitle={this.formatDisplayTitle(input.displayTitle)}
                options={this.formatOptionalSelectOptions(input.itemSpecifics)}
                input={input}
            />];

            var optionalItemSpecifics = [];
            if (input.fields.length > 0) {
                optionalItemSpecifics = input.fields.map((name) => {
                    return <Field
                        name={name}
                        component={this.renderOptionalItemSpecific}
                        itemSpecifics={input.itemSpecifics}
                    />;
                });
            }

            fields.push(optionalItemSpecifics);

            return <span>
                {fields}
            </span>
        },
        formatOptionalSelectOptions(itemSpecifics) {
            return itemSpecifics.map(itemSpecific => {
                return {
                    name: this.formatDisplayTitle(itemSpecific.name),
                    value: itemSpecific.name
                }
            });
        },
        onOptionalItemSpecificSelected: function (input, selected) {
            input.fields.push({
                fieldName: selected.value
            });
        },
        renderOptionalItemSpecific: function (field) {
            var index = field.itemSpecifics.findIndex(itemSpecific => {
                return itemSpecific.name == field.input.value.fieldName;
            });
            var itemSpecific = field.itemSpecifics[index];
            return this.renderItemSpecific(itemSpecific);
        },
        render: function () {
            return <span>
                {this.renderRoot()}
            </span>
        }
    });

    return AmazonItemSpecifics;
});
