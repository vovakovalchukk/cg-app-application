import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import styled from 'styled-components';

const CellContainer = styled.div`
        display:flex;
        justify-content:center;
    `;

const ExpandLink = styled.a`
        user-select: none;
    `;

const EXPAND_STATUSES = {
    expanded: 'expanded',
    loading: 'loading',
    collapsed: 'collapsed'
};

const RIGHT_ARROW = '\u25BA';
const DOWN_ARROW = '\u25BC';

class ProductExpandCell extends React.Component {
    static defaultProps = {
        rowData: {},
        rowIndex: null
    };

    getRowData = () => {
        return stateUtility.getRowData(this.props.products, this.props.rowIndex)
    };

    isParentProduct = (rowData) => {
        return stateUtility.isParentProduct(rowData)
    };

    renderExpandIcon = () => {
        let rowData = this.getRowData();
        let isParentProduct = this.isParentProduct(rowData);
        if (!isParentProduct) {
            return;
        }
        if (this.getRowData().expandStatus === EXPAND_STATUSES.loading) {
            return <img
                title={'loading product variations...'}
                src={"/cg-built/zf2-v4-ui/img/loading-transparent-21x21.gif"}
                class={"b-loader"}
            />
        }
        return (!rowData.expandStatus || rowData.expandStatus === EXPAND_STATUSES.collapsed ? RIGHT_ARROW : DOWN_ARROW)
    };

    onExpandClick = () => {
        let rowData = this.getRowData();
        if (rowData.expandStatus === EXPAND_STATUSES.loading) {
            return;
        }
        if (!rowData.expandStatus || rowData.expandStatus === EXPAND_STATUSES.collapsed) {
            this.props.actions.expandProduct(rowData.id)
            return;
        }
        this.props.actions.collapseProduct(rowData.id);
    };

    render() {
        return (
            <CellContainer {...this.props}>
                <ExpandLink onClick={this.onExpandClick}>
                    {this.renderExpandIcon()}
                </ExpandLink>
            </CellContainer>
        );
    }
}

export default ProductExpandCell;