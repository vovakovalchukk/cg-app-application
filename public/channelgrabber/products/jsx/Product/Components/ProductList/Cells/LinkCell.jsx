define([
    'react',
    'Product/Components/Link',
    'Product/Components/ProductList/stateFilters',
    'styled-components'

], function(
    React,
    Link,
    stateFilters,
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
            const rowData = stateFilters.getRowData(products, rowIndex);
            
            let productLinks;
            if (products.allProductsLinks && products.allProductsLinks[rowData.id]) {
                // console.log('setting LinkCells productLinks to an array.. (supposeidely). products.allProductsLinks: ', products.allProductsLinks, ' products.allProductsLinks[rowData.id] : ', products.allProductsLinks[rowData.id]);
                productLinks = products.allProductsLinks[rowData.id];
            }
            
            console.log('in linkCell with props: ', this.props, ' productLinks : ', productLinks, ' this.props.products.allProductsLinks: ', products.allProductsLinks);
            return (
                <StyledLink.container>
                    <StyledLink
                        sku={rowData.sku}
                        productLinks={productLinks}
                    />
                </ StyledLink.container>
            );
        }
    });
    
    return LinkCell;
});
