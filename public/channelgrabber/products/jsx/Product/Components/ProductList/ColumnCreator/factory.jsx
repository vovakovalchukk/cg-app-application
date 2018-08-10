define([
    'react',
    'fixed-data-table',
    // 'Product/Components/ProductList/Cells/Text',
    // 'Product/Components/ProductList/CellCreator/factory',
    'Product/Components/ProductList/CellCreator/Text',
    'Product/Components/ProductList/CellCreator/DebugCell',
    'Product/Components/ProductList/CellCreator/ProductExpandCell'
    // 'Product/Components/ProductList/Cells/DebugCell'
], function(
    React,
    FixedDataTable,
    //todo flesh out individual cell components properly from TAC-165 onwards
    // cellCreator,
    TextCell,
    DebugCell,
    ProductExpandCell
    // TextCell,
    
    // DebugCell
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
        // console.log('in columnCreator with column: ', column);
    
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
