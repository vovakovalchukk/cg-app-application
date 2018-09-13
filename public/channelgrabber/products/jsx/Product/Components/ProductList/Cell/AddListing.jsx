define([
    'react',
    'Product/Components/ProductList/stateUtility',
    'Product/Components/Icon',
    'styled-components',
    'Product/Components/ProductList/Config/constants'
], function(
    React,
    stateUtility,
    Icon,
    styled,
    constants
) {
    "use strict";
    
    styled = styled.default;
    
    let AddIcon = styled(Icon)`
      background-image: url('${constants.ADD_ICON_URL}')
    `;
    
    let AddListingCell = React.createClass({
        getDefaultProps: function() {
            return {
                rowData: {},
                rowIndex: null
            };
        },
        getRowData: function() {
            return stateUtility.getRowData(this.props.products, this.props.rowIndex)
        },
        isParentProduct: function(rowData) {
            return stateUtility.isParentProduct(rowData)
        },
        onAddListingClick: async function() {
            const {products, rowIndex} = this.props;
            const rowData = this.getRowData(products, rowIndex);
            this.props.actions.createNewListing({
                rowData
            });
        },
        render() {
            return (
                <AddIcon
                    onClick={this.onAddListingClick}
                >
                </AddIcon>
            );
        }
    });
    
    return AddListingCell;
});
