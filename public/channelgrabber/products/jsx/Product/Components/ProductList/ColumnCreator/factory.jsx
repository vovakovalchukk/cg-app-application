define([
    'react',
    'fixed-data-table',
    // 'Product/Components/ProductList/Cells/Text',
    'Product/Components/ProductList/CellCreator/factory',
    
    // 'Product/Components/ProductList/Cells/DebugCell'
], function(
    React,
    FixedDataTable,
    //todo flesh out individual cell components properly from TAC-165 onwards
    cellCreator
    // TextCell,
    
    // DebugCell
) {
    "use strict";
    const Cell = FixedDataTable.Cell;
    const Column = FixedDataTable.Column;
    
    var columnCreator = function(column) {
        // console.log('in columnCreator with column: ', column);
        return (
            <Column
                columnKey={column.key}
                width={column.width}
                // label=
                fixed={column.fixed}
                header={column.headerText}
                cell={props => {
                    let {columnKey, rowIndex} = props;
                    return cellCreator({
                        columnKey,
                        rowIndex,
                        products:column.products,
                        expandProduct:column.expandProduct
                    })
                }}
            />
        )
    };
    
    return columnCreator;
    
    function renderDebugColumn(column){
        // console.log('outside of cell in renderDebugColumn with creatorObject: ', column);
        let testVar = 'have the variable'
        return (<Column
            columnKey="debug"
            width={100}
            label="debug"
            fixed={true}
            header={<Cell> debug</Cell>}
            cell={<DebugCell
                testVar={testVar}
                
                // columnObject={creatorObject}
            />}
        />);
    }
    function renderImageColumn(column) {
        return (<Column
            columnKey="image"
            width={100}
            label="image"
            fixed={true}
            header={<Cell> image head </Cell>}
            cell={props => {
                return (
                    <Cell>
                        image {props.rowIndex}
                    </Cell>
                );
            }}
        />);
    }
    function renderNameColumn(column) {
        return (<Column
            columnKey="name"
            width={150}
            label="name"
            fixed={true}
            header={<Cell> name head </Cell>}
            cell={props => {
                // let value = getValue(creatorObject.columnKey, creatorObject.data, props.rowIndex);
                return (
                    <Cell>
                        {/*{value}*/}sdfs
                    </Cell>
                );
            }}
        />);
    }
    
    function renderLinkColumn(creatorObject) {
        return (<Column
            columnKey="link"
            width={100}
            label="link"
            fixed={true}
            header={<Cell> link head </Cell>}
            cell={props => {
                // let value = getValue(creatorObject.columnKey, creatorObject.data, props.rowIndex);
                return (
                    <Cell>
                        {/*{value}*/}
                    </Cell>
                );
            }}
        />);
    }
    
    function renderAvailableColumn(creatorObject) {
        return (<Column
            columnKey="available"
            width={100}
            label="available"
            fixed={true}
            header={<Cell> available head </Cell>}
            cell={props => {
                return (
                    <Cell>
                        available {props.rowIndex}
                    </Cell>
                );
            }}
        />);
    }
    
    function renderSkuColumn(creatorObject) {
        return (<Column
            columnKey="sku"
            width={150}
            label="sku"
            fixed={true}
            header={<Cell> sku head </Cell>}
            cell={props => {
                // let value = getValue(creatorObject.columnKey, creatorObject.data, props.rowIndex);
                return (
                    <Cell>
                        {/*{value}*/}
                    </Cell>
                );
            }}
        />);
    }
    function renderDummyListingColumn(creatorObject) {
        return (<Column
            columnKey={creatorObject.columnKey}
            width={200}
            label={creatorObject.columnKey}
            fixed={creatorObject.isFixed}
            header={<Cell> {creatorObject.columnKey} </Cell>}
            cell={props => {
                // let value = getValue(creatorObject.columnKey, creatorObject.data, props.rowIndex);
                return (
                    <Cell>
                        {/*{value}*/}
                    </Cell>
                );
            }}
        />);
    }
    function renderParentProductExpand(creatorObject){
        return (<Column
            columnKey={creatorObject.columnKey}
            width={100}
            label={creatorObject.columnKey}
            fixed={true}
            header={<Cell> {creatorObject.columnKey} </Cell>}
            cell={props => {
                // let value = getValue(creatorObject.columnKey, creatorObject.data, props.rowIndex);
                return (
                    <Cell>
                        {/*{value}*/}
                    </Cell>
                );
            }}
        />);
    }
    
    function getValue(columnKey, data, rowIndex) {
        let rowValues = data[rowIndex].values;
        for (let column of rowValues) {
            if (column.columnKey === columnKey) {
                return column.value;
            }
        }
    }
    
    function getColumnRenderers() {
        return {
            debug:renderDebugColumn,
            parentProductExpand:renderParentProductExpand,
            image: renderImageColumn,
            link: renderLinkColumn,
            sku: renderSkuColumn,
            name: renderNameColumn,
            available: renderAvailableColumn,
            //todo - change these to represent actual data in TAC-165
            dummyListingColumn1: renderDummyListingColumn,
            dummyListingColumn2: renderDummyListingColumn,
            dummyListingColumn3: renderDummyListingColumn,
            dummyListingColumn4: renderDummyListingColumn,
            dummyListingColumn5: renderDummyListingColumn,
            dummyListingColumn6: renderDummyListingColumn,
            dummyListingColumn7: renderDummyListingColumn,
            dummyListingColumn8: renderDummyListingColumn,
        }
    }
});
