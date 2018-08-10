define([
    'react',
    'fixed-data-table',
    'Product/Components/ProductList/CellCreator/factory',
    'Product/Components/ProductLinkEditor',
    'Product/Components/Footer',
    'Product/Components/ProductList/ColumnCreator/columns',
    'Product/Components/ProductList/ColumnCreator/factory',
], function(
    React,
    FixedDataTable,
    cellCreator,
    ProductLinkEditor,
    ProductFooter,
    columns,
    columnCreator,
) {
    "use strict";
    
    const {Table} = FixedDataTable;
    
    var CreateProduct = React.createClass({
        getDefaultProps: function() {
            return {
                products: [],
                features: {},
                accounts: []
            };
        },
        getInitialState: function() {
            return {
                pagination: {
                    total: 0,
                    limit: 0,
                    page: 0
                }
            }
        },
        componentDidMount() {
            // console.log('ProductList CDM this.props: ', this.props);
            this.updateDimensions();
            window.addEventListener("resize", this.updateDimensions);
            document.addEventListener("fullscreenchange", this.updateDimensions);
        },
        componentWillUnmount: function() {
            window.removeEventListener("resize", this.updateDimensions);
            document.removeEventListener("fullscreenchange", this.updateDimensions);
        },
        updateDimensions: function() {
            this.setState({
                productsListContainer: {
                    height: this.productsListContainer.clientHeight,
                    width: this.productsListContainer.clientWidth
                }
            })
        },
        renderSearchBox: function() {
            if (this.props.searchAvailable) {
                return <SearchBox initialSearchTerm={this.props.initialSearchTerm}
                                  submitCallback={this.filterBySearch}/>
            }
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
        onPageChange: function(pageNumber) {
            this.performProductsRequest(pageNumber, this.state.searchTerm, this.state.skuList);
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
                actions:props.actions
            });
        },
        getVisibleRows:function(){
          return this.props.products.visibleRows;
        },
        renderColumns: function() {
            return columns.map((column) => {
                column.actions = this.props.actions;
                column.products = this.props.products;
                let createdColumn = columnCreator(column);
                return createdColumn
            })
        },
        isReadyToRenderTable: function() {
            return this.state.productsListContainer && this.state.productsListContainer.height && this.props.products.visibleRows.length;
        },
        renderProducts: function() {
            if (!this.isReadyToRenderTable()) {
                return;
            }
            let height = this.state.productsListContainer.height;
            let width = this.state.productsListContainer.width;
            return (
                <Table
                    rowHeight={70}
                    rowsCount={this.getVisibleRows().length}
                    width={width}
                    height={height}
                    headerHeight={50}
                    data={this.getVisibleRows()}
                    footerHeight={0}
                    groupHeaderHeight={0}
                    showScrollbarX={true}
                    showScrollbarY={true}
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
                    <div
                        className='products-list__container'
                        ref={(productsListContainer) => this.productsListContainer = productsListContainer}
                    >
                        <div id="products-list">
                            {this.renderProducts()}
                        </div>
                        <ProductLinkEditor
                            productLink={this.state.editingProductLink}
                            onEditorClose={this.onProductLinksEditorClose}
                            fetchUpdatedStockLevels={this.fetchUpdatedStockLevels}
                        />
                    </div>
                    <ProductFooter
                        pagination={this.state.pagination}
                        onPageChange={this.onPageChange}
                    />
                </div>
            );
        }
    });
    
    return CreateProduct;
});
