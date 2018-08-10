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
        getInitialState: function() {
            // console.log('productExpandCell GIS this.propps: ', this.props);
            this.rowData = stateFilters.getRowData(this.props.products, this.props.rowIndex);
            return null;
        },
        componentDidUpdate: function(){
            // console.log('ProductExpandCell componentDidUpdate this.props',this.props);
            this.rowData = stateFilters.getRowData(this.props.products, this.props.rowIndex);
        },
        isParentProduct: function(rowData) {
            // console.log('rowData: ', rowData);
            
            return rowData.variationCount !== undefined && rowData.variationCount >= 1
        },
        renderExpandIcon: function(){
            // console.log('in renderExpandIcon this.rowData.expandStatus: ', this.rowData.expandStatus);
            let isParentProduct = this.isParentProduct(this.rowData);
            if(!isParentProduct){
                return;
            }
            
            if( this.rowData.expandStatus === 'loading'){
                return 'loading....'
            }
            return (!this.rowData.expandStatus || this.rowData.expandStatus ==='collapsed'  ?'\u25BA'  : '\u25BC')
        },
        onExpandClick: function(){
            // console.log('on expand click this.rowData.expandStatus: ', this.rowData.expandStatus);
            if(this.rowData.expandStatus==='loading'){
                return;
            }
            if(!this.rowData.expandStatus || this.rowData.expandStatus === 'collapsed'){
                console.log('not expanded so going to expand this.rowData: ' , this.rowData);
                this.props.actions.expandProduct(this.rowData.id).then((resp)=>{
                    //
                });
                
                return;
            }
            // console.log('expanded so going to collapse');
            this.props.actions.collapseProduct(this.rowData.id);
        },
        render() {
            // console.log('in productExpandCell R with this.props: ', this.props, ' this.state: ' , this.state, 'this.rowData.expandStatus: ' , this.rowData.expandStatus);
            
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
