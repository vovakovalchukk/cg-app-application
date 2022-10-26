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

const ORDER_COLUMNS = ['Sku', 'Name', 'Weight', 'HS Tariff Number', 'Country Of Manufacture', ];

export default (function () {
    return {
        createHeaderCellContent: function (column, props, orderState, setOrder) {
            // if (columnKeysMetricPropertyMap[column.key]) {
            //     console.log(column.headerText)
            //     debugger
            //     return getHeaderTextWithMetricInfo(column, props.userSettings);
            // }
            let CellContent = {};

            if (cells[column.key]) {
                CellContent = cells[column.key]
                return (<HeaderCellContainer>
                    <CellContent {...column}/>
                </HeaderCellContainer>);
            }
            console.log(column.headerText == 'Country Of Manufacture')
            let onClickOrder = null
            let className
            if (ORDER_COLUMNS.includes(column.headerText)) {
                // if (column.headerText == 'Country Of Manufacture'){
                //     debugger
                // }

                let columnName = column.headerText.toLowerCase().replaceAll(' ', '');
                let order
                className = 'sorting'
                onClickOrder = async (event) => {
                    let currentOrder = orderState.order.split(',');
                    order = createNewOrder(columnName, currentOrder[0], currentOrder[1], event);
                    setOrder(order);
                    await props.actions.getProducts(props.pagination.page, '', [], order);
                }
            }
            return (
                <HeaderCellContainer title={column.headerText} id={column.headerText.toLowerCase()} onClick={onClickOrder}
                                     className={className}>
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

function changeOrderDirection(direction, event) {
    if (direction == 'asc') {
        event.currentTarget.classList.add('sorting_desc')
        event.currentTarget.classList.remove('sorting_asc')
        return 'desc'
    } else if (direction == 'desc') {
        event.currentTarget.classList.remove('sorting_desc')
        event.currentTarget.classList.add('sorting')
        return ''
    } else {
        event.currentTarget.classList.add('sorting_asc')
        return 'asc'
    }
}

function createNewOrder(currentColumn, previousOrderColumn, previousOrderDirection, event) {
    if (currentColumn == previousOrderColumn) {
        let newDirection = changeOrderDirection(previousOrderDirection, event);
        if (newDirection) {
            return currentColumn + ',' + newDirection;
        } else {
            return '';
        }
    } else {
        if (previousOrderColumn != '') {
            let oldColumnSort = document.getElementById(previousOrderColumn)
            oldColumnSort.classList.add('sorting')
            oldColumnSort.classList.remove('sorting_' + previousOrderDirection)
        }
        event.currentTarget.classList.add('sorting_asc')
        event.currentTarget.classList.remove('sorting')
        return currentColumn + ',' + 'asc';
    }
}

const columnKeysMetricPropertyMap = {
    [columnKeys.weight]: 'massUnit',
    [columnKeys.dimensions]: 'lengthUnit'
};