import React from 'react';
import styled from 'styled-components';
import FixedDataTable from 'fixed-data-table-2';
import CellFactory from 'Product/Components/ProductList/Cell/factory';

import columnKeys from 'Product/Components/ProductList/Column/columnKeys'
import StockModeCell from 'Product/Components/ProductList/Cell/StockMode';


import ImageCell from 'Product/Components/ProductList/Cell/Image';


"use strict";
const Column = FixedDataTable.Column;

let columnCreator = function(column, parentProps) {
    column.actions = parentProps.actions;
    column.products = parentProps.products;
    column = applyColumnSpecificProps(column, parentProps);

    let CellContent = CellFactory.createCellContent(column);
    let CellWrapper = CellFactory.createCellWrapper();
    let StyledCellWrapper = styled(CellWrapper)`
            display: flex;
            align-items: center;
            height: 100%;
            width:100%;
            box-sizing: border-box;
            padding-left:1rem;
            padding-right:1rem;
            justify-content:${getJustifyContentProp(column)}
        `;

    if (!CellContent) {
        console.error("cannot create cell in column factory for column: ", column);
    }

    let CellToRender = StyledCellWrapper;
    
    //todo - better handle this
    if(column.key === columnKeys.stockMode ){
        CellToRender = StockModeCell;
    }
    if(column.key===columnKeys.image){
        CellToRender = ImageCell
    }
    
    return (<Column
        pureRendering={true}
        columnKey={column.key}
        width={column.width}
        fixed={column.fixed}
        header={column.headerText}
        align={getHeaderCellAlignment(column)}
        pureRendering={true}
        cell={<CellToRender
            {...column}
            products={column.products}
            actions={column.actions}
            CellContent={CellContent}
        />}
    />);
};

export default columnCreator;

function applyColumnSpecificProps(column, parentProps) {
    const columnSpecificPropsMap = {
        stockMode: 'stock',
        bulkSelect: 'bulkSelect'
    };
    let keyToAssign = columnSpecificPropsMap[column.key]
    if (!keyToAssign) {
        return column;
    }
    column[keyToAssign] = parentProps[keyToAssign]
    return column;
}

function getJustifyContentProp(column) {
    const alignFlexMap = {
        'center': 'center',
        'left': 'flex-start',
        'right': 'flex-end'
    };
    return alignFlexMap[column.align];
}

function getHeaderCellAlignment(column) {
    return column.align;
}