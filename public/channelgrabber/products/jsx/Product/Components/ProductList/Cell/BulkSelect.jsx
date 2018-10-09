import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility';

class BulkSelectCell extends React.Component {
    static defaultProps = {};
    state = {};

    getRowData = () => {
        return stateUtility.getRowData(this.props.products, this.props.rowIndex)
    };

    onSelectChange = (e) => {
      let row = this.getRowData();
      this.props.actions.changeProductBulkSelectStatus(row.id, e.target.checked);
    };

    isSelected = () => {
        let selected = this.props.bulkSelect.selectedProducts;
        const row = stateUtility.getRowData(this.props.products, this.props.rowIndex);
        if(!row) {
            return false;
        }
        return selected.indexOf(row.id) > -1;
    };

    render() {
        return (
            <div className={this.props.className}>
                <input
                    type="checkbox"
                    onChange={this.onSelectChange}
                    checked={this.isSelected()}
                    className={"std-checkbox"}
                />
            </div>
        );
    }
}

export default BulkSelectCell;

