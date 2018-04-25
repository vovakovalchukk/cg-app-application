define([
    'react',
    'Common/Components/Input',
    'Common/Components/Select'
], function(
    React,
    Input,
    Select
) {

    var StockModeInputsComponent = React.createClass({
        getDefaultProps: function() {
            return {
                stockModeOptions: null,
                onChange: null,
                selected: {
                    value:null,
                    amount:null
                }
            };
        },
        getInitialState: function() {
            return {
                selectedStockMode: {}
            };
        },
        onSelectChange: function(e) {
            console.log("onSelectChange with e : " , e);
            var selectedStockModeValue = e.target.value;
            console.log('selectedStockMode: ' , selectedStockMode);

            var amountToSave = null;
            if (doesStockModeNeedValue(selectedStockModeValue)) {
                amountToSave = this.state.selectedStockMode.amount;
            }


            var selectedStockMode = {
                value: selectedStockModeValue,
                amount: amountToSave
            };

            console.log("selectedStockMode to save: " , selectedStockMode);

            this.setState({
                selectedStockMode: selectedStockMode
            });
            this.props.onChange(
                {
                    target: {
                        value: selectedStockMode

                    }
                });
        },
        onUpdateStockAmount: function(e) {
            console.log('in onUpdateStockAmount with e.target.value : ', e.target.value);
            var selectedStockMode = Object.assign({}, this.state.selectedStockMode);
            selectedStockMode.amount = e.target.value;
            this.setState({
                selectedStockMode: selectedStockMode
            });
            this.props.onChange(
                {
                    target: {
                        value: selectedStockMode

                    }
                });
        },
        shouldInputBeDisabled: function() {
            return doesStockModeNeedValue(this.state.selectedStockMode);
        },
        isSelectedStockModeOption: function(title) {
            if (this.props) {
                if (this.props.selected) {
                    if (this.props.selected.name == title) {
                        return true;
                    }
                }
            }
        },
        renderStockModeSelect: function() {
            console.log('in renderStockModeSelect with this.props.selected: ', this.props.selected);
            var selectedVal = this.props.selected.value;
            return (
                <select
                    onChange={this.onSelectChange}
                    className={'c-input-field'}
                    value={selectedVal}
                >
                    {this.props.stockModeOptions.map(function(option) {
                        return <option
                            name={option.title}
                            value={option.value}
                        >
                            {option.title}
                        </option>
                    })}
                </select>
            );
        },
        renderNumberInput: function() {
            return (
                <input
                    className={'c-input-field u-margin-top-xsmall'}
                    disabled={this.shouldInputBeDisabled()}
                    type={'number'}
                    onChange={this.onUpdateStockAmount}
                />
            )
        },
        render: function() {
            return (
                <div>
                    {this.renderStockModeSelect.call(this)}
                    {this.renderNumberInput.call(this)}
                </div>
            );
        }
    });

    return StockModeInputsComponent;

    function doesStockModeNeedValue(value) {
        var needsValue = (
            value == 'all' ||
            value == 'null' ||
            !value
        );
        return needsValue;
    }

    function getValueTypeForStockMode(selectedStockModeName, stockModes) {

        for (var i = 0; i < stockModes.length; i++) {
            console.log('stockMode[i]: ' , stockModes[i]);

            if (stockModes[i].value == selectedStockModeName) {
                console.log('stockMode[i]: ' , stockModes[i]);
                return stockModes[i].value;
            }
        }
    }

});
