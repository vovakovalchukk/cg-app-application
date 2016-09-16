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
                'Reduced',
                'Super Reduced',
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
                this.headers.map(function (header) {
                    var element = {};
                    for (var taxRateId in product.taxRates[memberState]) {
                        if (!product.taxRates[memberState].hasOwnProperty(taxRateId)) {
                            continue;
                        }
                        if (product.taxRates[memberState][taxRateId].name === header) {
                            element = product.taxRates[memberState][taxRateId];
                            element.taxRateId = taxRateId;
                        }
                    }
                    rates.push(element);
                });
                var row = rates.map(function (object, index) {
                    if (object.name === undefined) {
                        var cellText = "";
                        if (index === 0) {
                            cellText = memberState;
                        }
                        return (<td>{cellText}</td>);
                    }
                    return(<td>
                        <input type="radio" name={memberState+"-radio"} value={object.taxRateId} onClick={this.onVatChanged.bind(this, object.taxRateId)} checked={object.selected === true} key={object.taxRateId}/>
                        <span className="rate">{parseFloat(object.rate) + '%'}</span>
                    </td>);
                }.bind(this));
                vatRows.push(<tr>{row}</tr>);
            }
            return vatRows;
        },
        onVatChanged: function (taxRateId) {
            var product = this.props.variations[0];
            var memberState = taxRateId.substring(0, 2);
            for (var taxRate in product.taxRates[memberState]) {
                if (!product.taxRates.hasOwnProperty(memberState)) {
                    continue;
                }
                product.taxRates[memberState][taxRate].selected = (product.taxRates[memberState][taxRate].taxRateId === taxRateId);
            }
            this.props.onVariationDetailChanged(product);
            this.props.onVatChanged(taxRateId);
        },
        render: function () {
            var rowheight = 45;
            var numberRows = this.props.variations[0].parentProductId ? (this.props.fullView ? this.props.variations.length : 2) : 1;
            var style = {
                maxHeight: numberRows * rowheight
            };
            return (
                <div className="vat-table">
                    <div className="head">
                        <table>
                            <thead>
                                <tr>{this.getHeaders()}</tr>
                            </thead>
                        </table>
                    </div>
                    <div className="body" style={style}>
                        <table>
                            <tbody>
                                {this.getVatRows()}
                            </tbody>
                        </table>
                    </div>
                </div>
            );
        }
    });

    return VatViewComponent;
});