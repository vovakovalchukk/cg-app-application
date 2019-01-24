import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import Icon from 'Product/Components/Icon';
import styled from 'styled-components';
import constants from 'Product/Components/ProductList/Config/constants';

"use strict";

let AddIcon = styled(Icon)`
        background-image: url('${constants.ADD_ICON_URL}');
        background-size: 40%;
        clip-path: circle(20% at 50% 50%);    
`;

AddIcon.iconSizer = styled.div`
        position: relative
        display: flex;
        width: ${constants.LISTING_ICON_SIZE + 'px'};
        height: ${constants.LISTING_ICON_SIZE + 'px'};
        z-index: 5;
`;
AddIcon.iconBackground = styled.div`
        position: absolute;
        box-sizing: border-box;
        border: 2px solid rgb(211, 211, 211);
        background: aliceblue;
        border-radius: 50%;
        width: ${constants.LISTING_ICON_SIZE / 1.5 + 'px'};
        height: ${constants.LISTING_ICON_SIZE / 1.5 + 'px'};
        top: 2px;
        left: 50%;
        top: 50%;
        transform: translate(-50%,-50%);
`;

class AddListingCell extends React.Component {
    static defaultProps = {
        rowData: {},
        rowIndex: null
    };

    onAddListingClick = async (rowData) => {
        console.log('awaiting this..');
        await this.props.actions.createNewListing({
            rowData
        });
        console.log('finished awaiting');
        
        
    };

    render() {
        const {products, rowIndex} = this.props;
        const rowData = stateUtility.getRowData(products, rowIndex);
        if (stateUtility.isVariation(rowData)) {
            return <span/>
        }
        return (
            <div className={this.props.className}>
                <AddIcon.iconSizer>
                    <AddIcon
                        onClick={this.onAddListingClick.bind(this, rowData)}
                        className={this.props.className}
                    />
                </AddIcon.iconSizer>
                <AddIcon.iconBackground/>
            </div>
        );
    }
}

export default AddListingCell;