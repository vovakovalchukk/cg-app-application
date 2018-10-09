import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import styled from 'styled-components';

const Checkbox = styled.div`
    width:1rem;
    height:1rem;
    border: ${props => (props.isSelected ? 'green solid 1px' : 'grey 1px solid')};
    line-height:16px;
    background:white;
    color:green;
    border-radius:50%;
    cursor:pointer;
`;

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
        if(!row) {
            return false;
        }
        let isSelected = selected.indexOf(row.id) > -1;
        console.log('isSelected: ', isSelected);
        return isSelected;
    };

    render() {
        return (
            <a
                className={" " + this.props.className}
                onClick={this.onSelectChange}
            >
                <Checkbox isSelected={this.isSelected()}>
                    {this.isSelected() ? '✔' : ''}
                </Checkbox>
            </a>
        );
    }
}

export default BulkSelectCell;

