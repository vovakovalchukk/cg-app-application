define([
    'react',
    'react-redux',
    'fixed-data-table',
    'Product/Components/ProductList/stateFilters',
    'Product/Components/ProductList/tableDataWrapper',
    'Product/Components/ProductList/CellCreator/Text',
    'Product/Components/ProductList/CellCreator/DebugCell',
    'Product/Components/ProductList/CellCreator/ProductExpandCell'
], function(
    React,
    ReactRedux,
    FixedDataTable,
    stateFilters,
    tableDataWrapper,
    TextCell,
    DebugCell,
    ProductExpandCell
) {
    "use strict";
    
    const Cell = FixedDataTable.Cell;
    
    const mapDispatchToProps = function(dispatch) {
        return {};
    };
    
    var CellCreator = function(creatorObject) {
        // console.log('in cell creator with creatorObject: ', creatorObject);
        let cellRenderers = getCellComponents();
        let CellContentComponent = cellRenderers[creatorObject.columnKey];
    
        const mapStateToProps = function(state) {
            //todo - clever stuff in here to extract state
            return {
                products:state.products,
                rowData: stateFilters.getRowData(state.products.visibleRows,creatorObject.rowIndex),
                cellData: stateFilters.getCellData(
                    state.products.visibleRows,
                    creatorObject.columnKey,
                    creatorObject.rowIndex
                )
            }
        };
        const ReduxConnector = ReactRedux.connect(mapStateToProps, mapDispatchToProps);
        
        let ConnectedCellContentComponent = ReduxConnector(CellContentComponent);
        
        if (typeof CellContentComponent !== 'function') {
            console.error("no function for column renderer column ", column)
            return
        }
        
        return (<Cell >
            <ConnectedCellContentComponent {...creatorObject}/>
        </Cell>)
        
        // // connecting manually to Redux since using a container here causes issues with fixed-data-table
        // CellComponent.contextTypes = {
        //     store: React.PropTypes.object.isRequired
        // };
        // return <CellComponent
        //     {...creatorObject}
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
