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
          let row = this.getRowData();
          this.props.actions.changeProductBulkSelectStatus(row.id, e.target.checked);
        },
        isSelected: function(){
            let selected = this.props.bulkSelect.selectedProducts;
            const row = stateUtility.getRowData(this.props.products, this.props.rowIndex);
            if(!row) {
                return false;
            }
            return selected.indexOf(row.id) > -1;
        },
        render() {
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
