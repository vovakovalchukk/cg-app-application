define([
    'react',
    'fixed-data-table',
    'Product/Components/ProductList/CellCreator/Text',
    'Product/Components/ProductList/CellCreator/DebugCell'
], function(
    React,
    FixedDataTable,
    TextCell,
    DebugCell
) {
    "use strict";
    const Cell = FixedDataTable.Cell;
    const Column = FixedDataTable.Column;
    
    var CellCreator = function(creatorObject) {
        console.log('in cellCreator with creatorObject...',creatorObject);
        let cellRenderers = getCellComponents();
        let CellComponent = cellRenderers[creatorObject.columnKey];
    
        if (typeof CellComponent !== 'function') {
            console.error("no function for column renderer column "  , column)
            return
        }
        
        
        return <CellComponent
                    dummyProp={'dummy prop'}
                    />
        
        
    };
    
    return CellCreator;
  
 
    
    // /
    function getCellComponents() {
        return {
            debug: DebugCell,
            parentProductExpand:TextCell,
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
        }
    }
});
