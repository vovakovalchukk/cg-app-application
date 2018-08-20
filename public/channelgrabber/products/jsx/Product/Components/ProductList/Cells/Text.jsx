define([
    'react',
    'fixed-data-table'

], function(
    React,
    FixedDataTable
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
            const {rowIndex, columnKey, data} = this.props;
            return (
                <Cell {...this.props}>
                    {data[rowIndex][columnKey]}
                </Cell>
            );
        }
    });
    
    return TextCell;
});
