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
import NoVatCell from 'Product/Components/ProductList/Cell/NoVat';
import FixedDataTable from 'fixed-data-table-2';
import AllocatedCell from 'Product/Components/ProductList/Cell/Allocated';
import LowStock from 'Product/Components/ProductList/Cell/LowStock';
import PickingLocationCell from 'Product/Components/ProductList/Cell/PickingLocation';
import OnPurchaseOrderCell from 'Product/Components/ProductList/Cell/OnPurchaseOrder';
import IncludePurchaseOrdersInAvailableCell from 'Product/Components/ProductList/Cell/IncludePurchaseOrdersInAvailable';
import CostCell from 'Product/Components/ProductList/Cell/Cost';
import FulfillmentLatencyCell from 'Product/Components/ProductList/Cell/FulfillmentLatency';
import BarcodeCell from 'Product/Components/ProductList/Cell/Barcode';

"use strict";

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
    vat: VatCell,
    noVat: NoVatCell,
    allocated: AllocatedCell,
    pickingLocation: PickingLocationCell,
    onPurchaseOrder: OnPurchaseOrderCell,
    includePurchaseOrdersInAvailable: IncludePurchaseOrdersInAvailableCell,
    lowStock: LowStock,
    cost: CostCell,
    fulfillmentLatency: FulfillmentLatencyCell,
    barcode: BarcodeCell
};

export default (function() {
    return {
        createCellContent: function(column) {
            return getCreatedCell(column)
        }
    };
    function getCreatedCell(column) {
        if (!column.products.visibleRows.length) {
            return Cell
        }
        return column.type ? cells[column.type] : cells[column.key];
    }
}());