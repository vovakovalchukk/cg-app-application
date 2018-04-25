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
                stockMode: {
                    name: '',
                    value: ''
                }
            };
        },
        updateStockMode: function(stockMode) {
            console.log("in updateStockMode with stockMode : ", stockMode);
            this.setState({
                stockMode: stockMode
            });
        },
        shouldInputBeDisabled: function() {
            console.log('in shouldInputBeDisabled  this.state.stockMode.value: ',  this.state.stockMode.value);
            var shouldBeDisabled = (
                this.state.stockMode.value == 'all' ||
                this.state.stockMode.value == 'null' ||
                this.state.stockMode.value == '' ||
                !this.state.stockMode.value
            );
            console.log('shouldBeDisabled: ' , shouldBeDisabled);
            return shouldBeDisabled;
        },
        formatOptionsForSelectComponent: function(options) {
            return options.map(function(option) {
                console.log('option: ', option)
                return {
                    name: option.title,
                    value: option.value
                }
            });
        },
        render: function() {
            console.log('in render with this.props: ', this.props);
            var options = this.formatOptionsForSelectComponent(this.props.stockModeOptions);
            return (
                <div>
                        <Select
                            options={options}
                            onOptionChange={this.updateStockMode}
                            classNames={'form-row__input'}
                        />


                        <Input
                            disabled={this.shouldInputBeDisabled()}
                            classNames={'form-row__input'}
                            inputType={'number'}
                        />
                </div>

            );
        }
    });

    return StockModeInputsComponent;

});
