import React from 'react';
import Select from 'Common/Components/Select';
    var StockModeInputsComponent = React.createClass({
        getDefaultProps: function() {
            return {
                stockModeOptions: null,
                onChange: null,
                value: "",
                classNames: null
            };
        },
        stockAmountShouldBeDisabled: function(stockModeTypeValue) {
            return (
                stockModeTypeValue == 'all' ||
                stockModeTypeValue == 'null' ||
                !stockModeTypeValue
            );
        },
        shortenOptions: function(options) {
            var shortenedOptions = [];
            for (var i = 0; i < options.length; i++) {
                var option = options[i];
                if (option.value == 'null') {
                    continue;
                }
                if (option.title.indexOf('List all') > -1) {
                    shortenedOptions.push(option);
                }
                if (option.title.indexOf('List up to a') > -1) {
                    shortenedOptions.push({
                        title: 'List up to',
                        value: option.value
                    })
                }
                if (option.title.indexOf('Fix') > -1) {
                    shortenedOptions.push({
                        title: 'Fixed at',
                        value: option.value
                    });
                }
            }
            return shortenedOptions;
        },
        getClassNames: function() {
            var classNames = 'c-stock-mode-input';
            if (!this.props.classNames) {
                classNames += ' c-stock-mode-input--medium';
            }
            return classNames
        },
        render: function() {
            var shortenedOptions = this.shortenOptions(this.props.stockModeOptions);
            var stockModeOptions = shortenedOptions.map(function(option) {
                return {
                    name: option.title,
                    value: option.value
                }
            });
            return (
                <div className={this.getClassNames()}>
                    <div className={"c-stock-mode-input__type-select-container"}>
                        <Select
                            options={stockModeOptions}
                            autoSelectFirst={true}
                            selectedOption={{
                                name: this.props.stockModeType.input.value.name,
                                value: this.props.stockModeType.input.value.value
                            }}
                            onOptionChange={function(option) {
                                this.props.stockModeType.input.onChange(option)
                            }.bind(this)}
                        />
                    </div>
                    <div className={"c-stock-mode-input__amount-container"}>
                        <input
                            className={'c-input-field c-stock-mode-input__amount_input u-margin-left-xsmall'}
                            name={'stockAmount'}
                            disabled={this.stockAmountShouldBeDisabled(this.props.stockModeType.input.value.value)}
                            type={'number'}
                            value={this.props.stockAmount.input.value}
                            onChange={this.props.stockAmount.input.onChange}
                        />
                    </div>
                </div>
            );
        }
    });

    export default StockModeInputsComponent;

