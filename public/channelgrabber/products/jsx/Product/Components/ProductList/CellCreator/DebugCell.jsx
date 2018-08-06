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
            return {
                rowData:{}
            };
        },
        getInitialState: function() {
            return {};
        },
        render() {
            
            // console.log('in render of DebugCell with this.props: ' , this.props);
            // console.log('tableDataWrapper: ', tableDataWrapper);
            const {rowIndex, columnKey, rowData} = this.props;
            
            if(!rowData.variationIds){
                return <Cell>new one</Cell>;
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
