define([
    'react',
    'fixed-data-table',
    'Product/Components/ProductList/Cells/Text',
    'Product/Components/ProductList/Cells/DebugCell',
    'Product/Components/ProductList/Cells/ProductExpandCell',
    'Product/Components/ProductList/Cells/ImageCell',
    'Product/Components/ProductList/Cells/LinkCell'
], function(
    React,
    FixedDataTable,
    TextCell,
    DebugCell,
    ProductExpandCell,
    ImageCell,
    LinkCell
) {
    "use strict";
    ////
    const Column = FixedDataTable.Column;
    
    let cells = {
        productExpand: ProductExpandCell,
        image: ImageCell,
        link: LinkCell,
        sku: TextCell,
        name: TextCell,
        available: TextCell,
        //todo - change these to represent actual data in TAC-165
        dummyListingColumn1: TextCell,
        dummyListingColumn2: TextCell,
        dummyListingColumn3: TextCell,
        dummyListingColumn4: TextCell,
        dummyListingColumn5: TextCell,
        dummyListingColumn6: TextCell,
        dummyListingColumn7: TextCell,
        dummyListingColumn8: TextCell,
        
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
        let CreatedCell = cells[column.key];
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
        />)
    };
    
    return columnCreator;
});
