import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility';

import CheckboxStateless from  'Common/Components/Checkbox--stateless';

class BulkSelectCell extends React.Component {
    static defaultProps = {};
    state = {};

    getRowData = () => {
        return stateUtility.getRowData(this.props.products, this.props.rowIndex)
    };

    onSelectChange = (e) => {
        let row = this.getRowData();
        this.props.actions.changeProductBulkSelectStatus(row.id, !this.isSelected());
    };

    isSelected = () => {
        let selected = this.props.bulkSelect.selectedProducts;
        const row = stateUtility.getRowData(this.props.products, this.props.rowIndex);
        if (!row) {
            return false;
        }
        let isSelected = selected.indexOf(row.id) > -1;
        return isSelected;
    };

    render() {
        const row = stateUtility.getRowData(this.props.products, this.props.rowIndex);
        if(stateUtility.isVariation(row)){
            return <span/>
        }
        return (
            <CheckboxStateless
                className={this.props.className}
                onSelect={this.onSelectChange}
                isSelected={this.isSelected()}
            />
        );
    }
}

export default BulkSelectCell;

