define([
    'react',
    'fixed-data-table',
    'Product/Components/ProductList/tableDataWrapper',
    
    'Product/Components/ProductList/CellCreator/Text',
    'Product/Components/ProductList/CellCreator/DebugCell',
    'Product/Components/ProductList/CellCreator/ProductExpandCell'

], function(
    React,
    FixedDataTable,
    tableDataWrapper,
    TextCell,
    DebugCell,
    ProductExpandCell
) {
    "use strict";
    
    const Cell = FixedDataTable.Cell;
    
    var CellCreator = function(creatorObject) {
        console.log('in cell creator with creatorObject: ', creatorObject);
        // let rowData = tableDataWrapper.getRowData(creatorObject.rowIndex);
        // if(!rowData){
        //     return <Cell></Cell>
        // }
        let cellRenderers = getCellComponents();
        let CellComponent = cellRenderers[creatorObject.columnKey];
        
        if (typeof CellComponent !== 'function') {
            console.error("no function for column renderer column ", column)
            return
        }
        
        // connecting manually to Redux since using a container here causes issues with fixed-data-table
        CellComponent.contextTypes = {
            store: React.PropTypes.object.isRequired
        };
        return <CellComponent
            {...creatorObject}
        />
        // return <CellComponent
        //     {...creatorObject}
        //     rowData={rowData}
        // />
    };
    
    return CellCreator;
    
    
    function getCellComponents() {
        return {
            debug: DebugCell,
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
        }
    }
});
