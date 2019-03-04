import React from 'react';
import BulkSelectCell from 'Product/Components/ProductList/Cell/Header/BulkSelect';
import ProductExpandCell from 'Product/Components/ProductList/Cell/Header/ProductExpand';
import FixedDataTable from 'fixed-data-table-2';
import columnKeys from "../../Column/columnKeys";
import styled from "styled-components";
import styleVars from "../../styleVars";

"use strict";

const Cell = FixedDataTable.Cell;

let HeaderCellContainer = styled(Cell)`
    .public_fixedDataTableCell_cellContent{
      max-height: ${styleVars.heights.headerHeight}px;  
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
    }
`;

let cells = {
    productExpand: ProductExpandCell,
    bulkSelect: BulkSelectCell
};

export default (function() {
    return {
        createHeaderCellContent: function(column, userSettings) {
            if (columnKeysMetricPropertyMap[column.key]) {
                return getHeaderTextWithMetricInfo(column, userSettings);
            }
            let CellContent = {};
            if(cells[column.key]){
                CellContent = cells[column.key]
                debugger;
                return (<HeaderCellContainer>
                    <CellContent {...column}/>
                </HeaderCellContainer>);
            }
            return  (
                <HeaderCellContainer title={column.headerText}>
                    {column.headerText}
                </HeaderCellContainer>
            );
        }
    };
}());

function getHeaderTextWithMetricInfo(column, userSettings) {
    let metricProp = columnKeysMetricPropertyMap[column.key];
    let metricString = '(' + userSettings[metricProp] + ')';
    return column.headerText + ' ' + metricString;
}

const columnKeysMetricPropertyMap = {
    [columnKeys.weight]: 'massUnit',
    [columnKeys.dimensions]: 'lengthUnit'
};