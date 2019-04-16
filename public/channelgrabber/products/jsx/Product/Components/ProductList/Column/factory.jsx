import React from 'react';
import styled from 'styled-components';
import CellFactory from 'Product/Components/ProductList/Cell/factory';
import columnKeys from 'Product/Components/ProductList/Column/columnKeys'
import CellWrapper from 'Product/Components/ProductList/Cell/Wrapper';
import FixedDataTable from 'fixed-data-table-2';
import headerCellFactory from 'Product/Components/ProductList/Cell/Header/factory';

"use strict";

const {Column} = FixedDataTable;

const columnSpecificPropsMap = {
    stockMode: ['rows', 'stock', 'userSettings', 'scroll', 'select'],
    includePurchaseOrdersInAvailable: ['rows', 'stock', 'incPOStockInAvailableOptions', 'scroll', 'select'],
    available: ['rows'],
    dimensions: ['rows', 'detail', 'scroll'],
    weight: ['rows', 'detail', 'scroll'],
    vat: ['rows', 'vat', 'scroll', 'select'],
    bulkSelect: ['bulkSelect'],
    lowStock: ['rows', 'stock', 'userSettings', 'scroll', 'select'],
    pickingLocation: ['rows', 'scroll', 'pickLocations', 'pickLocationsSelect', 'select'],
    name: ['rows', 'name', 'focus'],
    productExpand: ['expand'],
    cost: ['rows', 'detail', 'scroll']
};
//const columnNoWrapper = [columnKeys.stockMode];
const alignFlexMap = {
    'center': 'center',
    'left': 'flex-start',
    'right': 'flex-end'
};

let cellWrappers = {};

let columnCreator = function(column, parentProps) {
    column.actions = parentProps.actions;
    column.products = parentProps.products;
    column = applyColumnSpecificProps(column, parentProps);

    createCellWrapper(column);

    let CellToRender = getCell(column);
    let HeaderCellToRender = headerCellFactory.createHeaderCellContent(column, parentProps.userSettings);

    return (<Column
        pureRendering={true}
        columnKey={column.key}
        width={column.width}
        fixed={column.fixed}
        header={HeaderCellToRender}
        align={getHeaderCellAlignment(column)}
        cell={CellToRender}
    />);
};

export default columnCreator;

function createCellWrapper(column) {
    cellWrappers[column.columnKey] = cellWrappers[column.columnKey] || styled(CellWrapper)`
        display: flex;
        align-items: center;
        height: 100%;
        width: 100%;
        box-sizing: border-box;
        justify-content: ${getJustifyContentProp(column)}
    `;
}

function getCell(column) {
    let CellContent = CellFactory.createCellContent(column);

    if (!CellContent) {
        console.error("cannot create cell in column factory for column: ", column);
    }

    let StyledCellWrapper = cellWrappers[column.columnKey];
    let CellToRender = <StyledCellWrapper {...column} CellContent={CellContent}/>;

    return CellToRender;
}

function applyColumnSpecificProps(column, parentProps) {
    let keysToAssign = columnSpecificPropsMap[column.key] ? columnSpecificPropsMap[column.key] : columnSpecificPropsMap[column.type];
    if (!keysToAssign) {
        return column;
    }
    keysToAssign.forEach(keyToAssign => {
        column[keyToAssign] = parentProps[keyToAssign]
    });
    return column;
}

function getJustifyContentProp(column) {
    return alignFlexMap[column.align];
}

function getHeaderCellAlignment(column) {
    return column.align;
}