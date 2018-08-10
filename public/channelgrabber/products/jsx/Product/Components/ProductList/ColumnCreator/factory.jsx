define([
    'react',
    'fixed-data-table',
    'Product/Components/ProductList/Cells/Text',
    'Product/Components/ProductList/Cells/DebugCell',
    'Product/Components/ProductList/Cells/ProductExpandCell'
], function(
    React,
    FixedDataTable,
    TextCell,
    DebugCell,
    ProductExpandCell
) {
    "use strict";
    const Column = FixedDataTable.Column;
    
    let cells = {
        productExpand: ProductExpandCell,
        image: TextCell,
        link: TextCell,
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
