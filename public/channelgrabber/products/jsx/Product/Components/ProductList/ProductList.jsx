define([
    'react',
    'fixed-data-table',
    'Product/Components/ProductList/tableDataWrapper',
    'Product/Components/ProductLinkEditor',
    'Product/Components/Footer',
    'Product/Components/ProductList/ColumnCreator/factory'
], function(
    React,
    FixedDataTable,
    tableDataWrapper,
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
            this.updateDimensions();
            window.addEventListener("resize", this.updateDimensions);
            document.addEventListener("fullscreenchange", this.updateDimensions);
    
           
            
        },
        componentWillUnmount: function() {
            window.removeEventListener("resize", this.updateDimensions);
            document.removeEventListener("fullscreenchange", this.updateDimensions);
        },
        shouldComponentUpdate:function(){
          if(this.dataShouldBeStored()){
              tableDataWrapper.storeData(this.props.products);
              console.log('just storedData... ' , tableDataWrapper.getData());
          }
          return true;
        },
        dataShouldBeStored:function(){
            return this.props.products.length && (tableDataWrapper.getData() !== this.props.products)
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
        getTableDataWrapper: function(){
            //todo -- eventually get this returning a js class that acts as a filter tool / row extraction tool
            // console.log('in getTableDataWrapper witht his.props.products: ' , this.props.products);
            // console.log('tableDatWrapper : ' , tableDataWrapper);
            
            
            
            return tableDataWrapper;
        },
        getList: function() {
            const products = this.props.products;
            if (products && products.length <= 0) {
                return;
            }
            let list = products.map((product, i) => {
                //
                let row = {
                    rowIndex: i,
                    values: [
                        {
                            columnKey: 'debug',
                            value: 'a'
                        },
                        {
                            columnKey: 'image',
                            value: 'http://via.placeholder.com/40',
                        },
                        {
                            columnKey:'parentProductExpand',
                            value: this.isParentProduct(product) ? 'parent' : '',
    
                        },
                        {
                            columnKey: 'link',
                            value: 'https://app.dev.orderhub.io/products',
    
                        },
                        {
                            columnKey: 'sku',
                            // value: product.sku,
                        },
                        {
                            columnKey: 'name',
                            value: product.name,
                            // rowIndex:i,
                            // product
                        },
                        {
                            columnKey: 'available',
                            value: 0,
                            // rowIndex:i,
                            // product
                        },
                        //todo - change this dummy data to something significant in TAC-165
                        {
                            columnKey: 'dummyListingColumn1',
                            value: 1
                        },
                        {
                            columnKey: 'dummyListingColumn2',
                            value: 2
                        },
                        {
                            columnKey: 'dummyListingColumn3',
                            value: 3
                        },
                        {
                            columnKey: 'dummyListingColumn4',
                            value: 4
                        },
                        {
                            columnKey: 'dummyListingColumn5',
                            value: 4
                        },
                        {
                            columnKey: 'dummyListingColumn6',
                            value: 4
                        },
                        {
                            columnKey: 'dummyListingColumn7',
                            value: 4
                        },
                        {
                            columnKey: 'dummyListingColumn8',
                            value: 4
                        }
                    ]
                };
                return row;
            });
            return list;
        },
        renderColumns: function(data) {
            if (!data || data.length === 0) {
                return;
            }
            let columns = [];
            //todo - debug this part (this is where error occurs
            data.forEach((rowData) => {
                    columns = rowData.values;
                }
            );
            
            
            return columns.map((columnData, columnIndex) => {
                let {columnKey, product} = columnData;
                let column = columnCreator({
                    columnKey,
                    columnIndex,
                    data: this.getTableDataWrapper()
                });
                // /
                return column
            })
        },
        isReadyToRenderTable: function(data) {
            return this.state.productsListContainer && this.state.productsListContainer.height && data;
        },
        renderProducts: function() {
            let data = this.getList();
            // do not create the table until the dimensions have been captured from the initial render
            if (!this.isReadyToRenderTable(data)) {
                return;
            }
            let height = this.state.productsListContainer.height;
            let width = this.state.productsListContainer.width;
            return (
                <Table
                    rowHeight={50}
                    rowsCount={data.length}
                    width={width}
                    height={height}
                    headerHeight={50}
                    data={data}
                    rowGetter={(index) => {
                        return data[index];
                    }}
                    footerHeight={0}
                    groupHeaderHeight={0}
                    showScrollbarX={true}
                    showScrollbarY={true}
                >
                    {this.renderColumns(data)}
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
