import React from 'react';
import styled from 'styled-components';
import FixedDataTable from 'fixed-data-table-2';
import CellFactory from 'Product/Components/ProductList/Cell/factory';
import columnKeys from 'Product/Components/ProductList/Column/columnKeys'
import StockModeCell from 'Product/Components/ProductList/Cell/StockMode';
import CellWrapper from 'Product/Components/ProductList/Cell/Wrapper';

"use strict";

const Column = FixedDataTable.Column;

const columnKeysMetricPropertyMap = {
    [columnKeys.weight]: 'massUnit',
    [columnKeys.dimensions]: 'lengthUnit'
};
const columnSpecificPropsMap = {
    stockMode: ['stock', 'rows', 'userSettings'],
    available: ['rows'],
    dimensions: ['rows'],
    weight: ['rows'],
    vat: ['rows', 'vat'],
    bulkSelect: ['bulkSelect']
};
const alignFlexMap = {
    'center': 'center',
    'left': 'flex-start',
    'right': 'flex-end'
};

let columnCreator = function(column, parentProps) {
    column.actions = parentProps.actions;
    column.products = parentProps.products;
    column = applyColumnSpecificProps(column, parentProps);

    let CellContent = CellFactory.createCellContent(column);

    let StyledCellWrapper = styled(CellWrapper)`
            display: flex;
            align-items: center;
            height: 100%;
            width:100%;
            box-sizing: border-box;
            justify-content:${getJustifyContentProp(column)}
        `;

    if (!CellContent) {
        console.error("cannot create cell in column factory for column: ", column);
    }

    let CellToRender = StyledCellWrapper;

    // bypassing the CellWrapper for certain cells for performance reasons
    if (column.key === columnKeys.stockMode) {
        CellToRender = StockModeCell;
    }

    return (<Column
        pureRendering={true}
        columnKey={column.key}
        width={column.width}
        fixed={column.fixed}
        header={getHeaderText(column, parentProps.userSettings)}
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
    let keysToAssign = columnSpecificPropsMap[column.key] ? columnSpecificPropsMap[column.key] : columnSpecificPropsMap[column.type];
    if (!keysToAssign) {
        return column;
    }
    keysToAssign.forEach(keyToAssign => {
//        console.log('assigning ', keyToAssign);


        column[keyToAssign] = parentProps[keyToAssign]

//        console.log('column[keyToAssign]', column[keyToAssign]);


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