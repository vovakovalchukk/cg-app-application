define([
    'react',
    'fixed-data-table',
    'Product/Components/ProductList/stateUtility',
    'Product/Components/StockModeInputs'
], function(
    React,
    FixedDataTable,
    stateUtility,
    StockModeInputs
) {
    "use strict";
    
    let StockModeCell = React.createClass({
        getDefaultProps: function() {
            return {
                products: {},
                rowIndex: null
            };
        },
        getInitialState: function() {
            return {};
        },
        onStockModeChange: function(e) {
            console.log('in onStockModeChange e: ', e);
            
        },
        render() {
            console.log('in StockModeCell with this.props:  ', this.props);
            const {products, rowIndex} = this.props;
            const row = stateUtility.getRowData(products, rowIndex);
            
            const isSimpleProduct = stateUtility.isSimpleProduct(row)
            const isVariation = stateUtility.isVariation(row);
            
            if (!isSimpleProduct && !isVariation) {
                //todo - remove the text here before submission
                return <span></span>
            }
            
            //todo - change the input values to reflect what is coming back from the store
            return (
                <span>
                    <select>
                      <option value="volvo">Volvo</option>
                      <option value="saab">Saab</option>
                      <option value="mercedes">Mercedes</option>
                      <option value="audi">Audi</option>
                    </select>
                    
                    
                    <StockModeInputs
                        onChange={this.onStockModeChange}
                        stockModeOptions={this.props.stock.stockModeOptions}
                        // stockModeOptions
                        stockModeType={{
                            input: {
                                value: 'all'
                            }
                        }}
                        stockAmount={{
                            input: {
                                value: '0'
                            }
                        }}
                    />
                </span>
            );
        }
    });
    
    return StockModeCell;
});
