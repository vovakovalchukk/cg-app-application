define([
    'react'
], function(
    React
) {
    "use strict";
    var CurrencyInput = React.createClass({
        getDefaultProps: function () {
            return {
                currency: "£",
                title: null
            }
        },
        render: function () {
            var value = parseFloat(this.props.value);
            return (
                <span className="currency-symbol">
                    {this.props.currency}
                    <input
                        ref="input"
                        type="number"
                        name={this.props.name ? this.props.name : "price"}
                        placeholder="0.00"
                        value={isNaN(value) ? '' : value}
                        onChange={this.props.onChange}
                        title={this.props.title}
                    />
                </span>
            );
        }
    });

    return CurrencyInput;
});