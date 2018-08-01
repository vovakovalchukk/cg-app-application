define([
    'react',
    'fixed-data-table',
    'Product/Components/ProductList/Cells/Text'
], function(
    React,
    FixedDataTable,
    //todo flesh out individual cell components properly from TAC-165 onwards
    TextCell
) {
    "use strict";
    const Cell = FixedDataTable.Cell;
    const Column = FixedDataTable.Column;
    
    var columnCreator = function(creatorObject) {
        const {columnKey} = creatorObject;
        let columnRenderers = getColumnRenderers();
        let columnRenderer = columnRenderers[columnKey];
        if(typeof columnRenderer !== 'function'){
            return
        }
        return columnRenderer(creatorObject);
    };
    
    return columnCreator;
    
    function renderImageColumn(creatorObject) {
        return (<Column
            columnKey="image"
            width={200}
            label="image"
            fixed={false}
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
    function renderNameColumn(creatorObject) {
        return (<Column
            columnKey="name"
            width={200}
            label="name"
            fixed={false}
            header={<Cell> name head </Cell>}
            cell={props => {
                let value = getValue(creatorObject.columnKey, creatorObject.data, props.rowIndex);
                return (
                    <Cell>
                        {value}
                    </Cell>
                );
            }}
        />);
    }
    function renderLinkColumn(creatorObject){
        return (<Column
            columnKey="link"
            width={200}
            label="link"
            fixed={false}
            header={<Cell> link head </Cell>}
            cell={props => {
                let value = getValue(creatorObject.columnKey, creatorObject.data, props.rowIndex);
                return (
                    <Cell>
                        {value}
                    </Cell>
                );
            }}
        />);
    }
    function renderAvailableColumn(creatorObject){
        return (<Column
            columnKey="available"
            width={200}
            label="available"
            fixed={false}
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
            width={200}
            label="sku"
            fixed={false}
            header={<Cell> sku head </Cell>}
            cell={props => {
                let value = getValue(creatorObject.columnKey, creatorObject.data, props.rowIndex);
                return (
                    <Cell>
                        {value}
                    </Cell>
                );
            }}
        />);
    }
    function renderDummyListingColumn(creatorObject){
        return (<Column
            columnKey={creatorObject.columnKey}
            width={200}
            label={creatorObject.columnKey}
            fixed={false}
            header={<Cell> {creatorObject.columnKey} </Cell>}
            cell={props => {
                let value = getValue(creatorObject.columnKey, creatorObject.data, props.rowIndex);
                return (
                    <Cell>
                        {value}
                    </Cell>
                );
            }}
        />);
    }
    
    function getValue(columnKey, data, rowIndex){
        let rowValues = data[rowIndex].values;
        for(let column of rowValues){
            if(column.columnKey === columnKey){
                return column.value;
            }
        }
    }
    
    function getColumnRenderers() {
        return {
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
        }
    }
    
});
