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
                let row = {
                    rowIndex: i,
                    values: [
                        {
                            key: 'debug',
                            width:100,
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
                            key:'parentProductExpand',
                            width:100,
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
            
            return columns.map((column, columnIndex) => {
                let {key,width,fixed,headerText} = column;
                let createdColumn = columnCreator({
                    key,
                    width,
                    headerText,
                    fixed,
                    columnIndex
                });
                // /
                return createdColumn
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
