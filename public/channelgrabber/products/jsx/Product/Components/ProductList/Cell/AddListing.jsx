define([
    'react',
    'Product/Components/ProductList/stateUtility',
    'styled-components'
], function(
    React,
    stateUtility,
    styled
) {
    "use strict";
    
    styled = styled.default;
    
    const ADD_ICON_UNICODE = '\u002B';
    
    let AddIcon = styled.span`
      font-size:1.5rem;
      cursor:pointer;
      margin-left:0.5rem;
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
                    {ADD_ICON_UNICODE}
                </AddIcon>
            );
        }
    });
    
    return AddListingCell;
});
