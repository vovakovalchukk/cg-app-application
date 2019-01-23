import React from 'react';
import Select from 'Common/Components/Select';

class StockModeInputsComponent extends React.Component {
    static defaultProps = {
        stockModeOptions: null,
        stockModeType: '',
        onChange: null,
        value: "",
        classNames: null,
        className: '',
        portalSettingsForSelect: {}
    };

    stockAmountShouldBeDisabled = (stockModeTypeValue) => {
        return (
            stockModeTypeValue == 'all' ||
            stockModeTypeValue == 'null' ||
            !stockModeTypeValue
        );
    };

    shortenOptions = (options) => {
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
    };

    render() {
        let shortenedOptions = this.shortenOptions(this.props.stockModeOptions);
        let stockModeOptions = shortenedOptions.map(function(option) {
            return {
                name: option.title,
                value: option.value
            }
        });

        let selected = this.props.stockModeType.input.value;

        let selectedNameFromValue = '';
        if (selected.value) {
            selectedNameFromValue = stockModeOptions.find(option => {
                return option.value === selected.value;
            }).name;
        }

        return (
            <div className={this.props.className}>
                <div className={"c-stock-mode-input__type-select-container"}>
                    <Select
                        options={stockModeOptions}
                        autoSelectFirst={true}
                        selectedOption={{
                            name: selectedNameFromValue,
                            value: this.props.stockModeType.input.value.value
                        }}
                        onOptionChange={function(option) {
                            this.props.stockModeType.input.onChange(option)
                        }.bind(this)}
                        portalSettingsForDropdown={this.props.portalSettingsForDropdown}
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
}

export default StockModeInputsComponent;