import React from 'react';
import Link from 'Product/Components/Link';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import styled from 'styled-components'

// define([], function() {
//     "use strict";

const StyledLink = styled(Link)([]);

StyledLink.container = styled.div`
           display: flex;
           justify-content: center;
    `;

class LinkCell extends React.Component {
    render() {
        // console.log('in LinkCell render this.props: ', this.props);
        
        const {products, rowIndex} = this.props;
        const rowData = stateUtility.getRowData(products, rowIndex);
        const isParentProduct = stateUtility.isParentProduct(rowData);
        
        let productLinks = getProductLinks(products, rowData);
        
        // return (
        //         <div>sdfsdf</div>
        // );
        // return (
        //     <Skeleton
        //         width={LINK_ICON_DIMENSIONS.height + 'px'}
        //         height={LINK_ICON_DIMENSIONS.height + 'px'}
        //         borderRadius={(LINK_ICON_DIMENSIONS.height / 2) + 'px'}
        //     />
        // );
        return (
                <StyledLink.container className={this.props.className} {...this.props}>
                    {!isParentProduct ?
                        <StyledLink
                            sku={rowData.sku}
                            productLinks={productLinks}
                            linkStatus={rowData.linkStatus}
                        />
                        : ''
                    }
                </ StyledLink.container>
        );
    }
};


export default LinkCell;

function getProductLinks(products, rowData) {
    return products.allProductsLinks[rowData.id];
}

// });
