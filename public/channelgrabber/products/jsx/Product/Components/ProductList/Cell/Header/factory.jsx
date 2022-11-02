import React from 'react';
import BulkSelectCell from 'Product/Components/ProductList/Cell/Header/BulkSelect';
import ProductExpandCell from 'Product/Components/ProductList/Cell/Header/ProductExpand';
import FixedDataTable, {Cell} from 'fixed-data-table-2';
import columnKeys from "../../Column/columnKeys";
import styled from "styled-components";
import styleVars from "../../styleVars";

"use strict";

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

const ORDER_COLUMNS = ['Sku', 'Name', 'Weight', 'HS Tariff Number', 'Country Of Manufacture', 'Cost Price', 'Available',
    'Awaiting Dispatch', 'Awaiting Dispatch', 'Stock on Order'];

export default (function () {
    return {
        createHeaderCellContent: function (column, props) {
            let CellContent = {};

            if (cells[column.key]) {
                CellContent = cells[column.key]
                return (<HeaderCellContainer>
                    <CellContent {...column}/>
                </HeaderCellContainer>);
            }
            let onClickSort = null,
                className = '',
                columnName = column.headerText.toLowerCase().replaceAll(' ', '');
            if (ORDER_COLUMNS.includes(column.headerText)) {
                if (columnName === 'costprice') {
                    columnName = 'cost';
                } else if (columnName === 'available') {
                    columnName = 'onhand';
                } else if (columnName === 'awaitingdispatch') {
                    columnName = 'allocated'
                } else if (columnName === 'stockonorder') {
                    columnName = 'onpurchaseorder'
                }
                className = 'sorting'

                let currentSort = props.sort;
                if (currentSort.length) {
                    let sort = currentSort[0];
                    if (sort.column === columnName) {
                        className += (' sorting_' + sort.direction);
                    }
                }

                onClickSort = () => {
                    props.actions.sortBy(columnName);
                }
            }
            return (
                <HeaderCellContainer title={column.headerText} id={columnName} onClick={onClickSort}
                                     className={className}>
                    {getHeaderTextWithMetricInfo(column, props.userSettings)}
                </HeaderCellContainer>
            );
        }
    };
}());

function getHeaderTextWithMetricInfo(column, userSettings) {
    if (columnKeysMetricPropertyMap[column.key] && column.headerText.slice(column.headerText.length - 1) != ')') {
        let metricProp = columnKeysMetricPropertyMap[column.key];
        let metricString = '(' + userSettings[metricProp] + ')';
        return column.headerText + ' ' + metricString;
    }
    return column.headerText
}

const columnKeysMetricPropertyMap = {
    [columnKeys.weight]: 'massUnit',
    [columnKeys.dimensions]: 'lengthUnit'
};