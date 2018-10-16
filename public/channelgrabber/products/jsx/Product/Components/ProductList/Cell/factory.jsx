import React from 'react';
import FixedDataTable from 'fixed-data-table-2';
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

class CellWrapper extends React.Component {
    render() {
        console.log('in CellWrapper with this.props', this.props);
//        let {CellContent} = this.props;
        console.log('CellContent in CellWrapper this.props.CellContent: ', this.props.CellContent);

        let CellContent = this.props.CellContent;
        return (
            <span>
                <CellContent {...this.props}/>
            </span>
        )
    };
}

export default (function() {
    return {
        createCellContent: function(column) {
            console.log('in createCell');
            return getCreatedCell(column)
        },
        createCellWrapper: function() {
            return CellWrapper
        }
    };
    function getCreatedCell(column) {
        console.log('in getCreatedCell column: ', column);
        if (!column.products.visibleRows.length) {
            return () => (<Cell></Cell>)
        }
        // todo - have an intermediary cell wrapper before hitting these cells to handle the blanks
        let CellContentComponent = column.type ? cells[column.type] : cells[column.key];
        return CellContentComponent;
    }
}());