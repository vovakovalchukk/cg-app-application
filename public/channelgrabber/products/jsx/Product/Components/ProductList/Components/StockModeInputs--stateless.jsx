import React from 'react';
import StatelessSelect from 'Product/Components/ProductList/Components/Select--stateless';
import styled from 'styled-components';

const StockModesContainer = styled.div`
    display: flex;
    align-items: center;  
`;
const StockModeValue = styled.input`
    width: 45px;
`;

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

    getDefaultOption(stockModeOptions) {
        return stockModeOptions.find(option => {
            return option.value === "null";
        });
    };

    shortenOptions = (options) => {
        var shortenedOptions = [];
        for (var i = 0; i < options.length; i++) {
            var option = options[i];

            let formattedOption;


            if (option.title.indexOf('List all') > -1) {
                formattedOption = option;
            }
            if (option.title.indexOf('List up to a') > -1) {
                formattedOption = {
                    title: 'List up to',
                    value: option.value
                }
            }
            if (option.title.indexOf('Fix') > -1) {
                formattedOption = {
                    title: 'Fixed at',
                    value: option.value
                }
            }
            if (option.value == 'null') {
                formattedOption.value == option.value;
                formattedOption.title = 'Default ('+ formattedOption.title+')';
            }
            shortenedOptions.push(formattedOption)
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
        let selectedOptionFromValue = {};

        if(!selected.value){
            selectedOptionFromValue = this.getDefaultOption(stockModeOptions);
        }else{
            selectedOptionFromValue = stockModeOptions.find(option => {
                return option.value === selected.value;
            });
        }

        let selectedNameFromValue = selectedOptionFromValue.name;
        let valueForInput = this.props.stockAmount.input.value ? this.props.stockAmount.input.value : '';

        return (
            <StockModesContainer className={this.props.className}>
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
                        styleVars={{
                           widthOfDropdown: 100,
                            widthOfInput: 80
                        }}
                    />
                </div>
                <div className={"c-stock-mode-input__amount-container"}
                     key={'stockModeContainerDiv-' + this.props.inputId}
                >
                    <div className={'safe-input-box'}>
                        <div className={'submit-input'}>
                            <StockModeValue
                                key={'stockModeContainerInput-' + this.props.inputId}
                                className={'c-input-field'}
                                name={'stockAmount'}
                                type={'number'}
                                value={valueForInput}
                                onChange={this.props.stockAmount.input.onChange}
                                width={45}
                                placeholder={this.props.stockLevelPlaceholder}
                                disabled={this.stockAmountShouldBeDisabled(this.props.stockModeType.input.value.value)}
                            />
                        </div>
                    </div>
                </div>
            </StockModesContainer>
        );
    }
}

export default StockModeInputsComponent;