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
                rowIndex:null,
                expandProduct:null,
                collapseProduct:null
            };
        },
        componentDidMount: function(){
          console.log('ProductExpandCell componentDIdMount this.props',this.props);
          
          
        },
        getInitialState: function() {
            return {
                status:'collapsed'
            };
        },
        isParentProduct: function(rowData) {
            // console.log('rowData: ', rowData);
            
            return rowData.variationCount !== undefined && rowData.variationCount >= 1
        },
        renderExpandIcon: function(){
            let rowData = stateFilters.getRowData(this.props.products.visibleRows, this.props.rowIndex);
            let isParentProduct = this.isParentProduct(rowData);
            // console.log('isParentProduct: ', isParentProduct);
            
            if(!isParentProduct){
                return;
            }
            
            if(this.state.status ==='loading'){
                return 'loading....'
            }
            
            return (this.state.status ==='expanded' ?  '\u25BC':'\u25BA')
        },
        onExpandClick: function(){
            let rowData = stateFilters.getRowData(this.props.products.visibleRows, this.props.rowIndex);
            console.log('on expand click');
            if(this.state.status === 'collapsed'){
                console.log('not expanded so going to expand');
                this.props.actions.expandProduct(rowData.id).then((resp)=>{
                    this.setState({
                        status:'expanded'
                    });
                });
                //todo put buffering thing here
                this.setState({
                    status:'loading'
                },function(){
                    console.log('just set status to loading...');
                    
                    
                });
                return;
            }
            console.log('expanded so going to collapse');
            this.props.actions.collapseProduct(rowData.id);
            this.setState({
                status:'collapsed'
            });
        },
        render() {
            // console.log('in productExpandCell with this.props: ', this.props, ' this.state: ' , this.state);
            
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
