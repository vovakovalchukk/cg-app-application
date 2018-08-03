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
            const {rowIndex, columnKey, data} = this.props;
            
            // console.log('in render of DebugCell with this.props: ' , this.props);
            // console.log('tableDataWrapper: ', tableDataWrapper);
            
            
            console.log('in debug render and data in tableDataWrapper: ' , tableDataWrapper.getData());
            
            
            
            return (
                <Cell {...this.props}>
                    {/*{data[rowIndex][columnKey]}*/}
                    in debug cell
                </Cell>
            );
        }
    });
    
    return DebugCell;
});
