define([
    'react',
    'fixed-data-table',
    'Product/Components/ProductList/Cells/Text'
], function(
    React,
    FixedDataTable,
    TextCell
) {
    "use strict";
    console.log('in columnCreator');
    const Cell = FixedDataTable.Cell;
    const Column = FixedDataTable.Column;
    
    var columnCreator = function(creatorObject) {
        // console.log('in columnCreator with creatorObject: ' , creatorObject);
        const {data, columnKey} = creatorObject;
        let columnRenderers = getColumnRenderers();
        let columnRenderer = columnRenderers[columnKey];
        
        if(typeof columnRenderer !== 'function'){
            //todo remove this debug error.log
            console.error('can not render for creatorObject:  ',creatorObject);
            return
        }
        return columnRenderer(creatorObject);
    };
    
    return columnCreator;
    
    function renderImageColumn(creatorObject) {
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
    function renderNameColumn(creatorObject) {
        return (<Column
            columnKey="name"
            width={200}
            label="name"
            fixed={true}
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
            fixed={true}
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
            width={200}
            label="sku"
            fixed={true}
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
    function renderParentProductExpand(creatorObject) {
        return (<Column
            columnKey= {creatorObject.columnKey}
            width={200}
            label="sku"
            fixed={true}
            header={<Cell> is Parent </Cell>}
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
            parentProductExpand: renderParentProductExpand
        }
    }
    
});
