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
                currentSelection: {},
                active: false
            }
        },
        onClick: function (e) {
            this.setState({
                active: !this.state.active
            });
        },
        onOptionSelected: function (e) {
            var selectedOption = this.props.options.find(function (option) {
                if (option.value === e.target.value) {
                    return option;
                }
            });
            this.setState({
                currentSelection: selectedOption
            });
            this.props.onNewOption(selectedOption);
        },
        render: function () {
            var selectedOption = "";
            var options = this.props.options.map(function(opt) {
                if (opt.selected) {
                    selectedOption = opt;
                }
                return (
                    <li className={"custom-select-item "+(opt.selected ? "active" : "")} value={opt.value} key={opt.value} onClick={this.onOptionSelected}>
                        <a value={opt.value}>{opt.name}</a>
                    </li>
                )
            }.bind(this));
            return (
                <div className={"custom-select "+ (this.state.active ? 'active' : '')} onClick={this.onClick}>
                        <div className="selected">
                            <span className="selected-content"><b>{this.props.prefix ? (this.props.prefix + ": ") : ""}</b>{(this.state.currentSelection.name ? this.state.currentSelection.name : selectedOption.name)}</span>
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