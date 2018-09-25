define([
    'react',
    'Clipboard',
    'fixed-data-table',
    'Product/Components/ProductList/stateUtility',
    'Common/Components/Select'
], function(
    React,
    Clipboard,
    FixedDataTable,
    stateUtility,
    Select
) {
    "use strict";
    
    let VatCell = React.createClass({
        getDefaultProps: function() {
            return {
                products: {},
                rowIndex: null
            };
        },
        getInitialState: function() {
            return {};
        },
        changeVat:function(e){
            const {products, rowIndex, countryCode} = this.props;
            const row = stateUtility.getRowData(products, rowIndex);
            this.props.actions.updateVat(row.id, countryCode, e.value);
        },
        render() {
            const {products, rowIndex, countryCode} = this.props;
            const row = stateUtility.getRowData(products, rowIndex);
            let productVat = this.props.vat.productsVat[row.id];
            
            let vatRatesForCountry = this.props.vat.vatRates[countryCode];
            let options = generateOptionsFromVatRates(vatRatesForCountry);
            let selectedVatKey = productVat[countryCode];
            
            let selectedLabel = options.find(option=>(selectedVatKey===option.value)).name;
            
            let selected = {
                name: selectedLabel,
                value:selectedVatKey
            };
            
            return (
                <div className={this.props.className}>
                    <Select
                        options={options}
                        selectedOption={selected}
                        onOptionChange={this.changeVat}
                        fullWidth={true}
                    />
                </div>
            );
        }
    });
    
    return VatCell;
    
    function generateOptionsFromVatRates(vatRates){
        return vatRates.map(rate=>{
            return {
                name:rate.label,
                value:rate.key
            }
        });
    }
});
