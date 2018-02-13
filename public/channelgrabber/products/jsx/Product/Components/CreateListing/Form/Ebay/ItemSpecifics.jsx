define([
    'react',
    'Common/Components/Select',
    'Common/Components/Input'
], function(
    React,
    Select,
    Input
) {
    return React.createClass({
        getDefaultProps: function() {
            return {
                title: null,
                description: null,
                price: null,
                accountId: null,
                product: null,
                ean: null
            }
        },
        getInitialState: function() {
            return {
                error: false,
                settingsFetched: false,
                shippingServiceFieldValues: {},
                currencyFieldValues: {},
                shippingService: null,
                rootCategories: null,
                availableSites: {},
                listingDurationFieldValues: null,
                itemSpecifics: {},
                optionalItemSpecifics: [],
                optionalItemSpecificsSelectOptions: [],
                selectedItemSpecifics: {},
                customItemSpecificCount: 0
            }
        },
        buildItemSpecificsInputs: function() {
            var itemSpecifics = [];
            if (this.state.itemSpecifics.required) {
                var required = [];
                var hasPlusButton;
                $.each(this.state.itemSpecifics.required, function (name, properties) {
                    required.push(this.buildItemSpecificsInputByType(name, properties));
                }.bind(this));
                itemSpecifics.push(<span>Item Specifics (Required){required}</span>);
            }
            if (this.state.itemSpecifics.optional) {
                itemSpecifics.push(
                    <label>
                        <span className={"inputbox-label"}><b>Item Specifics (Optional)</b></span>
                        <div className={"order-inputbox-holder"}>
                            <Select
                                name="item-specifics-optional"
                                options={this.buildOptionalItemSpecificsSelectOptions(this.state.itemSpecifics.optional)}
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
            if (properties.type == 'custom') {
                return this.buildCustomItemSpecific(properties);
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
                {this.renderPlusButton(name, hasPlusButton)}
            </label>
        },
        renderPlusButton: function (name, shouldRender = false) {
            if (!shouldRender) {
                return null;
            }
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
        buildCustomItemSpecific: function () {
            var customInputName = 'CustomInputName' + this.state.customItemSpecificCount;
            var customInputValueName = 'CustomInputValueName' + this.state.customItemSpecificCount;
            var itemSpecific = <label>
                <span className={"inputbox-label container-extra-item-specific"}>
                    <Input
                        name={customInputName}
                    />
                </span>
                <div className={"order-inputbox-holder"}>
                    <Input
                        name={customInputValueName}
                    />
                </div>
                {this.renderPlusButton(customInputName, true)}
            </label>;
            this.setState({customItemSpecificCount: this.state.customItemSpecificCount + 1});
            return itemSpecific;
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

            var optionalItemSpecifics = JSON.parse(JSON.stringify(this.state.optionalItemSpecifics));
            optionalItemSpecifics.push(field);
            this.setState({
                optionalItemSpecifics: optionalItemSpecifics
            });
        },
        buildOptionalItemSpecificsInputs: function() {
            var itemSpecifics = [];
            var field;
            var optionalItemSpecificsLenght = this.state.optionalItemSpecifics.length;
            for (var key = 0; key < optionalItemSpecificsLenght; key++) {
                field = this.state.optionalItemSpecifics[key];
                itemSpecifics.push(this.buildItemSpecificsInputByType(
                    field.name,
                    field.value
                ));
            }
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
            this.state.itemSpecifics = this.props.itemSpecifics;
            return <span>
                {(this.state.itemSpecifics) ? this.buildItemSpecificsInputs() : null}
                {(this.state.optionalItemSpecifics) ? this.buildOptionalItemSpecificsInputs() : null}
            </span>
        }
    });
});
