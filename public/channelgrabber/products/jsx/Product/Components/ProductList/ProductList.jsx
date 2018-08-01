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
                },
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
        //todo - remove this commented code (it is only here for easy reference to the existing implementation of listings tab
        // getHoverText: function (listing) {
        //     var hoverText = {
        //         'active': 'This is an active listing with available stock',
        //         'pending': 'We have recently sent a stock update to this listing, and are currently waiting for '+$.trim(listing.channel)+' to confirm they have received and processed the stock update',
        //         'paused': 'Listing is paused due to no stock being available for sale',
        //         'error': 'We received an error when sending a stock update for this listing and so we are not currently able to manage the stock for this listing.',
        //         'inactive': 'You do not currently have this SKU listed in this location',
        //         'unimported': 'This listing has not yet been imported or does not exist'
        //     };
        //     return hoverText[$.trim(listing.status)];
        // },
        // getValues: function() {
        //     var values = [];
        //     for (var accountId in this.props.listingsPerAccount) {
        //         this.props.listingsPerAccount[accountId].map(function(listingId) {
        //             if (this.props.listings.hasOwnProperty(listingId)) {
        //                 var listing = this.props.listings[listingId];
        //                 var status = $.trim(listing.status);
        //                 var listingUrl = $.trim(listing.url);
        //                 values.push(<td title={this.getHoverText(listing)}><a target="_blank" href={listingUrl}><span className={"listing-status " + status}></span></a></td>);
        //             } else {
        //                 values.push(<td title={this.getHoverText({status: 'unimported'})}><span className={"listing-status unknown"}></span></td>);
        //             }
        //         }.bind(this));
        //     }
        //     return values;
        // },
        getListingHoverText:function(listing){
            var hoverText = {
                'active': 'This is an active listing with available stock',
                'pending': 'We have recently sent a stock update to this listing, and are currently waiting for '+$.trim(listing.channel)+' to confirm they have received and processed the stock update',
                'paused': 'Listing is paused due to no stock being available for sale',
                'error': 'We received an error when sending a stock update for this listing and so we are not currently able to manage the stock for this listing.',
                'inactive': 'You do not currently have this SKU listed in this location',
                'unimported': 'This listing has not yet been imported or does not exist'
            };
            return hoverText[$.trim(listing.status)];
        },
        isParentProduct: function(product) {
            return product.variationCount !== undefined && product.variationCount >= 1
        },
        getList: function() {
            const products = this.props.products;
            if (products && products.length <= 0) {
                return;
            }
            let list = products.map((product, i) => {
                console.log('product: ', product);
                console.log('isParentProduct(product): ', this.isParentProduct(product));
                
                let row = {
                    rowIndex: i,
                    values: [
                        {
                            columnKey: 'image',
                            value: 'http://via.placeholder.com/40'
                        },
                        {
                            columnKey:'parentProductExpand',
                            value: this.isParentProduct(product) ? 'parent' : 'variation'
                        },
                        {
                            columnKey: 'link',
                            value: 'https://app.dev.orderhub.io/products'
                        },
                        {
                            columnKey: 'sku',
                            value: product.sku
                        },
                        {
                            columnKey: 'name',
                            value: product.name
                        },
                        {
                            columnKey: 'available',
                            value: 0
                        }
                    ]
                };
                // let listingsTabData = [];
                // for (var accountId in product.listingsPerAccount) {
                //     let listingsInAccount = product.listingsPerAccount[accountId]
                //
                //     listingsInAccount.forEach((listingId) => {
                //         let listing = product.listings[listingId];
                //         let listingUrl = $.trim(listing.url);
                //         if(product.listings.hasOwnProperty(listingId)){
                //             let status = $.trim(listing.status);
                //             listingsTabData.push({
                //                 columnKey: listing.channel,
                //                 hoverText: this.getListingHoverText(listing),
                //                 listing,
                //                 status,
                //                 listingUrl
                //             })
                //         }else{
                //             let status = 'unimported';
                //             listingsTabData.push({
                //                 columnKey: listing.channel,
                //                 hoverText: this.getListingHoverText({status: 'unimported'}),
                //                 listing,
                //                 status,
                //                 listingUrl
                //             })
                //         }
                //     })
                // }
                // console.log('listingsTabData: ', listingsTabData);
                // row.values.concat(listingsTabData);
                // console.log('row: ', row);
                return row;
            });
        
            return list;
        },
        renderColumns: function(data) {
            // console.log('in renderCOlumns with data: ', data);
            if (!data || data.length === 0) {
                return;
            }
            return data.map((rowData) => {
                    return rowData.values.map((columnData, columnIndex) => {
                        let column = columnCreator({
                            data,
                            columnKey: columnData.columnKey,
                            columnIndex
                        });
                        return column
                        
                        
                    })
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
            
            // do not want to create the table until the dimensions have been captured from the container
            if (!this.state.productsListContainer || !this.state.productsListContainer.height || !data) {
                // console.log('breaking out... this.state.productsListContainer: ' , JSON.stringify(this.productsListContainer,null,1));
                return;
            } else {
                // console.log('no error so continuing forward: ' , JSON.stringify(this.state.productsListContainer,null,1));
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
                    {/*{(this.props.products.length ?*/}
                    {/*<ProductFooter*/}
                    {/*pagination={this.state.pagination}*/}
                    {/*onPageChange={this.onPageChange}*/}
                    {/*/> : ''*/}
                    {/*)}*/}
                    
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
