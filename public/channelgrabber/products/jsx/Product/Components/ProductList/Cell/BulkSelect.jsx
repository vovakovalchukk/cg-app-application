import React from 'react';
import Clipboard from 'Clipboard';
import FixedDataTable from 'fixed-data-table';
import stateUtility from 'Product/Components/ProductList/stateUtility';
    
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
    
    export default BulkSelectCell;

