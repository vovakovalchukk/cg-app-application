define([
    'react',
    'fixed-data-table',
    'Product/Components/ProductLinkEditor',
    'Product/Components/Footer',
    'Product/Components/ProductList/ColumnCreator/factory'
], function(
    React,
    FixedDataTable,
    ProductLinkEditor,
    ProductFooter,
    columnCreator
) {
    "use strict";
    
    const Table = FixedDataTable.Table;
    
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
            
            console.log('ProductList CDM this.props: ', this.props);
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
        getList: function() {
            const products = this.props.products.visibleRows;
            console.log('in getList with products: ', products);
            
            
            if (products && products.length <= 0) {
                return;
            }
            let list = products.map((product, i) => {
                let row = {
                    rowIndex: i,
                    values: [
                        {
                            key: 'debug',

                            width:120,
                            fixed:true,
                            headerText:'debug'
                        },
                        {
                            key: 'image',
                            width:100,
                            fixed:true,
                            headerText:'Image'
                        },
                        {
                            key:'productExpand',
                            width:50,
                            fixed:true,
                            headerText:'expand product'
                        },
                        {
                            key: 'link',
                            width:100,
                            fixed:true,
                            headerText:'Link'
                        },
                        {
                            key: 'sku',
                            width:200,
                            fixed:true,
                            headerText:'Sku'
                        },
                        {
                            key: 'name',
                            width:200,
                            fixed:true,
                            headerText:'Name'
                        },
                        {
                            key: 'available',
                            width:100,
                            fixed:true,
                            headerText:'Available'
                        },
                        //todo - change this dummy data to something significant in TAC-165
                        {
                            key: 'dummyListingColumn1',
                            width:200,
                            headerText:'dummy listing col',
                            fixed:false
    
                        },
                        {
                            key: 'dummyListingColumn2',
                            width:200,
                            headerText:'dummy listing col',
                            fixed:false
    
    
                        },
                        {
                            key: 'dummyListingColumn3',
                            width:200,
                            headerText:'dummy listing col',
                            fixed:false
    
    
                        },
                        {
                            key: 'dummyListingColumn4',
                            width:200,
                            headerText:'dummy listing col',
                            fixed:false
    
    
                        },
                        {
                            key: 'dummyListingColumn5',
                            width:200,
                            headerText:'dummy listing col',
                            fixed:false
    
                        },
                        {
                            key: 'dummyListingColumn6',
                            width:200,
                            headerText:'dummy listing col',
                            fixed:false
                        },
                        {
                            key: 'dummyListingColumn7',
                            width:200,
                            headerText:'dummy listing col',
                            fixed:false
    
    
                        },
                        {
                            key: 'dummyListingColumn8',
                            width:200,
                            headerText:'dummy listing col',
                            fixed:false
    
                        }
                    ]
                };
                return row;
            });
            return list;
        },
        renderColumns: function(list) {
            if (!list || list.length === 0) {
                return;
            }
            let columns = [];
            //todo - debug this part (this is where error occurs
            list.forEach((rowData) => {
                    columns = rowData.values;
                }
            );
            
            return columns.map((column, columnIndex) => {
                let {key,width,fixed,headerText} = column;
                let createdColumn = columnCreator({
                    key,
                    width,
                    headerText,
                    fixed,
                    columnIndex
                });

                return createdColumn
            })
        },
        isReadyToRenderTable: function(data) {
            return this.state.productsListContainer && this.state.productsListContainer.height && data;
        },
        renderProducts: function() {
            let list = this.getList();
            // do not create the table until the dimensions have been captured from the initial render
            if (!this.isReadyToRenderTable(list)) {
                return;
            }
            let height = this.state.productsListContainer.height;
            let width = this.state.productsListContainer.width;
            return (
                <Table
                    rowHeight={70}
                    rowsCount={list.length}
                    width={width}
                    height={height}
                    headerHeight={50}
                    data={list}
                    footerHeight={0}
                    groupHeaderHeight={0}
                    showScrollbarX={true}
                    showScrollbarY={true}
                >
                    {this.renderColumns(list)}
                </Table>
            )
        },
        render: function() {
            console.log('ProductList render this.props: ',this.props);
            
            
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
