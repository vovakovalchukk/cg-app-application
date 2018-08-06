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
    
    let CollapseCell = React.createClass({
        getDefaultProps: function() {
            return {};
        },
        getInitialState: function() {
            return {};
        },
        render() {
            const {data, rowIndex, columnKey, collapsedRows, callback, ...props} = this.props;
            return (
                <Cell {...props}>
                    <a onClick={() => callback(rowIndex)}>
                        {collapsedRows.has(rowIndex) ? '\u25BC' : '\u25BA'}
                    </a>
                </Cell>
            );
        }
    });
    
    return CollapseCell;
});
