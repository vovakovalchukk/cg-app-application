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
    
    let TextCell = React.createClass({
        getDefaultProps: function() {
            return {};
        },
        getInitialState: function() {
            return {};
        },
        render() {
            let cellData = stateUtility.getCellData(
                this.props.products,
                this.props.columnKey,
                this.props.rowIndex
            );
            
            return (
                <div {...this.props}>
                    {cellData}
                </div>
            );
        }
    });
    
    return TextCell;
});
