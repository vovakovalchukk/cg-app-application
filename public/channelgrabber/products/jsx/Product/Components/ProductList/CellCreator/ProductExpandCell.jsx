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
    
    let ProductExpandCell = React.createClass({
        getDefaultProps: function() {
            return {
                rowData: {},
                rowIndex:null,
                expandProduct:null,
                collapseProduct:null
            };
        },
        getInitialState: function() {
            return {
                isExpanded:false
            };
        },
        isParentProduct: function(rowData) {
            return rowData.variationCount !== undefined && rowData.variationCount >= 1
        },
        renderExpandIcon: function(){
            let isParentProduct = this.isParentProduct(this.props.rowData);
            if(!isParentProduct){
                return;
            }
            
            return (this.state.isExpanded ?  '\u25BC':'\u25BA')
        },
        onExpandClick: function(){
            // console.log('on expand click');
            this.setState({
                isExpanded:!this.state.isExpanded
            });
            this.props.expandProduct(this.props.rowData.id);
        },
        render() {
            // console.log('in productExpandCell with this.props: ', this.props);
            
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
