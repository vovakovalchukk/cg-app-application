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
    
    const Cell = FixedDataTable.Cell;
    
    let TextCell = React.createClass({
        getDefaultProps: function() {
            return {};
        },
        getInitialState: function() {
            return {};
        },
        render() {
            let cellData = stateFilters.getCellData(
                this.props.products.visibleRows,
                this.props.columnKey,
                this.props.rowIndex
            );
            // let cellValue = tableDataWrapper.getCellData(columnKey,rowIndex)
            
            return (
                <div {...this.props}>
                    {/*{cellValue}*/}
                    {cellData}
                </div>
            );
        }
    });
    
    return TextCell;
});
