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
        const {data, columnKey} = creatorObject;
        let columnRenderers = getColumnRenderers();
    
        console.log('in columnCreator with columnKey: ' , columnKey);
    
        console.log('columnRenderers: ', columnRenderers);
        
        
        return columnRenderers[columnKey]();
    };
    
    return columnCreator;
    
    function renderImageColumn() {
        return (<Column
            columnKey="image"
            width={300}
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
    function renderNameColumn() {
        return (<Column
            columnKey="name"
            width={300}
            label="name"
            header={<Cell> name head </Cell>}
            cell={props => {
                return (
                    <Cell>
                        name {props.rowIndex}
                    </Cell>
                );
            }}
        />);
    }
    function renderLinkColumn(){
        return (<Column
            columnKey="link"
            width={300}
            label="link"
            header={<Cell> link head </Cell>}
            cell={props => {
                return (
                    <Cell>
                        link {props.rowIndex}
                    </Cell>
                );
            }}
        />);
    }
    function renderAvailableColumn(){
        return (<Column
            columnKey="available"
            width={300}
            label="available"
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
    function renderSkuColumn() {
        return (<Column
            columnKey="sku"
            width={300}
            label="sku"
            header={<Cell> sku head </Cell>}
            cell={props => {
                return (
                    <Cell>
                        sku {props.rowIndex}
                    </Cell>
                );
            }}
        />);
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
