import React from 'react';
import styled from 'styled-components';
import FixedDataTable from 'fixed-data-table-2';
import CellFactory from 'Product/Components/ProductList/Cell/factory';
import columnKeys from 'Product/Components/ProductList/Column/columnKeys'
import CellWrapper from 'Product/Components/ProductList/Cell/Wrapper';

"use strict";

const Column = FixedDataTable.Column;

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
    name: ['name']
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
        return column.headerText;
    }
    let metricProp = columnKeysMetricPropertyMap[column.key];
    let metricString = '(' + userSettings[metricProp] + ')';
    return column.headerText + ' ' + metricString;
}