define([
    'react',
    'Product/Components/Select'
], function(
    React,
    Select
) {
    "use strict";

    var VatViewComponent = React.createClass({
        getDefaultProps: function() {
            return {
                variations: [],
                fullView: false
            };
        },
        getHeaders: function () {
            this.headers = [
                'Member State',
                'Standard',
                'Super Reduced',
                'Reduced',
                'Zero'
            ];

            return this.headers.map(function(header) {
                return <th>{header}</th>;
            });
        },
        getVatRows: function () {
            if (! this.props.variations.length) {
                return;
            }

            var product = this.props.variations[0];
            if (! product.taxRates) {
                return;
            }

            var vatRows = [];
            for (var memberState in product.taxRates) {
                if (! product.taxRates.hasOwnProperty(memberState)) {
                    continue;
                }
                var rates = [];
                this.headers.map(function (header, index) {
                    if (index === 0) {
                        rates.push(<td className="memberstate-cell">{memberState}</td>);
                        return;
                    }
                    var taxRate = product.taxRates[memberState][memberState+index];
                    if (taxRate === undefined) {
                        rates.push(<td></td>);
                        return;
                    }
                    var formattedRate = parseFloat(taxRate['rate']);
                    rates.push(<td>
                        <input type="radio" name={memberState+"-radio"} value={memberState+index} checked={taxRate['selected']}/>
                        <span className="rate">{formattedRate + '%'}</span>
                    </td>);
                });
                vatRows.push(<tr>{rates}</tr>);
            }
            return vatRows;
        },
        render: function () {
            return (
                <div className="vat-table">
                    <table>
                        <thead>
                            <tr>{this.getHeaders()}</tr>
                        </thead>
                        <tbody>
                            {this.getVatRows()}
                        </tbody>
                    </table>
                </div>
            );
        }
    });

    return VatViewComponent;
});