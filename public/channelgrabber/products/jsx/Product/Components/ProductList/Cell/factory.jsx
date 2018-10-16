import React from 'react';
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
import CellWrapper from 'Product/Components/ProductList/Cell/Wrapper';

"use strict";

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

export default (function() {
    return {
        createCellContent: function(column) {
            return getCreatedCell(column)
        },
        createCellWrapper: function() {
            return CellWrapper
        }
    };
    function getCreatedCell(column) {
        if (!column.products.visibleRows.length) {
            return () => (<Cell></Cell>)
        }
        let CellContentComponent = column.type ? cells[column.type] : cells[column.key];
        return CellContentComponent;
    }
}());