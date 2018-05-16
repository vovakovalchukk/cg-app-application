define([
    'react'
], function(
    React
) {
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
        getClassNames: function() {
            var classNames = 'c-stock-mode-input';
            if (!this.props.classNames) {
                classNames += ' c-stock-mode-input--medium';
            }
            return classNames
        },
        render: function() {
            return (
                <div className={this.getClassNames()}>
                    <select
                        onChange={this.props.stockModeType.input.onChange}
                        className={'c-input-field'}
                        value={this.props.stockModeType.input.value}
                        name={'stockModeType'}
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
                    <input
                        className={'c-input-field u-margin-top-xsmall'}
                        name={'stockAmount'}
                        disabled={this.stockAmountShouldBeDisabled(this.props.stockModeType.input.value)}
                        type={'number'}
                        value={this.props.stockAmount.input.value}
                        onChange={this.props.stockAmount.input.onChange}
                    />
                </div>
            );
        }
    });

    return StockModeInputsComponent;
});
