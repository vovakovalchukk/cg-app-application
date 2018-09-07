define([
    'react',
    'fixed-data-table',
    'Product/Components/ProductList/Cells/Text',
    'Product/Components/ProductList/Cells/ProductExpandCell',
    'Product/Components/ProductList/Cells/ImageCell',
    'Product/Components/ProductList/Cells/LinkCell',
    'Product/Components/ProductList/Cells/NameCell',
    'Product/Components/ProductList/Cells/ListingAccountCell',
    'Product/Components/ProductList/Cells/AddListingCell'
], function(
    React,
    FixedDataTable,
    TextCell,
    ProductExpandCell,
    ImageCell,
    LinkCell,
    NameCell,
    ListingAccountCell,
    AddListingCell
) {
    "use strict";
    
    const Column = FixedDataTable.Column;
    
    let cells = {
        productExpand: ProductExpandCell,
        image: ImageCell,
        link: LinkCell,
        sku: TextCell,
        name: NameCell,
        available: TextCell,
        listingAccount: ListingAccountCell,
        addListing: AddListingCell,
        //todo to be replaced in 215
        dummyDetailsColumn1: TextCell,
        dummyDetailsColumn2: TextCell,
        dummyDetailsColumn3: TextCell,
        dummyDetailsColumn4: TextCell,
        dummyDetailsColumn5: TextCell,
        dummyDetailsColumn6: TextCell,
        dummyDetailsColumn7: TextCell,
        dummyDetailsColumn8: TextCell,
    };
    
    var columnCreator = function(column) {
        
        if(column.key==='addListing'){
            // console.log('columnCretor addListing column : ' , column);
            //
        }
        
        let CreatedCell = getCreatedCell(column);
        if(!CreatedCell){
            console.error("cannot create cell in column factory for column: " , column);
        }
        return (<Column
            columnKey={column.key}
            width={column.width}
            fixed={column.fixed}
            header={column.headerText}
            cell={<CreatedCell
                {...column}
                products={column.products}
                actions={column.actions}
            />}
        />);
    };
    
    return columnCreator;
    
    function getCreatedCell(column){
        return column.type ? cells[column.type] : cells[column.key];
    }
});
