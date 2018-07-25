define([
    'react',
    'fixed-data-table',
    'Product/Components/ProductLinkEditor',
    'Product/Components/Footer'

], function(
    React,
    FixedDataTable,
    ProductLinkEditor,
    ProductFooter
) {
    "use strict";
    
    const Cell = FixedDataTable.Cell;
    const Table = FixedDataTable.Table;
    const Column = FixedDataTable.Column;
    
    
    var CreateProduct = React.createClass({
        getDefaultProps: function() {
            return {
                products:[],
                features:{}
            };
        },
        getInitialState: function(){
            return{
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
        getList: function(){
            let rowCount = 50;
            //todo - replace this dummy data with something significant
            return Array(rowCount).fill().map((val, i) => {
                return {
                    id: i,
                    image: 'http://via.placeholder.com/40',
                    link: 'https://app.dev.orderhub.io/products',
                    sku:'sku '+i,
                    name: 'Product Name '+i,
                    available:0,
                    text: 'lorem  sdfoisjdofnsigndigfdifgberineorgn'
                }
            });
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
            let rows = this.getList();
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
                    rowsCount={rows.length}
                    width={1000}
                    height={400}
                    headerHeight={50}
                    data={rows}
                    rowGetter={(index)=>{
                        return rows[index];
                    }}
                    footerHeight={0}
                    groupHeaderHeight={0}
                    showScrollbarX={true}
                    showScrollbarY={true}
                >
                    <Column
                        columnKey="id"
                        width={300}
                        label="id"
                        header={<Cell> id head </Cell>}
                        cell={ props => {
                            return(
                                <Cell>
                                    {props.rowIndex}
                                </Cell>
                            );
                        }}
                    />
                    <Column
                        columnKey="sku"
                        width={300}
                        label="sku"
                        header={<Cell> sku head </Cell>}
                        cell={ props => {
                            return(
                                <Cell>
                                    {rows[props.rowIndex][props.columnKey]}
                                </Cell>
                            );
                        }}
                    />
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
            // return (
            //     <div>
            //         <br/>
            //         <br/>
            //         <br/>
            //         <br/>
            //         <br/>
            //         <br/>
            //         <br/>
            //         <br/>
            //         <br/>
            //         <br/>
            //         <br/>
            //         in the product list app
            //
            //     </div>
            // );
        }
    });
    
    return CreateProduct;
});
