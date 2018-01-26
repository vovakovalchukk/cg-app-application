define([
    'react'
], function(
    React
) {
    "use strict";
    var CurrencyInput = React.createClass({
        getDefaultProps: function () {
            return {
                currency: "£"
            }
        },
        render: function () {
            return (
                <span className="currency-symbol">
                    {this.props.currency}
                    <input ref="input"
                           type="number"
                           name={this.props.name ? this.props.name : "price"}
                           placeholder="0.00"
                           value={this.props.value ? this.props.value : ''}
                           onChange={this.props.onChange}
                    />
                </span>
            );
        }
    });

    return CurrencyInput;
});