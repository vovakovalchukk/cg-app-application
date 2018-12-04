import React from 'react';
import Link from 'Product/Components/Link';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import styled from 'styled-components'

const StyledLink = styled(Link)([]);
StyledLink.container = styled.div`
   display: flex;
   justify-content: center;
`;

class LinkCell extends React.Component {
    render() {
        const {products, rowIndex} = this.props;
        const rowData = stateUtility.getRowData(products, rowIndex);
        const isParentProduct = stateUtility.isParentProduct(rowData);
        let productLinks = getProductLinks(products, rowData);
        console.log('productLinks: ' , productLinks);


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