define([
    'react',
    'Common/Components/ClickOutside'
], function(
    React,
    ClickOutside
) {
    "use strict";

    var SelectComponent = React.createClass({
        getDefaultProps: function () {
            return {
                filterable: false,
                selectedOption: {
                    name: '',
                    value: ''
                },
                options: []
            };
        },
        getInitialState: function () {
            return {
                searchTerm: '',
                inputFocus: false,
                selectedOption: this.props.selectedOption,
                active: false
            }
        },
        onClickOutside: function () {
            this.setState({
                active: false
            });
        },
        componentWillReceiveProps: function (newProps) {
            if (newProps.selectedOption.name !== "") {
                this.setState({
                    selectedOption: newProps.selectedOption,
                });
            }
        },
        onClick: function (e) {
            var active = this.state.inputFocus ? true : !this.state.active;
            this.setState({
                active: active
            });
        },
        onOptionSelected: function (e) {
            var selectedOption = this.props.options.find(function (option) {
                return option.value === e.target.value;
            });
            this.setState({
                selectedOption: selectedOption,
            });
            this.props.onOptionChange(selectedOption);
        },
        onInputFocus: function (e) {
            this.setState({
                active: true,
                inputFocus: true
            });
        },
        onInputBlur: function (e) {
            this.setState({
                inputFocus: false
            });
        },
        onFilterResults: function (e) {
            this.setState({
                searchTerm: e.target.value
            });
        },
        filterBySearchTerm: function(option) {
            if (! this.props.filterable) {
                return true;
            }
            if (option.name.toUpperCase().includes(this.state.searchTerm.toUpperCase())) {
                return true;
            }
        },
        splitOptionNameIntoComponents: function (optionName, optionValue) {
            var optionComponentArray = optionName.map(function (optionComponent) {
                return <span className="option-component" value={optionValue}>{optionComponent}</span>
            });
            return optionComponentArray;
        },
        getOptionName: function (optionName, optionValue) {
            if (Array.isArray(optionName)) {
                return this.splitOptionNameIntoComponents(optionName, optionValue);
            }
            return optionName;
        },
        getOptionNames: function () {
            var options = this.props.options.filter(this.filterBySearchTerm).map(function(opt, index) {
                var optionName = this.getOptionName(opt.name, opt.value);

                return (
                    <li className={"custom-select-item "+(opt.selected ? "active" : "")} value={opt.value} key={index} onClick={this.onOptionSelected}>
                        <a value={opt.value}>{optionName}</a>
                    </li>
                )
            }.bind(this));

            if (options.length) {
                return options;
            }
            return <div className="results-none">{this.props.filterable ? 'No results' : ''}</div>
        },
        getSelectedOptionName: function () {
            var selectedOptionName = this.state.selectedOption && this.state.selectedOption.name ? this.state.selectedOption.name : (this.props.options.length > 0 ? this.props.options[0].name : '');
            return this.getOptionName(selectedOptionName);
        },
        getFilterBox: function () {
            if (this.props.filterable) {
                return (
                    <div className="filter-box">
                            <input
                              onFocus={this.onInputFocus}
                              onBlur={this.onInputBlur}
                              onChange={this.onFilterResults}
                              placeholder={this.props.options.length ? 'Search...' : ''}
                            />
                    </div>
                );
            }
        },
        render: function () {
            return (
                <ClickOutside onClickOutside={this.onClickOutside}>
                    <div className={"custom-select "+ (this.state.active ? 'active' : '')} onClick={this.onClick}>
                            <div className="selected">
                                <span className="selected-content"><b>{this.props.prefix ? (this.props.prefix + ": ") : ""}</b>{this.getSelectedOptionName()}</span>
                                <span className="sprite-arrow-down-10-black">&nbsp;</span>
                            </div>
                            <div className="animated fadeInDown open-content">
                                {this.getFilterBox()}
                                <ul>
                                    {this.getOptionNames()}
                                </ul>
                            </div>
                    </div>
                </ClickOutside>
            );
        }
    });

    return SelectComponent;
});