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
            
            // console.log('in render of DebugCell with this.props: ' , this.props , ' this.context: ' , this.context);
            // console.log('tableDataWrapper: ', tableDataWrapper);
            const {rowIndex, columnKey, rowData} = this.props;
            
            return <div> in the debug cell </div>
            
            //todo implement below once you have properly got redux in
            // let variationIds = rowData.variationIds.map((variation) =>
            //     <span>{variation} </span>
            // );
            //
            // // console.log('in debug render and data in tableDataWrapper: ' , tableDataWrapper.getData());
            // return (
            //     <div
            //         // rowData={rowData}
            //     >
            //         id: {rowData.id} <br />
            //         variations :{variationIds}
            //     </div>
            // );
        }
    });
    
    return DebugCell;
});
