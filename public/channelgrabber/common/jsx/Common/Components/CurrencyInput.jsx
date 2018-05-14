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
                title: null,
                min: null,
                max: null,
                step: "any",
                className: ''
            }
        },
        render: function () {
            var value = parseFloat(this.props.value);
            return (
                <span className={"currency-symbol " + this.props.className}>
                    {this.props.currency}
                    <input
                        ref="input"
                        type="number"
                        name={this.props.name ? this.props.name : "price"}
                        step={this.props.step}
                        placeholder="0.00"
                        value={isNaN(value) ? '' : value}
                        onChange={this.props.onChange}
                        title={this.props.title}
                        min={this.props.min}
                        max={this.props.max}
                    />
                </span>
            );
        }
    });

    return CurrencyInput;
});