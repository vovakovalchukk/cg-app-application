define([
    'react',
    'fixed-data-table',
    'Product/Components/ProductLinkEditor',
    'Product/Components/Footer',
    'Product/Components/ProductList/Service/columnCreator'
], function(
    React,
    FixedDataTable,
    ProductLinkEditor,
    ProductFooter,
    columnCreator
) {
    "use strict";
    
    const Cell = FixedDataTable.Cell;
    const Table = FixedDataTable.Table;
    const Column = FixedDataTable.Column;
    
    
    var CreateProduct = React.createClass({
        getDefaultProps: function() {
            return {
                products: [],
                features: {}
            };
        },
        getInitialState: function() {
            return {
                pagination: {
                    total: 0,
                    limit: 0,
                    page: 0
                },
            }
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
        getList: function() {
            let rowCount = 50;
            //todo - replace this dummy data with something significant
            return Array(rowCount).fill().map((val, i) => {
                return {
                    // id: i,
                    image: 'http://via.placeholder.com/40',
                    link: 'https://app.dev.orderhub.io/products',
                    sku: 'sku ' + i,
                    name: 'Product Name ' + i,
                    available: 0,
                }
            });
        },
     
        renderTextColumn: function() {
        
        },
        renderColumns: function(data) {
            
            let columnKeys = Object.keys(data[0]);
            
            console.log('columnKeys: ', columnKeys);
            
            return columnKeys.map( (columnKey,columnIndex) => {
                    return columnCreator({
                        data,
                        columnKey,
                        columnIndex
                    });
                }
            );
        },
        renderProducts: function() {
            // if (this.props.products.length === 0 && this.state.initialLoadOccurred) {
            //     return (
            //         <div className="no-products-message-holder">
            //             <span className="sprite-noproducts"></span>
            //             <div className="message-holder">
            //                 <span className="heading-large">No Products to Display</span>
            //                 <span className="message">Please Search or Filter</span>
            //             </div>
            //         </div>
            //     );
            // }
            let data = this.getList();
            // return {
            //     id: i,
            //     image: 'http://via.placeholder.com/40',
            //     link: 'https://app.dev.orderhub.io/products',
            //     sku:'sku '+i,
            //     name: 'Product Name '+i,
            //     available:0,
            //     text: 'lorem  sdfoisjdofnsigndigfdifgberineorgn'
            // }
            return (
                <Table
                    rowHeight={50}
                    rowsCount={data.length}
                    width={1000}
                    height={400}
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
                    {this.renderSearchBox()}
                    {this.props.features.createProducts ? this.renderAddNewProductButton() : 'cannot create'}
                    
                    <div className='products-list__container'>
                        <div id="products-list">
                            {this.renderProducts()}
                        </div>
                        <ProductLinkEditor
                            productLink={this.state.editingProductLink}
                            onEditorClose={this.onProductLinksEditorClose}
                            fetchUpdatedStockLevels={this.fetchUpdatedStockLevels}
                        />
                        {(this.props.products.length ?
                                <ProductFooter
                                    pagination={this.state.pagination}
                                    onPageChange={this.onPageChange}
                                /> : ''
                        )}
                    </div>
                </div>
            );
        }
    });
    
    return CreateProduct;
});
