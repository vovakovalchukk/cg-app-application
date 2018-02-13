define(['react', 'Common/Components/ClickOutside'], function(React, ClickOutside) {
    "use strict";

    var MultiSelectComponent = React.createClass({
        getDefaultProps: function () {
            return {
                filterable: false,
                options: [],
                selectedOptions: [],
                title: null,
                onOptionChange: null
            };
        },
        getInitialState: function () {
            return {
                active: false,
                searchTerm: '',
                inputFocus: false,
                selectedOptions: this.props.selectedOptions,
                disabled: false
            }
        },
        componentDidUpdate: function(prevProps, prevState) {
            if (this.props.onOptionChange && prevState.selectedOptions.length !== this.state.selectedOptions.length) {
                this.props.onOptionChange(this.state.selectedOptions);
            }
        },
        onClickOutside: function () {
            this.setState({
                active: false
            });
        },
        onClick: function (e) {
            if (this.state.disabled) {
                return;
            }

            var active = this.state.inputFocus ? true : !this.state.active;
            this.setState({
                active: active
            });
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
        onFilterResults: function (e) {
            this.setState({
                searchTerm: e.target.value
            });
        },
        filterBySearchTerm: function(option) {
            if (!this.props.filterable) {
                return true;
            }
            if (option.name.toUpperCase().includes(this.state.searchTerm.toUpperCase())) {
                return true;
            }
        },
        onOptionSelected: function (option) {
            var selectedOptions = this.state.selectedOptions.slice(0);
            var index = selectedOptions.indexOf(option);

            if (index === -1) {
                selectedOptions.push(option);
            } else {
                selectedOptions.splice(index, 1);
            }

            this.setState({
                selectedOptions: selectedOptions
            });
        },
        onSelectAll: function (e) {
            this.setState({
                selectedOptions: this.props.options.map(function(option, index) {
                    return option.value;
                })
            });
        },
        onClearAll: function (e) {
            this.setState({
                selectedOptions: []
            });
        },
        getSelected: function () {
            var optionHash = {};
            for (var index in this.props.options) {
                if (this.props.options.hasOwnProperty(index)) {
                    var option = this.props.options[index];
                    optionHash[option.value] = option.name;
                }
            }

            return this.state.selectedOptions.map(function(option, index) {
                return optionHash[option];
            }).join(", ");
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
        getActions: function () {
            return <div className="custom-select-actions">
                <a className="select-all" onClick={this.onSelectAll}>Select All</a>
                <a className="clear-action" onClick={this.onClearAll}>Clear Selected</a>
            </div>;
        },
        getOptions: function () {
            if (this.state.disabled) {
                return [];
            }

            var options = this.props.options.filter(this.filterBySearchTerm).map(function(option, index) {
                return <li className="custom-select-item">
                    <a className="std-checkbox" onClick={this.onOptionSelected.bind(this, option.value)}>
                        <input type="checkbox" value={option.value} checked={this.state.selectedOptions.indexOf(option.value) !== -1}/>
                        <label>
                            <span className="checkbox_label">{option.name}</span>
                        </label>
                    </a>
                </li>;
            }.bind(this));

            if (options.length) {
                return options;
            }

            return <div className="results-none">{this.props.filterable ? 'No results' : ''}</div>
        },
        render: function () {
            return <ClickOutside onClickOutside={this.onClickOutside}>
                <div className={"custom-select custom-select-group large "+ (this.state.active ? 'active' : '')} title={this.props.title}>
                    <div className="selected" onClick={this.onClick}>
                        <span className="selected-content"><b>{this.props.prefix ? (this.props.prefix + ": ") : ""}</b>{this.getSelected()}</span>
                        <span className="sprite-arrow-down-10-black">&nbsp;</span>
                    </div>
                    <div className="animated fadeInDown open-content">
                        {this.getFilterBox()}
                        {this.getActions()}
                        <ul className="custom-select-checkboxes">
                            {this.getOptions()}
                        </ul>
                    </div>
                </div>
            </ClickOutside>;
        }
    });

    return MultiSelectComponent;
});