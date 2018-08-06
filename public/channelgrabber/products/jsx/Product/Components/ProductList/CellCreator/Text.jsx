define([
    'react',
    'fixed-data-table',
    'Product/Components/ProductList/tableDataWrapper'
], function(
    React,
    FixedDataTable,
    tableDataWrapper
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
            // console.log('in TextCell with this.props: ', this.props);
            let {columnKey,rowIndex} = this.props;
            
            let cellValue = tableDataWrapper.getCellData(columnKey,rowIndex)
            
            return (
                <Cell {...this.props}>
                    {cellValue}

                </Cell>
            );
        }
    });
    
    return TextCell;
});
