import React from 'react';
"use strict";

class VatViewComponent extends React.Component {
    static defaultProps = {
        variationCount: 0,
        fullView: false,
        autoSelectFirst: false,
        onChange: null,
        onVatChanged: null,
        onVatChangeWithFullSelection: null,
        tableCssClassNames: ''
    };

    state = {
        selectedVatRates: {}
    };

    getHeaders = () => {
        this.headers = [
            'Member State',
            'Standard',
            'Reduced',
            'Super Reduced',
            'Zero'
        ];
        return this.headers.map(function(header) {
            return <th className={'c-table-with-inputs__cell'}>{header}</th>;
        });
    };

    getVatRows = () => {
        var product = this.props.parentProduct;
        if (!product.taxRates) {
            return <tr>
                <td>
                    In order to use this feature, please <a href={this.props.adminCompanyUrl}>set your company
                    to VAT registered here</a>
                </td>
            </tr>;
        }
        var vatRows = [];
        for (var memberState in product.taxRates) {
            if (!product.taxRates.hasOwnProperty(memberState)) {
                continue;
            }
            var rates = [];
            this.headers.map(function(header) {
                var element = {};
                for (var taxRateId in product.taxRates[memberState]) {
                    if (!product.taxRates[memberState].hasOwnProperty(taxRateId)) {
                        continue;
                    }
                    if (product.taxRates[memberState][taxRateId].name === header) {
                        element = product.taxRates[memberState][taxRateId];
                        element.taxRateId = taxRateId;
                        var selectedTaxRateId = (this.state.selectedVatRates[memberState] === undefined ? product.taxRates[memberState][taxRateId].selected : this.state.selectedVatRates[memberState]);
                        if (element.taxRateId === selectedTaxRateId) {
                            element.selected = true;
                        }
                    }
                }
                rates.push(element);
            }.bind(this));
            
            var row = rates.map(function(object, index) {
                if (object.name === undefined) {
                    var cellText = "";
                    if (index === 0) {
                        cellText = memberState;
                    }
                    return (<td>{cellText}</td>);
                }
                return (<td>
                        <span className="checkbox-wrapper">
                            <a className="std-checkbox">
                                <input type="checkbox" id={object.taxRateId + "-radio-" + product.id}
                                       name={object.taxRateId + "-radio-" + product.id} value={object.taxRateId}
                                       onClick={this.onVatChanged.bind(this, object.taxRateId)}
                                       checked={object.selected === true} key={object.taxRateId}/>
                                <label htmlFor={object.taxRateId + "-radio-" + product.id}></label>
                            </a>
                            <span className="rate">{parseFloat(object.rate) + '%'}</span>
                        </span>
                </td>);
            }.bind(this));
            vatRows.push(<tr>{row}</tr>);
        }
        return vatRows;
    };

    onVatChanged = (taxRateId) => {
        var product = this.props.parentProduct;
        var memberState = taxRateId.substring(0, 2);
        for (var taxRate in product.taxRates[memberState]) {
            if (!product.taxRates.hasOwnProperty(memberState)) {
                continue;
            }
            product.taxRates[memberState][taxRate].selected = (product.taxRates[memberState][taxRate].taxRateId === taxRateId);
        }
        var selectedVatRates = this.state.selectedVatRates;
        selectedVatRates[memberState] = taxRateId;
        this.setState({
            selectedVatRates: selectedVatRates
        }, function() {
            if (this.props.onVatChangeWithFullSelection) {
                this.props.onVatChangeWithFullSelection(this.state.selectedVatRates)
            }
        });
        if (this.props.onVatChanged) {
            this.props.onVatChanged(taxRateId);
        }
    };

    render() {
        var rowheight = 45;
        var numberRows = this.props.variationCount !== 0 ? (this.props.fullView ? this.props.variationCount : 2) : 1;
        var style = {
            maxHeight: numberRows * rowheight
        };
        return (
            <div className="vat-table">
                <div className="head">
                    <table className={this.props.tableCssClassNames}>
                        <thead>
                        <tr>{this.getHeaders()}</tr>
                        </thead>
                    </table>
                </div>
                <div className="body" style={style}>
                    <table className={this.props.tableCssClassNames}>
                        <tbody>
                        {this.getVatRows()}
                        </tbody>
                    </table>
                </div>
            </div>
        );
    }
}

export default VatViewComponent;