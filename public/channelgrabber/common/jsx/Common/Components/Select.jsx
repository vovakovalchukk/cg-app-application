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
                options: [],
                autoSelectFirst: true,
                title: null,
                onOptionChange: null
            };
        },
        getInitialState: function () {
            return {
                searchTerm: '',
                inputFocus: false,
                selectedOption: {
                    name: '',
                    value: ''
                },
                active: false,
                options: [],
                disabled: false
            }
        },
        onClickOutside: function () {
            this.setState({
                active: false
            });
        },
        componentDidMount: function() {
            var selectedOption = this.props.selectedOption;
            if (!this.isSelectedOptionAvailable(selectedOption, this.props.options)) {
                selectedOption = this.getDefaultSelectedOption();
            }
            this.setState({
                disabled: this.props.disabled,
                options: this.props.options,
                selectedOption: selectedOption
            });
        },
        componentWillReceiveProps: function (newProps) {
            var newState = {
                disabled: newProps.disabled
            }
            var options = this.state.options;
            if (this.shouldUpdateStateFromProps(newProps)) {
                newState.options = newProps.options;
                options = newProps.options;
            }
            var selectedOption = (newProps.selectedOption && newProps.selectedOption.name !== "" ? newProps.selectedOption : this.state.selectedOption);
            if (!this.isSelectedOptionAvailable(selectedOption, options)) {
                selectedOption = this.getDefaultSelectedOption();
            }
            newState.selectedOption = selectedOption;
            this.setState(newState);
        },
        shouldUpdateStateFromProps: function(newProps) {
            if (!this.props.options) {
                return true;
            }
            for (var i = 0; i < newProps.options.length; i++) {
                if (!this.props.options[i]) {
                    return true;
                }
                if (newProps.options[i].value != this.props.options[i].value || newProps.options[i].name != this.props.options[i].name) {
                    return true;
                }
            }
            return false;
        },
        getDefaultSelectedOption: function() {
            return {name: '', value: ''};
        },
        isSelectedOptionAvailable: function(selectedOption, options) {
            if (selectedOption == undefined || selectedOption.name == '') {
                return true;
            }
            var indexOfSelectedOption = options.findIndex(function(option) {
                return option.name == selectedOption.name;
            });
            return (indexOfSelectedOption > -1);
        },
        onClick: function (e) {
            if (this.state.disabled) {
                return;
            }
            // For certain child elements we dont want to trigger this behaviour when the event bubbles up
            // Note: the use of data attributes is usually not required in React but as this is potentially a bubbled
            // event we have no control over the passed data so we had to resort to it
            var targetDataset = e.nativeEvent.target.dataset;
            if (typeof targetDataset.triggerSelectClick !== undefined && targetDataset.triggerSelectClick == 'false') {
                return;
            }
            var active = this.state.inputFocus ? true : !this.state.active;
            this.setState({
                active: active
            });
        },
        onOptionSelected: function (value) {
            if (this.state.disabled) {
                return;
            }

            var selectedOption = this.state.options.find(function (option) {
                return option.value === value;
            });

            this.setState({
                selectedOption: selectedOption,
                active: false
            }, function() {
                // We need the state to finish updating before we update the parent because that triggers
                // a re-render before the state of this would have been updated and we'll lose it
                this.callBackOnOptionSelectChanged(selectedOption);
            }.bind(this));
        },
        callBackOnOptionSelectChanged: function(selectedOption) {
            if (this.props.onOptionChange) {
                this.props.onOptionChange(selectedOption, this.props.title);
            }
        },
        onInputFocus: function (e) {
            if (this.state.disabled) {
                return;
            }

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
        onCustomOption: function(e) {
            const ENTER_KEY_CODE = 13;
            if (this.state.disabled || e.which !== ENTER_KEY_CODE) {
                return;
            }

            var customOption = {name: e.target.value, value: e.target.value};
            var options = this.state.options.slice();

            options.push(customOption);
            this.clearInput(e.target);

            this.setState({
                options: options,
                selectedOption: customOption,
                active: false
            }, function() {
                this.callBackOnOptionSelectChanged(customOption);
            }.bind(this));
        },
        onFilterResults: function (e) {
            this.setState({
                searchTerm: e.target.value
            });
        },
        clearInput: function(input) {
            input.value = '';
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
            if (this.state.disabled) {
                return [];
            }

            var optionName;
            var className;
            var options = this.state.options.filter(this.filterBySearchTerm).map(function(opt, index) {
                optionName = this.getOptionName(opt.name, opt.value);
                className = '';
                if (opt.disabled) {
                    className = 'disabled';
                } else if (opt.selected) {
                    className = 'active';
                }

                return (
                    <li
                        className={"custom-select-item " + className}
                        value={opt.value} key={index}
                        onClick={this.onOptionSelected.bind(this, opt.value)}
                    >
                        <a value={opt.value} data-trigger-select-click="false">{optionName}</a>
                    </li>
                );
            }.bind(this));

            if (this.props.customOptions) {
                options.unshift(
                    <li className="custom-select-item no-background">
                        <div className="filter-box no-borders">
                            <input
                                onFocus={this.onInputFocus}
                                onBlur={this.onInputBlur}
                                onKeyUp={this.onCustomOption}
                                placeholder={this.state.options.length ? 'Custom Option...' : ''}
                                data-trigger-select-click="false"
                            />
                        </div>
                    </li>
                );
            }

            if (options.length) {
                return options;
            }
            return <div className="results-none">{this.props.filterable ? 'No results' : ''}</div>
        },
        getSelectedOptionName: function () {
            var selectedOptionName = '';
            if (this.state.selectedOption && this.state.selectedOption.name) {
                selectedOptionName = this.state.selectedOption.name
            } else if (this.props.autoSelectFirst) {
                selectedOptionName = this.state.options.length > 0 ? this.state.options[0].name : '';
            }

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
                          placeholder={this.state.options.length ? 'Search...' : ''}
                          data-trigger-select-click="false"
                        />
                    </div>
                );
            }
        },
        render: function () {
            return <ClickOutside onClickOutside={this.onClickOutside}>
                <div className={"custom-select "+ (this.state.active ? 'active' : '')+(this.state.disabled ? 'disabled' : '')} onClick={this.onClick} title={this.props.title}>
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
            </ClickOutside>;
        }
    });

    return SelectComponent;
});