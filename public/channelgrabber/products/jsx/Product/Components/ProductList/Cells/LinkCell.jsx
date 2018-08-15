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
            const rowData = stateFilters.getRowData(this.props.products, this.props.rowIndex)
    
            return (
                <StyledLink.container>
                    <StyledLink sku={rowData.sku}/>
                </ StyledLink.container>
            );
        }
    });
    
    return LinkCell;
});
