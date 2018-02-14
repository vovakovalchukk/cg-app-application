define([
    'react',
    'Common/Components/Select',
    'Common/Components/Input',
    'Product/Components/CreateListing/Form/Ebay/CustomItemSpecific'
], function(
    React,
    Select,
    Input,
    CustomItemSpecific
) {
    return React.createClass({
        getInitialState: function() {
            return {
                itemSpecifics: {},
                optionalItemSpecifics: [],
                optionalItemSpecificsSelectOptions: [],
                selectedItemSpecifics: {},
                customItemSpecifics: []
            }
        },
        buildItemSpecificsInputs: function() {
            var itemSpecifics = [], requiredItems, optionalItems;

            if (requiredItems = this.props.itemSpecifics.required) {
                var required = [], item;
                $.each(requiredItems, function (name, properties) {
                    required.push(this.buildItemSpecificsInputByType( name, properties));
                }.bind(this));
                itemSpecifics.push(<span>Item Specifics (Required){required}</span>);
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
        buildTextItemSpecific: function(name, options, hasPlusButton = false) {
            hasPlusButton = (options.maxValues && options.maxValues > 1);
            return <label>
                <span className={"inputbox-label"}>{name}</span>
                <div className={"order-inputbox-holder"}>
                    <Input
                        name={name}
                        value={this.getItemSpecificTextInputValue(name)}
                        onChange={this.onItemSpecificInputChange}
                    />
                </div>
                {hasPlusButton ? this.renderPlusButton(name) : null}
            </label>
        },
        renderPlusButton: function (name) {
            return <span className="refresh-icon">
                <i
                    className='fa fa-2x fa-plus-square icon-create-listing'
                    aria-hidden='true'
                    onClick={this.onPlusButtonClick}
                    data={name}
                />
            </span>;
        },
        onPlusButtonClick: function (item) {
            console.log(item);
        },
        buildCustomItemSpecific: function (index) {
            return <CustomItemSpecific
                index={index}
                onRemoveButtonClick={this.onRemoveCustomSpecificButtonClick}
                onChange={this.onCustomInputChange}
            />;
        },
        onCustomInputChange: function (index) {
            if (index !== this.state.customItemSpecifics.length - 1) {
                return;
            }
            this.addCustomItemSpecific(index + 1);
        },
        onRemoveCustomSpecificButtonClick: function (index) {
            console.log(index);
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
            return <label>
                <span className={"inputbox-label"}>{name}</span>
                <div className={"order-inputbox-holder"}>
                    <Select
                        name="duration"
                        options={this.getSelectOptionsForItemSpecific(name, options.options)}
                        autoSelectFirst={false}
                        title={name}
                        onOptionChange={this.onItemSpecificSelected}
                    />
                </div>
            </label>
        },
        getSelectOptionsForItemSpecific(selectName, options) {
            var selectOptions = [];
            $.each(options, function(optionValue, optionName) {
                selectOptions.push({
                    "name": optionName,
                    "value": {
                        "value": optionValue,
                        "selectName": selectName
                    }
                });
            });
            return selectOptions;
        },
        buildTextSelectItemSpecific: function(name, options) {
            return <label>
                <span className={"inputbox-label"}>{name}</span>
                <div className={"order-inputbox-holder"}>
                    <Select
                        name="duration"
                        options={this.getSelectOptionsForItemSpecific(name, options.options)}
                        autoSelectFirst={false}
                        title={name}
                        onOptionChange={this.onItemSpecificSelected}
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

            var optionalItemSpecifics = JSON.parse(JSON.stringify(this.state.optionalItemSpecifics));
            optionalItemSpecifics.push(field);
            this.setState({
                optionalItemSpecifics: optionalItemSpecifics
            });
        },
        addCustomItemSpecific: function() {
            var customSpecificCount = this.state.customItemSpecifics.length,
                nextIndex = customSpecificCount + 1,
                customItemSpecifics = JSON.parse(JSON.stringify(this.state.customItemSpecifics));

            customItemSpecifics.push({nextIndex: nextIndex});

            this.setState({
                customItemSpecifics: customItemSpecifics
            });
        },
        buildOptionalItemSpecificsInputs: function() {
            var itemSpecifics = [],
                field,
                optionalItemSpecificsLenght = this.state.optionalItemSpecifics.length,
                customItemSpecifics = this.state.customItemSpecifics;

            for (var key = 0; key < optionalItemSpecificsLenght; key++) {
                field = this.state.optionalItemSpecifics[key];
                itemSpecifics.push(
                    this.buildItemSpecificsInputByType(
                        key,
                        field.name,
                        field.value
                ));
            }

            $.each(customItemSpecifics, function (key, value) {
                itemSpecifics.push(this.buildCustomItemSpecific(key))
            }.bind(this));

            return <span>{itemSpecifics}</span>;
        },
        onItemSpecificSelected: function(field) {
            var selectedItemSpecifics = this.state.selectedItemSpecifics;
            this.state.selectedItemSpecifics[field.value.selectName] = field.value.value;
            this.setState({
                selectedItemSpecifics: this.state.selectedItemSpecifics
            });
            this.props.setFormStateListing({'itemSpecifics': this.state.selectedItemSpecifics});
        },
        onItemSpecificInputChange: function(event) {
            var selectedItemSpecifics = this.state.selectedItemSpecifics;
            selectedItemSpecifics[event.target.name] = event.target.value;
            this.setState({
                selectedItemSpecifics: this.state.selectedItemSpecifics
            });
            this.props.setFormStateListing({'itemSpecifics': this.state.selectedItemSpecifics});
        },
        render: function () {
            return <span>
                {this.buildItemSpecificsInputs()}
                {this.buildOptionalItemSpecificsInputs()}
            </span>
        }
    });
});
