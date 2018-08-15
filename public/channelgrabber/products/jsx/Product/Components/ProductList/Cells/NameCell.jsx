define([
    'react',
    'fixed-data-table',
    'Product/Components/ProductList/stateUtility'
], function(
    React,
    FixedDataTable,
    stateUtility
) {
    "use strict";
    
    let NameCell = React.createClass({
        getDefaultProps: function() {
            return {};
        },
        getInitialState: function() {
            return {};
        },
        getVariationName:function(row){
            return Object.keys(row.attributeValues).map((key, index) => {
                return (
                    <div>{key}: {row.attributeValues[key]}</div>
                );
            });
        },
        render() {
            const {products, rowIndex} = this.props;
            const row = stateUtility.getRowData(products, rowIndex);
            const isParentProduct = stateUtility.isParentProduct(row)
            
            return (
                <div {...this.props}>
                    {isParentProduct ?
                        row['name']
                        :
                        this.getVariationName(row)
                    }
                </div>
            );
        }
    });
    
    return NameCell;
    
});
