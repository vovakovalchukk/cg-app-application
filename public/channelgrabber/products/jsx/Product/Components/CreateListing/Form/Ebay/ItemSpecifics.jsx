define([
    'react',
    'Common/Components/Select',
    'Common/Components/MultiSelect',
    'Common/Components/Input',
    'Product/Components/CreateListing/Form/Ebay/CustomItemSpecific'
], function(
    React,
    Select,
    MultiSelect,
    Input,
    CustomItemSpecific
) {
    return React.createClass({
        getInitialState: function() {
            return {
                optionalItemSpecifics: [],
                optionalItemSpecificsSelectOptions: [],
                selectedItemSpecifics: {},
                customItemSpecifics: [],
                itemSpecificsCount: {}
            }
        },
        buildItemSpecificsInputs: function() {
            var itemSpecifics = [], requiredItems, optionalItems;

            if (requiredItems = this.props.itemSpecifics.required) {
                var required = [], item;
                $.each(requiredItems, function (name, properties) {
                    required.push(this.buildItemSpecificsInputByType(name, properties));
                }.bind(this));
                itemSpecifics.push(<span><b>Item Specifics (Required)</b>{required}</span>);
            }
            if (optionalItems = this.props.itemSpecifics.optional) {
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
            if (properties.type == 'text') {
                return this.buildTextItemSpecific(name, properties);
            }
            if (properties.type == 'select') {
                return this.buildSelectItemSpecific(name, properties);
            }
            if (properties.type == 'textselect') {
                return this.buildTextSelectItemSpecific(name, properties);
            }
        },
        buildTextItemSpecific: function(name, options) {
            var inputs = [],
                counts = this.state.itemSpecificsCount,
                count = (counts[name]) ? counts[name] : 1,
                hasPlusButton = this.isMultiOption(options),
                displayName = name;

            for (var i = 0; i < count; i++) {
                inputs.push(
                    <label>
                        <span className={"inputbox-label"}>{displayName}</span>
                        <div className={"order-inputbox-holder"} data-index={i}>
                            <Input
                                name={name}
                                value={this.getItemSpecificTextInputValue(name)}
                                onChange={this.onItemSpecificInputChange}
                            />
                        </div>
                        {hasPlusButton && i === count - 1 ? this.renderPlusButton(name) : null}
                    </label>
                );
                displayName = '';
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
            var name = event.target.dataset.name,
                counts = JSON.parse(JSON.stringify(this.state.itemSpecificsCount));

            counts[name] = (counts[name]) ? counts[name] + 1 : 2;
            this.setState({
                itemSpecificsCount: counts
            });
        },
        buildCustomItemSpecific: function (item) {
            return <CustomItemSpecific
                index={item.index}
                name={item.name}
                value={item.value}
                onRemoveButtonClick={this.onRemoveCustomSpecificButtonClick}
                onChange={this.onCustomInputChange}
            />;
        },
        onCustomInputChange: function (index, type, value) {
            var customSpecifics = this.state.customItemSpecifics.slice(),
                foundItem = customSpecifics.findIndex(i => i.index == index),
                selectedItemSpecifics = JSON.parse(JSON.stringify(this.state.selectedItemSpecifics));

            if (foundItem === -1) {
                return;
            }

            customSpecifics[foundItem][type] = value;

            this.setState({
                customItemSpecifics: customSpecifics,
                selectedItemSpecifics: selectedItemSpecifics
            });

            if (index === this.getMaxCustomItemSpecificIndex()) {
                this.addCustomItemSpecific();
            }
        },
        getMaxCustomItemSpecificIndex: function() {
            return this.state.customItemSpecifics.reduce(function (max, item) {
                return max < item.index ? item.index : max;
            }, -1);
        },
        onRemoveCustomSpecificButtonClick: function (index) {
            if (this.state.customItemSpecifics.length === 1) {
                return;
            }
            var foundItem = this.state.customItemSpecifics.findIndex(i => i.index == index);
            if (foundItem > -1) {
                var newCustomItemSpecifics = this.state.customItemSpecifics.slice(),
                    selectedItemSpecifics = JSON.parse(JSON.stringify(this.state.selectedItemSpecifics));

                newCustomItemSpecifics.splice(foundItem, 1);
                delete selectedItemSpecifics[this.state.customItemSpecifics[foundItem].name];
                this.setState({
                    customItemSpecifics: newCustomItemSpecifics,
                    selectedItemSpecifics: selectedItemSpecifics
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
            $.each(itemSpecifics, function (name, value) {
                options.push({
                    "name": name,
                    "value": value
                })
            });
            options.push({
                "name": "Add Custom Item Specific",
                "value": {type: 'custom'}
            });
            return options;
        },
        buildSelectItemSpecific: function(name, options) {
            var SelectComponent = this.isMultiOption(options) ? MultiSelect : Select;
            return <label>
                <span className={"inputbox-label"}>{name}</span>
                <div className={"order-inputbox-holder"}>
                    <SelectComponent
                        options={this.getSelectOptionsForItemSpecific(name, options.options)}
                        autoSelectFirst={false}
                        title={name}
                        onOptionChange={this.onItemSpecificSelected}
                    />
                </div>
            </label>
        },
        isMultiOption: function (options) {
            return (options.maxValues && options.maxValues > 1);
        },
        getSelectOptionsForItemSpecific(selectName, options) {
            var selectOptions = [];
            $.each(options, function(optionValue, optionName) {
                selectOptions.push({
                    "name": optionName,
                    "value": optionValue
                });
            });
            return selectOptions;
        },
        buildTextSelectItemSpecific: function(name, options) {
            var SelectComponent = this.isMultiOption(options) ? MultiSelect : Select;
            return <label>
                <span className={"inputbox-label"}>{name}</span>
                <div className={"order-inputbox-holder"}>
                    <SelectComponent
                        options={this.getSelectOptionsForItemSpecific(name, options.options)}
                        autoSelectFirst={false}
                        title={name}
                        customOptions={true}
                        onOptionChange={this.onMultiItemSpecificSelected}
                    />
                </div>
            </label>
        },
        onOptionalItemSpecificSelect: function (field) {
            // Do no render the same field twice
            for (var i = 0; i < this.state.optionalItemSpecifics.length; i++) {
                if (this.state.optionalItemSpecifics[i].name == field.name) {
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
            customItemSpecifics.push({
                index: this.getMaxCustomItemSpecificIndex()+ 1,
                name: '',
                value: ''
            });
            this.setState({
                customItemSpecifics: customItemSpecifics
            });
        },
        buildOptionalItemSpecificsInputs: function() {
            var itemSpecifics = [],
                field,
                optionalItemSpecificsLenght = this.state.optionalItemSpecifics.length,
                customItemSpecifics = this.state.customItemSpecifics

            for (var key = 0; key < optionalItemSpecificsLenght; key++) {
                field = this.state.optionalItemSpecifics[key];
                itemSpecifics.push(
                    this.buildItemSpecificsInputByType(
                        field.name,
                        field.value
                ));
            }

            $.each(customItemSpecifics, function (index, item) {
                if (item === undefined) {
                    return;
                }
                itemSpecifics.push(this.buildCustomItemSpecific(item))
            }.bind(this));

            return <span>{itemSpecifics}</span>;
        },
        onItemSpecificSelected: function(field) {
            var selectedItemSpecifics = this.state.selectedItemSpecifics;
            this.state.selectedItemSpecifics[field.name] = field.value;
            this.setState({
                selectedItemSpecifics: this.state.selectedItemSpecifics
            });
            this.props.setFormStateListing({'itemSpecifics': this.state.selectedItemSpecifics});
        },
        onMultiItemSpecificSelected: function (fields, title) {
            var selectedItemSpecifics = JSON.parse(JSON.stringify(this.state.selectedItemSpecifics)),
                values = [];

            selectedItemSpecifics[title] = fields;

            this.props.setFormStateListing({'itemSpecifics': selectedItemSpecifics});
            this.setState({
                selectedItemSpecifics: selectedItemSpecifics
            });
        },
        onItemSpecificInputChange: function(event) {
            var selectedItemSpecifics = JSON.parse(JSON.stringify(this.state.selectedItemSpecifics)),
                index = event.target.parentElement.parentElement.dataset.index;

            if (!selectedItemSpecifics[event.target.name]) {
                selectedItemSpecifics[event.target.name] = [];
            }

            selectedItemSpecifics[event.target.name][index] = event.target.value;

            this.props.setFormStateListing({'itemSpecifics': selectedItemSpecifics});
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
