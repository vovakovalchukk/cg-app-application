define([
    'react',
    'fixed-data-table',
    'Product/Components/ProductList/tableDataWrapper',

], function(
    React,
    FixedDataTable,
    tableDataWrapper
) {
    "use strict";
    
    const Cell = FixedDataTable.Cell;
    
    
    let DebugCell = React.createClass({
        getDefaultProps: function() {
            return {};
        },
        getInitialState: function() {
            return {};
        },
        render() {
            
            // console.log('in render of DebugCell with this.props: ' , this.props);
            // console.log('tableDataWrapper: ', tableDataWrapper);
            const {rowIndex, columnKey} = this.props;
    
            let rowData = tableDataWrapper.getRowData(rowIndex);
            console.log('rowData returned from getter: ', rowData);
            if(!rowData){
                return <Cell></Cell>
            }
            
            
    
            let variationIds = rowData.variationIds.map((variation) =>
                <span>{variation} </span>
            );

            // console.log('in debug render and data in tableDataWrapper: ' , tableDataWrapper.getData());
            return (
                <Cell
                    // rowData={rowData}
                >
                    id: {rowData.id} <br />
                    variations :{variationIds}
                </Cell>
            );
        }
    });
    
    return DebugCell;
});
