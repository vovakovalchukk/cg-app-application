define([
    'react',
    'Product/Components/Link',
    'Product/Components/ProductList/stateUtility',
    'styled-components'

], function(
    React,
    Link,
    stateUtility,
    styled
) {
    "use strict";
    
    styled = styled.default;
    
    const StyledLink = styled(Link)([]);
    
    StyledLink.container = styled.div`
           display: flex;
           justify-content: center;
    `;
    
    let LinkCell = React.createClass({
        getDefaultProps: function() {
            return {};
        },
        getInitialState: function() {
            return {};
        },
        render() {
            const {products, rowIndex} = this.props;
            const rowData = stateUtility.getRowData(products, rowIndex);
            const isParentProduct = stateUtility.isParentProduct(rowData);
            
            let productLinks = getProductLinks(products, rowData);
            
            return (
                <StyledLink.container>
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
    });
    
    return LinkCell;
    
    function getProductLinks(products, rowData) {
        if (!doLinksExist(products) || isParentProduct(rowData)) {
            return [];
        }
        if (products.allProductsLinks && products.allProductsLinks[rowData.id] && !rowData.parentProductId) {
            return products.allProductsLinks[rowData.id][rowData.id];
        }
        if (!doesLinkExistForVariation(products, rowData)) {
            return [];
        }
        return products.allProductsLinks[rowData.parentProductId][rowData.id];
    }
    
    function doLinksExist(products) {
        return !!Object.keys(products.allProductsLinks).length
    }
    
    function isParentProduct(rowData) {
        return !!rowData.variationIds.length;
    }
    
    function doesLinkExistForVariation(products, rowData) {
        return products.allProductsLinks[rowData.parentProductId] && products.allProductsLinks[rowData.parentProductId][rowData.id]
    }
});
