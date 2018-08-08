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
            // console.log('in TextCellxtCell with this.props: ', this.props, ' this : ' , this);
            let {columnKey,rowIndex} = this.props;
            
            // let cellValue = tableDataWrapper.getCellData(columnKey,rowIndex)
            
            return (
                <div {...this.props}>
                    {/*{cellValue}*/}
                    {this.props.cellData}
                </div>
            );
        }
    });
    
    return TextCell;
});
