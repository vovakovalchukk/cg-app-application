define([
    'react'
], function(
    React
) {
    "use strict";

    var SelectComponent = React.createClass({
        getDefaultProps: function () {
            return {
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
        render: function () {
            var options = this.props.options.map(function(opt) {
                return (
                    <li className={"custom-select-item "+(opt.selected ? "active" : "")} value={opt.value} key={opt.value} onClick={this.onOptionSelected}>
                        <a value={opt.value}>{opt.name}</a>
                    </li>
                )
            }.bind(this));
            return (
                <div className={"custom-select "+ (this.state.active ? 'active' : '')} onClick={this.onClick}>
                        <div className="selected">
                            <span className="selected-content"><b>{this.props.prefix ? (this.props.prefix + ": ") : ""}</b>{this.state.selectedOption.name}</span>
                            <span className="sprite-arrow-down-10-black">&nbsp;</span>
                        </div>
                        <div className="animated fadeInDown open-content">
                            <ul>
                                {options}
                            </ul>
                        </div>
                </div>
            );
        }
    });

    return SelectComponent;
});