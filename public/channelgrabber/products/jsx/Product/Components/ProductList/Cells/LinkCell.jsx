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
    
    const StyledLink = styled(Link)`
    `;
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
            console.log('isParentProduct in LinkCell....: ', isParentProduct);
            
            let productLinks;
            if (products.allProductsLinks && products.allProductsLinks[rowData.id]) {
                productLinks = products.allProductsLinks[rowData.id][rowData.id];
            }
            return (
                <StyledLink.container>
                    {!isParentProduct ?
                        <StyledLink
                            sku={rowData.sku}
                            productLinks={productLinks}
                        />
                        : ''
                    }
                </ StyledLink.container>
            );
        }
    });
    
    return LinkCell;
});
