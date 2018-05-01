define([
    'react',
    'redux-form',
    'Common/Components/Select',
    'Common/Components/MultiSelect',
    'Common/Components/Input',
    './CustomItemSpecific'
], function(
    React,
    ReduxForm,
    Select,
    MultiSelect,
    Input,
    CustomItemSpecific
) {
    var Field = ReduxForm.Field;
    var addCustomItemSpecificName = "Add Custom Item Specific";

    return React.createClass({
        getInitialState: function() {
            return {
                categoryId: null,
                optionalItemSpecifics: [],
                selectedItemSpecifics: {},
                customItemSpecifics: [],
                itemSpecificsCounts: {}
            }
        },
        componentWillReceiveProps: function (newProps) {
            this.removeInvalidSelectedItemSpecifics(newProps);
        },
        removeInvalidSelectedItemSpecifics: function(newProps) {
            var selectedItemSpecifics = JSON.parse(JSON.stringify(this.state.selectedItemSpecifics));
            var changes = false;
            for (var title in this.state.selectedItemSpecifics) {
                if (!newProps.itemSpecifics
                    || newProps.itemSpecifics.length === 0
                    || newProps.itemSpecifics.required[title] == undefined && newProps.itemSpecifics.optional[title] == undefined
                ) {
                    delete selectedItemSpecifics[title];
                    changes = true;
                }
            }
            if (!changes) {
                return;
            }

            this.setState({
                selectedItemSpecifics: selectedItemSpecifics
            });
        },
        buildItemSpecificsInputs: function() {
            var itemSpecifics = [];
            var requiredItems = this.props.itemSpecifics.required;
            var optionalItems = this.props.itemSpecifics.optional;

            if (requiredItems && Object.keys(requiredItems).length > 0) {
                var required = [];
                for (var name in requiredItems) {
                    var properties = requiredItems[name];
                    required.push(this.buildItemSpecificsInputByType(name, properties));
                }
                itemSpecifics.push(
                    <span>
                        <label>
                            <span className={"inputbox-label"}><b>Item Specifics (Required)</b></span>
                            <div className={"order-inputbox-holder"}></div>
                        </label>
                        <span>
                            {required}
                        </span>
                    </span>
                );
            }
            if (optionalItems && Object.keys(optionalItems).length > 0) {
                itemSpecifics.push(
                    <label>
                        <span className={"inputbox-label"}><b>Item Specifics (Optional)</b></span>
                        <div className={"order-inputbox-holder"}>
                            <Select
                                name="item-specifics-optional"
                                options={this.buildOptionalItemSpecificsSelectOptions(optionalItems)}
                                autoSelectFirst={false}
                                title="Item Specifics (Optional)"
                                onOptionChange={this.onOptionalItemSpecificSelect}
                            />
                        </div>
                    </label>
                );
            }
            return <span>{itemSpecifics}</span>;
        },
        buildItemSpecificsInputByType: function(name, properties) {
            var builder;
            if (properties.type == 'text') {
                builder = this.buildTextItemSpecific;
            }
            if (properties.type == 'select') {
                builder = this.buildSelectItemSpecific;
            }
            if (properties.type == 'textselect') {
                builder = this.buildTextSelectItemSpecific;
            }
            if (!builder) {
                return null;
            }
            return <Field name={name} component={builder} label={name} options={properties}  />;
        },
        buildTextItemSpecific: function(field) {
            var name = field.label;
            var options = field.options;
            var inputs = [];
            var counts = this.state.itemSpecificsCounts;
            var count = (counts[name]) ? counts[name] : 1;
            var hasPlusButton = this.isMultiOption(options);
            var label = name;

            for (var index = 0; index < count; index++) {
                inputs.push(
                    <label>
                        <span className={"inputbox-label"}>{label}</span>
                        <div className={"order-inputbox-holder"}>
                            <Input
                                name={name}
                                value={this.getItemSpecificTextInputValue(name)}
                                onChange={this.onItemSpecificInputChange.bind(this, index, field.input)}
                            />
                        </div>
                        {hasPlusButton && index === count - 1 ? this.renderPlusButton(name) : null}
                    </label>
                );
                // Only the first of the repeated fields has a label
                label = '';
            }

            return <span>{inputs}</span>;
        },
        renderPlusButton: function (name) {
            return <span className="refresh-icon">
                <i
                    className='fa fa-2x fa-plus-square icon-create-listing'
                    aria-hidden='true'
                    onClick={this.onPlusButtonClick}
                    data-name={name}
                />
            </span>;
        },
        onPlusButtonClick: function (event) {
            var name = event.target.dataset.name;
            var counts = JSON.parse(JSON.stringify(this.state.itemSpecificsCounts));

            counts[name] = (counts[name]) ? counts[name] + 1 : 2;
            this.setState({
                itemSpecificsCounts: counts
            });
        },
        buildCustomItemSpecific: function (item) {
            return <CustomItemSpecific
                index={item.index}
                name={item.name}
                value={item.value}
                categoryId={this.props.categoryId}
                onRemoveButtonClick={this.onRemoveCustomSpecificButtonClick}
                onChange={this.onCustomInputChange}
            />;
        },
        onCustomInputChange: function (index, type, value) {
            var customItemSpecifics = this.state.customItemSpecifics.slice();
            var foundItem = customItemSpecifics.findIndex(function(customItemSpecific) {
                return customItemSpecific.index == index;
            });

            if (foundItem === -1) {
                return;
            }

            customItemSpecifics[foundItem][type] = value;

            if (this.isLastCustomItemSpecific(index)) {
                customItemSpecifics.push(this.getNewCustomItemSpecific());
            }

            this.setState({
                customItemSpecifics: customItemSpecifics
            });
        },
        isLastCustomItemSpecific: function(index) {
            return (index == this.getMaxCustomItemSpecificIndex());
        },
        getMaxCustomItemSpecificIndex: function() {
            return this.state.customItemSpecifics.reduce(function (max, item) {
                return max < item.index ? item.index : max;
            }, -1);
        },
        onRemoveCustomSpecificButtonClick: function (index) {
            var optionalItemSpecifics = this.state.optionalItemSpecifics.slice();
            if (this.state.customItemSpecifics.length === 1) {
                var foundCustomItemSpecific = optionalItemSpecifics.findIndex(function(optionalItemSpecific) {
                    return optionalItemSpecific.name == addCustomItemSpecificName;
                });
                optionalItemSpecifics.splice(foundCustomItemSpecific, 1);
            }
            var foundItem = this.state.customItemSpecifics.findIndex(function(customItemSpecific) {
                return customItemSpecific.index == index;
            });
            if (foundItem > -1) {
                var customItemSpecifics = this.state.customItemSpecifics.slice();

                customItemSpecifics.splice(foundItem, 1);

                this.setState({
                    customItemSpecifics: customItemSpecifics,
                    optionalItemSpecifics: optionalItemSpecifics
                });
            }
        },
        getItemSpecificTextInputValue: function(name) {
            if (this.props.itemSpecifics && this.props.itemSpecifics[name]) {
                return this.props.itemSpecifics[name];
            }
            return null;
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
        buildSelectItemSpecific: function(field) {
            var name = field.label;
            var options = field.options;
            var SelectComponent = this.isMultiOption(options) ? MultiSelect : Select;
            return <label>
                <span className={"inputbox-label"}>{name}</span>
                <div className={"order-inputbox-holder"}>
                    <SelectComponent
                        name={name}
                        options={this.getSelectOptionsForItemSpecific(name, options.options)}
                        autoSelectFirst={false}
                        title={name}
                        onOptionChange={this.onItemSpecificSelected.bind(this, field.input)}
                    />
                </div>
            </label>
        },
        isMultiOption: function (options) {
            return (options.maxValues && options.maxValues > 1);
        },
        getSelectOptionsForItemSpecific(selectName, options) {
            var selectOptions = [];
            for (var optionValue in options) {
                selectOptions.push({
                    "name": options[optionValue],
                    "value": optionValue
                });
            }
            return selectOptions;
        },
        buildTextSelectItemSpecific: function(field) {
            var name = field.label;
            var options = field.options;
            var SelectComponent = this.isMultiOption(options) ? MultiSelect : Select;
            return <label>
                <span className={"inputbox-label"}>{name}</span>
                <div className={"order-inputbox-holder"}>
                    <SelectComponent
                        name={name}
                        options={this.getSelectOptionsForItemSpecific(name, options.options)}
                        autoSelectFirst={false}
                        title={name}
                        customOptions={true}
                        onOptionChange={this.onItemSpecificSelected.bind(this, field.input)}
                        selectedOption={this.findSelectedOption(field.input.value, options.options)}
                    />
                </div>
            </label>
        },
        findSelectedOption: function(value, options) {
            var selectedOption = {
                name: value || '',
                value: value || ''
            };
            for (var optionValue in options) {
                if (optionValue == value) {
                    selectedOption = {
                        "name": options[optionValue],
                        "value": optionValue
                    };
                }
            }
            return selectedOption;
        },
        onOptionalItemSpecificSelect: function (field) {
            // Do no render the same field twice
            for (var index = 0; index < this.state.optionalItemSpecifics.length; index++) {
                if (this.state.optionalItemSpecifics[index].name == field.name) {
                    return;
                }
            }

            if (field.value.type == 'custom') {
                this.addCustomItemSpecific();
            }

            var optionalItemSpecifics = this.state.optionalItemSpecifics.slice();
            optionalItemSpecifics.push(field);
            this.setState({
                optionalItemSpecifics: optionalItemSpecifics
            });
        },
        addCustomItemSpecific: function() {
            var customItemSpecifics = this.state.customItemSpecifics.slice();
            customItemSpecifics.push(this.getNewCustomItemSpecific());
            this.setState({customItemSpecifics: customItemSpecifics});
        },
        getNewCustomItemSpecific: function() {
            return {
                index: this.getMaxCustomItemSpecificIndex() + 1,
                name: '',
                value: ''
            }
        },
        buildOptionalItemSpecificsInputs: function() {
            var itemSpecifics = [];
            var field;
            var optionalItemSpecificsLength = this.state.optionalItemSpecifics.length;
            var customItemSpecifics = this.state.customItemSpecifics;

            for (var key = 0; key < optionalItemSpecificsLength; key++) {
                field = this.state.optionalItemSpecifics[key];
                itemSpecifics.push(
                    this.buildItemSpecificsInputByType(
                        field.name,
                        field.value
                ));
            }

            for (var index in customItemSpecifics) {
                var item = customItemSpecifics[index];
                if (item === undefined) {
                    return;
                }
                itemSpecifics.push(this.buildCustomItemSpecific(item))
            }

            return <span>{itemSpecifics}</span>;
        },
        onItemSpecificSelected: function(input, fields, title) {
            var selectedItemSpecifics = JSON.parse(JSON.stringify(this.state.selectedItemSpecifics));
            var values = [];

            if (!Array.isArray(fields)) {
                fields = [fields];
            }

            selectedItemSpecifics[title] = fields.map(function(item) {
                return item.value;
            });

            input.onChange(selectedItemSpecifics[title]);

            this.setState({
                selectedItemSpecifics: selectedItemSpecifics
            });
        },
        onItemSpecificInputChange: function(index, input, event) {
            var selectedItemSpecifics = JSON.parse(JSON.stringify(this.state.selectedItemSpecifics));

            if (!selectedItemSpecifics[event.target.name]) {
                selectedItemSpecifics[event.target.name] = [];
            }

            selectedItemSpecifics[event.target.name][index] = event.target.value;

            input.onChange(event.target.value);

            this.setState({
                selectedItemSpecifics: selectedItemSpecifics
            });
        },
        render: function () {
            return <span>
                {this.buildItemSpecificsInputs()}
                {this.buildOptionalItemSpecificsInputs()}
            </span>
        }
    });
});
