import React from 'react';
import {Field, FieldArray, FormSection} from 'redux-form';
import Select from 'Common/Components/Select';
import MultiSelect from 'Common/Components/MultiSelect';
import Input from 'Common/Components/Input';
import Validators from '../../../Validators';
import OptionalItemSpecificsSelect from './OptionalItemSpecificsSelect';

const REQUIRED_ITEM_SPECIFICS = {
    'ProductType': 'ProductType'
};

class AmazonItemSpecifics extends React.Component {
    static defaultProps = {
        itemSpecifics: {}
    };

    state = {
        selectedChoices: {}
    };

    renderRoot = () => {
        if (Object.keys(this.props.itemSpecifics).length === 0 || !(0 in this.props.itemSpecifics)) {
            return null;
        }
        var rootItemSpecific = this.props.itemSpecifics[0];
        return this.renderItemSpecifics(rootItemSpecific.children, rootItemSpecific.name);
    };

    renderItemSpecifics = (itemSpecifics, name) => {
        var optional = [],
            fields = [];

        itemSpecifics.forEach((itemSpecific) => {
            itemSpecific = this.formatItemSpecificForRendering(itemSpecific);
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
    };

    renderItemSpecific = (itemSpecific) => {
        var functionName = 'render' + itemSpecific.type.ucfirst() + 'Field';
        return typeof this[functionName] == 'function' ? this[functionName](itemSpecific) : null;
    };

    formatItemSpecificForRendering = (itemSpecific) => {
        if (itemSpecific.name && REQUIRED_ITEM_SPECIFICS[itemSpecific.name]) {
            itemSpecific.required = true;
        }
        return itemSpecific;
    };

    renderTextField = (itemSpecific) => {
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
    };

    shouldRenderTextFieldArray = (itemSpecific) => {
        return this.isMultiOption(itemSpecific);
    };

    renderSelectField = (itemSpecific) => {
        var validator = (itemSpecific.required ? Validators.required : null);
        return <Field
            name={itemSpecific.name}
            displayTitle={this.formatDisplayTitle(itemSpecific.name)}
            component={this.renderSelectInput}
            options={itemSpecific.options}
            validate={validator}
        />
    };

    renderChoiceField = (itemSpecific) => {
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
    };

    renderFormSection = (elements) => {
        return <div className="form-section-container">
            {elements.children}
        </div>
    };

    renderChoiceSelectField = (itemSpecific) => {
        var options = itemSpecific.children.map((itemSpecific) => {
            return itemSpecific.name;
        });

        return <Field
            name={'selectedChoice'}
            displayTitle={itemSpecific.name}
            component={this.renderChoiceSelectComponent}
            options={options}
            validate={itemSpecific.required ? Validators.required : null}
        />;
    };

    renderChoiceSelectComponent = (field) => {
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
    };

    onChoiceOptionSelected = (input, selectedOption) => {
        this.onOptionSelected(input, selectedOption);
        this.saveChosenValueInState(input, selectedOption);
    };

    saveChosenValueInState = (input, selectedOption) => {
        var name = input.name.split('.').splice(-2, 1);
        var selectedChoices = Object.assign({}, this.state.selectedChoices, {
            [name]: selectedOption.value
        });
        this.setState({
            selectedChoices: selectedChoices
        });
    };

    renderSequenceField = (itemSpecific) => {
        return <div className="form-section-container">
            <label className="input-container">
                <span className={"inputbox-label"}>{this.formatDisplayTitle(itemSpecific.name)}</span>
                <div className={"order-inputbox-holder"}></div>
            </label>
            {this.renderItemSpecifics(itemSpecific.children, itemSpecific.name)}
        </div>
    };

    renderTextInput = (field) => {
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
    };

    renderTextFieldArray = (itemSpecific) => {
        var validator = (itemSpecific.required ? Validators.required : null);
        return <FieldArray
            name={itemSpecific.name}
            component={this.renderTextFieldArrayComponent}
            displayTitle={this.formatDisplayTitle(itemSpecific.name)}
            validate={validator}
            maxValues={itemSpecific.maxValues}
        />;
    };

    renderTextFieldArrayComponent = (input) => {
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
    };

    getActionButtonForInput = (field) => {
        if (!('index' in field) || !field.fields) {
            return null;
        }
        if (this.isLastField(field)) {
            return this.renderPlusButton(() => field.fields.push(""));
        }
        return this.renderRemoveButton(() => field.fields.remove(field.index));
    };

    isLastField = (field) => {
        if (field.maxValues && field.maxValues <= field.fields.length) {
            return false;
        }
        return field.index === field.fields.length - 1;
    };

    renderSelectInput = (field) => {
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
    };

    isMultiOption = (options) => {
        return (options.maxValues && options.maxValues > 1);
    };

    renderPlusButton = (onClick) => {
        return <span className="refresh-icon">
            <i
                className='fa fa-2x fa-plus-square icon-create-listing'
                aria-hidden='true'
                onClick={onClick}
            />
        </span>;
    };

    renderRemoveButton = (onClick) => {
        return <span className="remove-icon">
            <i
                className='fa fa-2x fa-minus-square icon-create-listing'
                aria-hidden='true'
                onClick={onClick}
            />
        </span>;
    };

    buildSelectOptionsForItemSpecific = (options) => {
        return Object.keys(options).map(value => {
            var optionValue =  options[value];
            return {
                name: this.formatDisplayTitle(optionValue),
                value: optionValue
            }
        });
    };

    onOptionSelected = (input, selectedOptions) => {
        if (selectedOptions instanceof Array) {
            input.onChange(selectedOptions.map(option => option.value));
        }
        input.onChange(selectedOptions.value);
    };

    findSelectedOption = (value) => {
        value = value instanceof Array ? null : value;
        return {
            name: value ? this.formatDisplayTitle(value) : '',
            value: value || ''
        };
    };

    formatDisplayTitle = (name) => {
        // Convert camel case space separated words
        name = name.replace(/([A-Z])/g, ' $1');
        // Convert underscores to spaces
        name = name.replace(/_/g, ' ');
        // Ensure single space between words
        return name.replace(/^\s+|\s+$/g, "");
    };

    renderOptionalItemSpecificSelect = (itemSpecifics) => {
        return <FieldArray
            component={this.renderOptionsItemSpecificComponents}
            name={'optionalItemSpecifics'}
            itemSpecifics={itemSpecifics}
            displayTitle={'Optional Item Specifics'}
        />;
    };

    renderOptionsItemSpecificComponents = (input) => {
        var fields = [<OptionalItemSpecificsSelect
            displayTitle={this.formatDisplayTitle(input.displayTitle)}
            options={this.formatOptionalSelectOptions(input.itemSpecifics, input.fields.getAll())}
            input={input}
        />];

        if (input.fields.length > 0) {
            input.fields.forEach((name) => {
                fields.push(<Field
                    name={name}
                    component={this.renderOptionalItemSpecific.bind(this)}
                    itemSpecifics={input.itemSpecifics}
                />)
            });
        }

        return <span>
            {fields}
        </span>
    };

    formatOptionalSelectOptions = (itemSpecifics, fieldValues) => {
        var options = [];
        itemSpecifics.forEach(itemSpecific => {
            if (options.findIndex(option => {return option.value === itemSpecific.name}) > -1) {
                return;
            }
            options.push({
                name: this.formatDisplayTitle(itemSpecific.name),
                value: itemSpecific.name
            });
        });

        return fieldValues ? this.filterOutSelectedOptions(options, fieldValues) : options;
    };

    filterOutSelectedOptions = (options, fieldValues) => {
        fieldValues.forEach(field => {
            var index = options.findIndex(option => {
                return option.value == field.fieldName;
            });
            if (index > -1) {
                options.splice(index, 1);
            }
        });
        return options;
    };

    renderOptionalItemSpecific = (field) => {
        var index = field.itemSpecifics.findIndex(itemSpecific => {
            return itemSpecific.name == field.input.value.fieldName;
        });
        var itemSpecific = field.itemSpecifics[index];
        return this.renderItemSpecific(itemSpecific);
    };

    render() {
        return <span>
            {this.renderRoot()}
        </span>
    }
}

export default AmazonItemSpecifics;

