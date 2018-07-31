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
    const Table = FixedDataTable.Table;
    const Column = FixedDataTable.Column;
    
    var columnCreator = function(creatorObject) {
        console.log('in columnCreator with creatorObject: ' , creatorObject);
        
        
        const {data, columnKey} = creatorObject;
        let columnRenderers = getColumnRenderers();
        return columnRenderers[columnKey](creatorObject);
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
    
    function getValue(columnKey, data, rowIndex){
        console.log('in getValue with columnKey:  '  , columnKey, ' data : ', data, ' and rowIndex: ' , rowIndex);
        
        
        return data[rowIndex][columnKey];
    }
    
    function getColumnRenderers() {
        return {
            image: renderImageColumn,
            link: renderLinkColumn,
            sku: renderSkuColumn,
            name: renderNameColumn,
            available: renderAvailableColumn
        }
    }
    
});
