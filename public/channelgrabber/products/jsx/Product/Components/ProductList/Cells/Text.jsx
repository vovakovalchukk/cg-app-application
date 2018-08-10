define([
    'react',
    'fixed-data-table',
    'Product/Components/ProductList/stateFilters'
], function(
    React,
    FixedDataTable,
    stateFilters
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
            let cellData = stateFilters.getCellData(
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
