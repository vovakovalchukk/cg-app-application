define([
    'react',
    'redux',
    'react-redux',
    'fixed-data-table',
    'Product/Components/ProductList/stateFilters',
    'Product/Components/ProductList/ActionCreators',
    'Product/Components/ProductList/CellCreator/Text',
    'Product/Components/ProductList/CellCreator/DebugCell',
    'Product/Components/ProductList/CellCreator/ProductExpandCell'
], function(
    React,
    Redux,
    ReactRedux,
    FixedDataTable,
    stateFilters,
    ActionCreators,
    TextCell,
    DebugCell,
    ProductExpandCell
) {
    "use strict";
    
    const Cell = FixedDataTable.Cell;
    
    var CellCreator = function(creatorObject) {
        // console.log('in cell creator with creatorObject: ', creatorObject);
        let cellRenderers = getCellComponents();
        let CellContentComponent = cellRenderers[creatorObject.columnKey];
        
        let rowData = stateFilters.getRowData(creatorObject.products.visibleRows, creatorObject.rowIndex);
        let cellData = stateFilters.getCellData(creatorObject.products.visibleRows, creatorObject.columnKey, creatorObject.rowIndex);
        
        return (<Cell >
            <CellContentComponent
                {...creatorObject}
                rowData={rowData}
                cellData={cellData}
            />
        </Cell>)
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
    
    function getMapStateToProps (creatorObject){
        return function(state){
            return {
                products:state.products,
                rowData: stateFilters.getRowData(state.products.visibleRows,creatorObject.rowIndex),
                cellData: stateFilters.getCellData(
                    state.products.visibleRows,
                    creatorObject.columnKey,
                    creatorObject.rowIndex
                )
            }
        }
    }
    
    function getMapDispatchToProps(creatorObject){
        // console.log('in getMapDispatchTOProps');
        const {
            expandProduct,
            collapseProduct
        } = ActionCreators;
        // console.log('ActionCreators: ', ActionCreators);
        
        // todo in later tickets restrict the dispatch methods to only relevant cells
        return function(dispatch) {
            return Redux.bindActionCreators({
                expandProduct,
                collapseProduct
            }, dispatch);
        };
    }
    
    
    
});
