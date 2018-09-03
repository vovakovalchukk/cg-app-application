define([
    'react',
    'fixed-data-table',
    'styled-components',
    'Product/Components/ProductList/CellCreator/factory',
    'Product/Components/ProductLinkEditor',
    'Product/Components/ProductList/Components/Footer/Footer',
    'Product/Components/ProductList/ColumnCreator/columns',
    'Product/Components/ProductList/ColumnCreator/factory',
    'Product/Components/ProductList/Components/Tabs/Root'
], function(
    React,
    FixedDataTable,
    styled,
    cellCreator,
    ProductLinkEditor,
    ProductFooter,
    columns,
    columnCreator,
    Tabs
) {
    "use strict";
    
    const {Table} = FixedDataTable;
    
    var ProductList = React.createClass({
        getDefaultProps: function() {
            return {
                products: [],
                features: {},
                accounts: [],
                actions: {},
                tabs: {}
            };
        },
        getInitialState: function() {
            return {
                pagination: {
                    total: 0,
                    limit: 0,
                    page: 0
                },
                editingProductLink: {
                    sku: "",
                    links: []
                },
                productsListContainer: {
                    height: null,
                    width: null
                },
                fetchingUpdatedStockLevelsForSkus: {}
            }
        },
        componentDidMount() {
            this.updateDimensions();
            window.addEventListener("resize", this.updateDimensions);
            document.addEventListener("fullscreenchange", this.updateDimensions);
            window.addEventListener('productLinkEditClicked', this.onEditProductLink, false);
            window.addEventListener('productLinkRefresh', this.onProductLinkRefresh, false);
        },
        componentWillUnmount: function() {
            window.removeEventListener("resize", this.updateDimensions);
            document.removeEventListener("fullscreenchange", this.updateDimensions);
            window.removeEventListener('productLinkEditClicked', this.onEditProductLink, false);
            window.removeEventListener('productLinkRefresh', this.onProductLinkRefresh, false);
        },
        componentWillReceiveProps: function() {
            var horizontalScrollbar = document.getElementsByClassName("ScrollbarLayout_face ScrollbarLayout_faceHorizontal public_Scrollbar_face")[0];
            if (horizontalScrollbar) {
                horizontalScrollbar.addEventListener('mousedown', this.updateHorizontalScrollIndex);
            }
        },
        updateDimensions: function() {
            this.setState({
                productsListContainer: {
                    height: this.productsListContainer.clientHeight,
                    width: this.productsListContainer.clientWidth
                }
            })
        },
        updateHorizontalScrollIndex: function() {
            this.props.actions.resetHorizontalScrollbarIndex();
        },
        //todo - use this as a basis for implementing functionality for TAC-173
        onPageChange: function(pageNumber) {

            // todo - change the below request to trigger a products request within Redux in TAC-173
            //     this.performProductsRequest(pageNumber, <searchTerm>, <skuList>);
        },
        onProductLinkRefresh: function(event) {
            let sku = event.detail;
            this.props.actions.getLinkedProducts([sku]);
        },
        renderSearchBox: function() {
            if (this.props.searchAvailable) {
                return <SearchBox initialSearchTerm={this.props.initialSearchTerm}
                                  submitCallback={this.filterBySearch}/>
            }
        },
        onEditProductLink: function(event) {
            let {sku, productLinks} = event.detail;
            this.setState({
                editingProductLink: {
                    sku,
                    links: productLinks
                }
            });
        },
        renderAddNewProductButton: function() {
            return (
                <div className=" navbar-strip--push-up-fix ">
                        <span className="navbar-strip__button " onClick={this.props.addNewProductButtonClick}>
                            <span className="fa-plus left icon icon--medium navbar-strip__button__icon">&nbsp;</span>
                            <span className="navbar-strip__button__text">Add</span>
                        </span>
                </div>
            )
        },
        onProductLinksEditorClose: function() {
            this.setState({
                editingProductLink: {
                    sku: "",
                    links: []
                }
            });
        },
        isParentProduct: function(product) {
            return product.variationCount !== undefined && product.variationCount >= 1
        },
        renderCell: function(props) {
            let {columnKey, rowIndex} = props;
            
            return cellCreator({
                columnKey,
                rowIndex,
                products: props.products,
                actions: props.actions
            });
        },
        getVisibleRows: function() {
            return this.props.products.visibleRows;
        },
        isTabSpecificColumn: function(column) {
            return !!column.tab;
        },
        isColumnSpecificToCurrentTab: function(column) {
            return column.tab === this.props.tabs.currentTab
        },
        renderColumns: function() {
            return columns.map((column) => {
                column.actions = this.props.actions;
                column.products = this.props.products;
                if (this.isTabSpecificColumn(column) && !this.isColumnSpecificToCurrentTab(column)) {
                    return;
                }
                let createdColumn = columnCreator(column);
                return createdColumn
            })
        },
        isReadyToRenderTable: function() {
            return this.state.productsListContainer && this.state.productsListContainer.height && this.props.products.simpleAndParentProducts && this.getVisibleRows() && this.getVisibleRows().length;
        },
        renderProducts: function() {
            let rows = this.getVisibleRows();
            if (!this.isReadyToRenderTable()) {
                return;
            }
            let height = this.state.productsListContainer.height;
            let width = this.state.productsListContainer.width;
            return (
                <Table
                    rowHeight={70}
                    rowsCount={rows.length}
                    width={width}
                    height={height}
                    headerHeight={50}
                    data={rows}
                    footerHeight={0}
                    groupHeaderHeight={0}
                    showScrollbarX={true}
                    showScrollbarY={true}
                    scrollToColumn={this.props.tabs.currentColumnScrollIndex}
                >
                    {this.renderColumns()}
                </Table>
            )
        },
        render: function() {
            return (
                <div id='products-app'>
                    <div className="top-toolbar">
                        {this.renderSearchBox()}
                        {this.props.features.createProducts ? this.renderAddNewProductButton() : 'cannot create'}
                    </div>
                    <Tabs/>
                    <div
                        className='products-list__container'
                        ref={(productsListContainer) => this.productsListContainer = productsListContainer}
                    >
                        <div id="products-list">
                            {this.renderProducts()}
                        </div>
                    </div>
                    <ProductLinkEditor
                        productLink={this.state.editingProductLink}
                        onEditorClose={this.onProductLinksEditorClose}
                        fetchUpdatedStockLevels={this.props.actions.getUpdatedStockLevels}
                    />
                    <ProductFooter
                        pagination={this.props.pagination}
                        actions={{
                            changePage:this.props.actions.changePage,
                            changeLimit:this.props.actions.changeLimit
                        }}
                    />
                </div>
            );
        }
    });
    
    return ProductList;
})
;
