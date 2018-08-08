define([
    'react',
    'fixed-data-table'
], function(
    React,
    FixedDataTable
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
            // console.log('rowData: ', rowData);
            
            return rowData.variationCount !== undefined && rowData.variationCount >= 1
        },
        renderExpandIcon: function(){
            let isParentProduct = this.isParentProduct(this.props.rowData);
            // console.log('isParentProduct: ', isParentProduct);
            
            if(!isParentProduct){
                return;
            }
            return (this.state.isExpanded ?  '\u25BC':'\u25BA')
        },
        onExpandClick: function(){
            console.log('on expand click');
            if(!this.state.isExpanded){
                console.log('not expanded so going to expand');
                
                
                this.props.expandProduct(this.props.rowData.id);
                this.setState({
                    isExpanded:true
                },function(){
                    console.log('just set isExpanded to true');
                });
                return;
            }
            
            console.log('expanded so going to collapse');
            
            
            this.props.collapseProduct(this.props.rowData.id);
            this.setState({
                isExpanded:false
            });
        },
        render() {
            console.log('in productExpandCell with this.props: ', this.props, ' this.state: ' , this.state);
            
            // let {columnKey,rowIndex} = this.props;
            // const {data, rowIndex, columnKey, collapsedRows, callback} = this.props;
            return (
                <div {...this.props}>
                    <a onClick={this.onExpandClick}>
                        {this.renderExpandIcon()}
                        {this.state.isExpanded ? 'expanded' : 'collapsed'}
                    </a>
                </div>
            );
        }
    });
    
    return ProductExpandCell;
});
