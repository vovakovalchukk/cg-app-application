define([
    'react',
    'Clipboard',
    'fixed-data-table',
    'Product/Components/ProductList/stateUtility'
], function(
    React,
    Clipboard,
    FixedDataTable,
    stateUtility
) {
    "use strict";
    
    //todo make this a react class
    
    let BulkSelectCell = React.createClass({
        getDefaultProps: function() {
            return {};
        },
        getInitialState: function() {
            return {};
        },
        getRowData: function() {
            return stateUtility.getRowData(this.props.products, this.props.rowIndex)
        },
        onSelectChange:function(e){
          console.log('on select change e: ' , e);
          console.log('e.target.checked: ', e.target.checked);
          let row = this.getRowData();
          this.props.actions.changeProductBulkSelectStatus(row.id, e.target.checked);
        },
        isSelected: function(){
            let selected = this.props.bulkSelect.selectedProducts;
            const row = stateUtility.getRowData(this.props.products, this.props.rowIndex);
            console.log('selected: ', selected);
            console.log('selected.indexOf(row.id) > -1: ', selected.indexOf(row.id) > -1);
            // return true;
            return selected.indexOf(row.id) > -1;
        },
        render() {
            console.log('in render');
            
            
            return (
                <div className={this.props.className}>
                    <input
                        type="checkbox"
                        onChange={this.onSelectChange}
                        checked={this.isSelected()}
                    />
                </div>
            );
        }
    });
    
    return BulkSelectCell;
});
