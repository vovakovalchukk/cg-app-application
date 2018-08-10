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
    
    let ProductExpandCell = React.createClass({
        getDefaultProps: function() {
            return {
                rowData: {},
                rowIndex:null
            };
        },
        getRowData:function(){
            return stateFilters.getRowData(this.props.products, this.props.rowIndex)
        },
        isParentProduct: function(rowData) {
            // console.log('rowData: ', rowData);
            
            return rowData.variationCount !== undefined && rowData.variationCount >= 1
        },
        renderExpandIcon: function(){
            let rowData = this.getRowData();
            // console.log('in renderExpandIcon this.rowData.expandStatus: ', this.rowData.expandStatus);
            let isParentProduct = this.isParentProduct(rowData);
            if(!isParentProduct){
                return;
            }
            
            if( this.getRowData().expandStatus === 'loading'){
                return 'loading....'
            }
            return (!this.getRowData().expandStatus || this.getRowData().expandStatus ==='collapsed'  ?'\u25BA'  : '\u25BC')
        },
        onExpandClick: function(){
            // console.log('on expand click this.getRowData().expandStatus: ', this.getRowData().expandStatus);
            let rowData = this.getRowData();
            if(rowData.expandStatus==='loading'){
                return;
            }
            if(!rowData.expandStatus || rowData.expandStatus === 'collapsed'){
                console.log('not expanded so going to expand this.getRowData(): ' , this.getRowData());
                this.props.actions.expandProduct(rowData.id)
                return;
            }
            // console.log('expanded so going to collapse');
            this.props.actions.collapseProduct(rowData.id);
        },
        render() {
            // console.log('in productExpandCell R with this.props: ', this.props, ' this.state: ' , this.state, 'this.getRowData().expandStatus: ' , this.getRowData().expandStatus);
            
            // let {columnKey,rowIndex} = this.props;
            // const {data, rowIndex, columnKey, collapsedRows, callback} = this.props;
            return (
                <div {...this.props}>
                    <a onClick={this.onExpandClick}>
                        {this.renderExpandIcon()}
                        
                    </a>
                </div>
            );
        }
    });
    
    return ProductExpandCell;
});
