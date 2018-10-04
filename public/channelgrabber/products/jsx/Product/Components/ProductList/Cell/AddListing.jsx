import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import Icon from 'Product/Components/Icon';
import styled from 'styled-components';
import constants from 'Product/Components/ProductList/Config/constants';

"use strict";

let AddIcon = styled(Icon)`
        background-image: url('${constants.ADD_ICON_URL}');
        background-size:50%;
    `;

AddIcon.sizer = styled.div`
        display:flex;
        width:${constants.LISTING_ICON_SIZE + 'px'};
        height:${constants.LISTING_ICON_SIZE + 'px'};
    `;

let AddListingCell = React.createClass({
    getDefaultProps: function() {
        return {
            rowData: {},
            rowIndex: null
        };
    },
    onAddListingClick: async function(rowData) {
        this.props.actions.createNewListing({
            rowData
        });
    },
    render() {
        const {products, rowIndex} = this.props;
        const rowData = stateUtility.getRowData(products, rowIndex);
        if (stateUtility.isVariation(rowData)) {
            return <span/>
        }
        return (
            <div className={this.props.className}>
                <AddIcon.sizer>
                    <AddIcon
                        onClick={this.onAddListingClick.bind(this, rowData)}
                        className={this.props.className}
                    />
                </AddIcon.sizer>
            </div>
        );
    }
});

export default AddListingCell;
