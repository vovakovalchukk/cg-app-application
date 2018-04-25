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
                selected: null
            };
        },
        getInitialState: function() {
            return {
                selectedStockMode:{

                }
            };
        },
        onUpdateStockMode: function(e) {
            var selectedStockModeName = e.target.value;
            var selectedStockModeValueType = getValueTypeForStockMode(selectedStockModeName, this.props.stockModeOptions);

            var amountToSave=null;
            if (doesStockModeNeedValue(selectedStockModeValueType)) {
                amountToSave = this.state.selectedStockMode.amount;
            }
            var selectedStockMode = {
                name:selectedStockModeName,
                type:selectedStockModeValueType,
                amount:amountToSave
            }

            this.setState({
                selectedStockMode:selectedStockMode
            });

//            this.props.onChange(selectedStockMode);
        },
        onUpdateStockAmount: function(e) {

            console.log('in onUpdateStockAmount with e.target.value : ', e.target.value);
            var selectedStockMode = Object.assign({},this.state.selectedStockMode);
            selectedStockMode.amount = e.target.value;
            this.setState({
                selectedStockMode:selectedStockMode
            });
//            this.props.onChange(stockMode);
        },
        shouldInputBeDisabled: function() {
            return doesStockModeNeedValue(this.state.selectedStockMode);
        },

        renderStockModeSelect: function() {

            return (
                <select
                    onChange={this.onUpdateStockMode}
                    className={'c-input-field'}
                >
                    {this.props.stockModeOptions.map(function(option) {
                        console.log('option : ', option)
                        return <option name={option.title} value={option.title}>
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

    function doesStockModeNeedValue(stockMode) {
        var needsValue = (
            stockMode.type == 'all' ||
            stockMode.type == 'null' ||
            !stockMode.type
        );
        return needsValue;
    }

    function getValueTypeForStockMode(selectedStockModeName, stockModes) {
        for (var i = 0; i < stockModes.length; i++) {
            if (stockModes[i].title == selectedStockModeName) {
                return stockModes[i].value;
            }
        }
    }

});
