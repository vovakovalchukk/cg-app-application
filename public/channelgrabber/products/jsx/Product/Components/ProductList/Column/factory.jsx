define([
    'react',
    'fixed-data-table',
    'Product/Components/ProductList/Cell/Text',
    'Product/Components/ProductList/Cell/ProductExpand',
    'Product/Components/ProductList/Cell/Image',
    'Product/Components/ProductList/Cell/Link',
    'Product/Components/ProductList/Cell/Name',
    'Product/Components/ProductList/Cell/ListingAccount',
    'Product/Components/ProductList/Cell/AddListing',
    'Product/Components/ProductList/Cell/StockMode',
    'Product/Components/ProductList/Cell/Weight',
    'Product/Components/ProductList/Cell/Dimensions',
    'Product/Components/ProductList/Cell/Vat',
    'Product/Components/ProductList/Column/columnKeys'
], function(
    React,
    FixedDataTable,
    TextCell,
    ProductExpandCell,
    ImageCell,
    LinkCell,
    NameCell,
    ListingAccountCell,
    AddListingCell,
    StockModeCell,
    WeightCell,
    DimensionsCell,
    VatCell,
    columnKeys
) {
    "use strict";
    
    const Column = FixedDataTable.Column;
    
    let cells = {
        productExpand: ProductExpandCell,
        image: ImageCell,
        link: LinkCell,
        sku: TextCell,
        name: NameCell,
        available: TextCell,
        listingAccount: ListingAccountCell,
        addListing: AddListingCell,
        //todo to be replaced in 215
        stockMode: StockModeCell,
        weight: WeightCell,
        dimensions: DimensionsCell,
        vat: VatCell
    };
    
    var columnCreator = function(column, parentProps) {
        column.actions = parentProps.actions;
        column.products = parentProps.products;
        column = applyColumnSpecificProps(column, parentProps);
        
        let CreatedCell = getCreatedCell(column);
        if(!CreatedCell){
            console.error("cannot create cell in column factory for column: " , column);
        }
        return (<Column
            columnKey={column.key}
            width={column.width}
            fixed={column.fixed}
            header={column.headerText}
            cell={<CreatedCell
                {...column}
                products={column.products}
                actions={column.actions}
            />}
        />);
    };
    
    return columnCreator;
    
    function applyColumnSpecificProps(column, parentProps){
        if(column.key===columnKeys.stockMode){
            column.stock = parentProps.stock
        }
        return column;
    }
    function getCreatedCell(column){
        return column.type ? cells[column.type] : cells[column.key];
    }
});
