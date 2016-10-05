define([
    'react'
], function(
    React
) {
    "use strict";

    var SelectComponent = React.createClass({
        getDefaultProps: function () {
            return {
                selectedOption: {
                    name: '',
                    value: ''
                },
                options: []
            };
        },
        getInitialState: function () {
            return {
                selectedOption: this.props.selectedOption,
                active: false
            }
        },
        componentWillReceiveProps: function (newProps) {
            if (newProps.selectedOption.name !== "") {
                this.setState({
                    selectedOption: newProps.selectedOption,
                });
            }
        },
        onClick: function (e) {
            this.setState({
                active: !this.state.active
            });
        },
        onOptionSelected: function (e) {
            var selectedOption = this.props.options.find(function (option) {
                return option.value === e.target.value;
            });
            this.setState({
                selectedOption: selectedOption,
            });
            this.props.onNewOption(selectedOption);
        },
        splitOptionNameIntoComponents: function (optionName) {
            var optionComponentArray = optionName.map(function (optionComponent) {
                return <span className="option-component">{optionComponent}</span>
            });
            return optionComponentArray;
        },
        getOptionName: function (optionName) {
            if (Array.isArray(optionName)) {
                return this.splitOptionNameIntoComponents(optionName);
            }
            return optionName;
        },
        getOptionNames: function () {
            var options = this.props.options.map(function(opt, index) {
                var optionName = this.getOptionName(opt.name);

                return (
                    <li className={"custom-select-item "+(opt.selected ? "active" : "")} value={opt.value} key={index} onClick={this.onOptionSelected}>
                        <a value={opt.value}>{optionName}</a>
                    </li>
                )
            }.bind(this));
            return options;
        },
        getSelectedOptionName: function () {
            var selectedOptionName = this.state.selectedOption && this.state.selectedOption.name ? this.state.selectedOption.name : (this.props.options.length > 0 ? this.props.options[0].name : '');
            return this.getOptionName(selectedOptionName);
        },
        render: function () {
            return (
                <div className={"custom-select "+ (this.state.active ? 'active' : '')} onClick={this.onClick}>
                        <div className="selected">
                            <span className="selected-content"><b>{this.props.prefix ? (this.props.prefix + ": ") : ""}</b>{this.getSelectedOptionName()}</span>
                            <span className="sprite-arrow-down-10-black">&nbsp;</span>
                        </div>
                        <div className="animated fadeInDown open-content">
                            <ul>
                                {this.getOptionNames()}
                            </ul>
                        </div>
                </div>
            );
        }
    });

    return SelectComponent;
});