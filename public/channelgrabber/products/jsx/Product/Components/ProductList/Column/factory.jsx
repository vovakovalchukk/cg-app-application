import react from 'react';
import styled from 'styled-components';
import FixedDataTable from 'fixed-data-table';
import TextCell from 'Product/Components/ProductList/Cell/Text';
import ProductExpandCell from 'Product/Components/ProductList/Cell/ProductExpand';
import ImageCell from 'Product/Components/ProductList/Cell/Image';
import NameCell from 'Product/Components/ProductList/Cell/Name';
import ListingAccountCell from 'Product/Components/ProductList/Cell/ListingAccount';
import AddListingCell from 'Product/Components/ProductList/Cell/AddListing';
import StockModeCell from 'Product/Components/ProductList/Cell/StockMode';
import WeightCell from 'Product/Components/ProductList/Cell/Weight';
import DimensionsCell from 'Product/Components/ProductList/Cell/Dimensions'
import VatCell from 'Product/Components/ProductList/Cell/Vat'
import LinkCell from 'Product/Components/ProductList/Cell/Link';
import AvailableCell from 'Product/Components/ProductList/Cell/Available';
import BulkSelectCell from 'Product/Components/ProductList/Cell/BulkSelect';

"use strict";

const Column = FixedDataTable.Column;
const Cell = FixedDataTable.Cell;

let cells = {
    productExpand: ProductExpandCell,
    image: ImageCell,
    bulkSelect: BulkSelectCell,
    link: LinkCell,
    sku: TextCell,
    name: NameCell,
    available: AvailableCell,
    listingAccount: ListingAccountCell,
    addListing: AddListingCell,
    stockMode: StockModeCell,
    weight: WeightCell,
    dimensions: DimensionsCell,
    vat: VatCell
};

let columnCreator = function(column, parentProps) {
    column.actions = parentProps.actions;
    column.products = parentProps.products;
    column = applyColumnSpecificProps(column, parentProps);
    
    let CreatedCell = getCreatedCell(column);
    let StyledCell = styled(CreatedCell)`
            display: flex;
            align-items: center;
            height: 100%;
            width:100%;
            box-sizing: border-box;
            padding-left:1rem;
            padding-right:1rem;
            justify-content:${getJustifyContentProp(column)}
        `;
    
    if (!CreatedCell) {
        console.error("cannot create cell in column factory for column: ", column);
    }
    
    return (<Column
        columnKey={column.key}
        width={column.width}
        fixed={column.fixed}
        header={column.headerText}
        align={getHeaderCellAlignment(column)}
        cell={<StyledCell
            {...column}
            products={column.products}
            actions={column.actions}
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

function getCreatedCell(column) {
    if (!column.products.visibleRows.length) {
        return () => (<Cell></Cell>)
    }
    return column.type ? cells[column.type] : cells[column.key];
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
