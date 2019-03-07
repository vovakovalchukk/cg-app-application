import React from 'react';
import styled from 'styled-components';
import CellFactory from 'Product/Components/ProductList/Cell/factory';
import columnKeys from 'Product/Components/ProductList/Column/columnKeys'
import CellWrapper from 'Product/Components/ProductList/Cell/Wrapper';
import FixedDataTable from 'fixed-data-table-2';
import styleVars from 'Product/Components/ProductList/styleVars';

"use strict";

const {Column, Cell} = FixedDataTable;

let HeaderCell = styled(Cell)`
    .public_fixedDataTableCell_cellContent{
      max-height: ${styleVars.heights.headerHeight}px;  
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
    }
`;

const columnKeysMetricPropertyMap = {
    [columnKeys.weight]: 'massUnit',
    [columnKeys.dimensions]: 'lengthUnit'
};
const columnSpecificPropsMap = {
    stockMode: ['stock', 'rows', 'userSettings', 'scroll'],
    available: ['rows'],
    dimensions: ['rows', 'detail', 'scroll'],
    weight: ['rows', 'detail', 'scroll'],
    vat: ['rows', 'vat', 'scroll'],
    bulkSelect: ['bulkSelect'],
    pickingLocation: ['rows', 'scroll', 'pickLocations', 'pickLocationsSelect'],
    name: ['rows', 'name', 'focus'],
    cost: ['rows', 'detail', 'scroll']
};
const columnNoWrapper = [columnKeys.stockMode];
const alignFlexMap = {
    'center': 'center',
    'left': 'flex-start',
    'right': 'flex-end'
};

let columnWrappers = {};

let columnCreator = function(column, parentProps) {
    column.actions = parentProps.actions;
    column.products = parentProps.products;
    column = applyColumnSpecificProps(column, parentProps);

    let CellContent = CellFactory.createCellContent(column);
    columnWrappers[column.columnKey] = columnWrappers[column.columnKey] || styled(CellWrapper)`
        display: flex;
        align-items: center;
        height: 100%;
        width: 100%;
        box-sizing: border-box;
        justify-content: ${getJustifyContentProp(column)}
    `;

    if (!CellContent) {
        console.error("cannot create cell in column factory for column: ", column);
    }

    let CellToRender;
    if (columnNoWrapper.includes(column.key)) {
        CellToRender = <CellContent {...column} />;
    } else {
        let StyledCellWrapper = columnWrappers[column.columnKey];
        CellToRender = <StyledCellWrapper {...column} CellContent={CellContent} />;
    }

    return (<Column
        pureRendering={true}
        columnKey={column.key}
        width={column.width}
        fixed={column.fixed}
        header={getHeaderText(column, parentProps.userSettings)}
        align={getHeaderCellAlignment(column)}
        cell={CellToRender}
    />);
};

export default columnCreator;

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

function getHeaderText(column, userSettings) {
    if (!columnKeysMetricPropertyMap[column.key]) {
        return <HeaderCell title={column.headerText}>
            {column.headerText}
        </HeaderCell>
    }
    let metricProp = columnKeysMetricPropertyMap[column.key];
    let metricString = '(' + userSettings[metricProp] + ')';
    return column.headerText + ' ' + metricString;
}