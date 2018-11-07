import React from 'react';
import StatelessSelect from 'Product/Components/ProductList/Components/Select--stateless';

class StockModeInputsComponent extends React.Component {
    static defaultProps = {
        stockModeOptions: null,
        stockModeType: '',
        onChange: null,
        value: "",
        classNames: null,
        className: '',
        portalSettingsForSelect:{}
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

//        if(this.props.inputId===1){
//            console.log('in render stateless', {
//                inputId: this.props.inputId,
//                value: this.props.stockAmount.input.value
//            });
//        }

        let valueForInput = this.props.stockAmount.input.value ? this.props.stockAmount.input.value : '';

        return (
            <div className={this.props.className}>
                <div className={"c-stock-mode-input__type-select-container"}>
                    <StatelessSelect
                        inputId={this.props.inputId}
                        options={stockModeOptions}
                        active={this.props.selectActive}
                        autoSelectFirst={true}
                        selectedOption={{
                            name: selectedNameFromValue,
                            value: this.props.stockModeType.input.value.value
                        }}
                        onOptionChange={function(option) {
                            this.props.stockModeType.input.onChange(option)
                        }.bind(this)}
                        portalSettingsForDropdown={this.props.portalSettingsForDropdown}
                        selectToggle={this.props.stockModeSelectToggle}
                        actions={this.props.actions}
                    />
                </div>
                <div className={"c-stock-mode-input__amount-container"} key={'stockModeContainerDiv-'+this.props.inputId}>
                    <input
                        key={'stockModeContainerInput-'+this.props.inputId}
                        className={'c-input-field c-stock-mode-input__amount_input u-margin-left-xsmall'}
                        name={'stockAmount'}
                        type={'number'}
                        value={valueForInput}
                        onChange={this.props.stockAmount.input.onChange}
                    />
                </div>
            </div>
        );
    }
}

export default StockModeInputsComponent;