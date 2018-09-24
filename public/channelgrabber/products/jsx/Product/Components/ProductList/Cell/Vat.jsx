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
        console.log('changeVat with e: ', e);
        
        },
        render() {
            const {products, rowIndex, countryCode} = this.props;
            const row = stateUtility.getRowData(products, rowIndex);
            // console.log('in renderVat cell this.props: ' , this.props , ' row: '  , row);
            let productVat = this.props.vat.productsVat[row.id];
            // console.log('vatRates: ', this.props.vat.vatRates);
            // console.log('countryCode: ', countryCode);
            
            let vatRatesForCountry = this.props.vat.vatRates[countryCode];
            // console.log('vatRatesForCountry : ', vatRatesForCountry );
            
            let options = generateOptionsFromVatRates(vatRatesForCountry);
            let selected = {
                name:productVat[countryCode],
                value:productVat[countryCode]
            }
            
            return (
                <Select
                    options={options}
                    selectedOption={selected}
                    onOptionChange={this.changeVat}
                />
            );
        }
    });
    
    return VatCell;
    
    function generateOptionsFromVatRates(vatRates){
        return vatRates.map(rate=>{
            return {
                name:rate.key,
                value:rate.key
            }
        });
    }
});
