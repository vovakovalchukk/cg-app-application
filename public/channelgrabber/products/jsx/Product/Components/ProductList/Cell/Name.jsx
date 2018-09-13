define([
    'react',
    'Clipboard',
    'fixed-data-table',
    'Product/Components/ProductList/stateUtility'
], function(
    React,
    Clipboard,
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
        getVariationName: function(row) {
            return Object.keys(row.attributeValues).map((key, index) => {
                return (
                    <div>{key}: {row.attributeValues[key]}</div>
                );
            });
        },
        componentDidMount: function() {
            new Clipboard('div.js-'+this.getUniqueClassName(), [], 'data-copy');
        },
        getUniqueClassName:function(){
            return this.props.columnKey+'-'+this.props.rowIndex;
        },
        render() {
            const {products, rowIndex} = this.props;
            const row = stateUtility.getRowData(products, rowIndex);
            const isVariation = stateUtility.isVariation(row);
            let name = isVariation ? this.getVariationName(row) : row['name'];
            
            return (
                <div {...this.props} className={'js-'+this.getUniqueClassName()} data-copy={name}>
                    {name}
                </div>
            );
        }
    });
    
    return NameCell;
});
