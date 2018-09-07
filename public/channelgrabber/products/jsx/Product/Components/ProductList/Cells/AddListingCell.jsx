define([
    'react',
    // 'Clipboard',
    // 'fixed-data-table',
    'Product/Components/ProductList/stateUtility'
], function(
    React,
    // Clipboard,
    // FixedDataTable,
    stateUtility
) {
    "use strict";
    
    let AddListingCell = (props) => {
        // console.log('in AddListingCell with props: ' , props);
        const {products, rowIndex} = props;
        const rowData = stateUtility.getRowData(products, rowIndex);
        if (props.onCreateNewListingIconClick) {
            console.log('onCreateListingIcons got the func ')
        }
        return (
            <span onClick={props.onCreateNewListingIconClick.bind(rowData)}>add listing</span>
        )
    };
    
    return AddListingCell;
});
