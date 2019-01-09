import React from 'react';
import FixedDataTable from 'fixed-data-table-2';
import SearchBox from 'Product/Components/Search';
import ProductLinkEditor from 'Product/Components/ProductLinkEditor';
import ProductFooter from 'Product/Components/ProductList/Components/Footer/Container';
import columnCreator from 'Product/Components/ProductList/Column/factory';
import Tabs from 'Product/Components/ProductList/Components/Tabs/Root';
import NavbarButton from 'Product/Components/ProductList/Components/Navbar/Button';
import constants from 'Product/Components/ProductList/Config/constants';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import visibleRowService from 'Product/Components/ProductList/VisibleRow/service';
import BlockerModal from 'Common/Components/BlockerModal';

"use strict";

const {Table} = FixedDataTable;

class ProductList extends React.Component {
    static defaultProps = {
        products: [],
        features: {},
        accounts: {},
        actions: {},
        tabs: {}
    };

    state = {
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
    };

    updateDimensions = () => {
        this.setState({
            productsListContainer: {
                height: this.productsListContainer.clientHeight,
                width: this.productsListContainer.clientWidth
            }
        })
    };
    updateHorizontalScrollIndex = () => {
        this.props.actions.resetHorizontalScrollbarIndex();
    };
    onProductLinkRefresh = (event) => {
        let {sku, links} = event.detail

        console.log('on ProductLinksRefresh (this is being triggered by the event listener...)', {sku, links});


        this.props.actions.getLinkedProducts([sku], links);
    };
    onEditProductLink = (event) => {
        let {sku, productLinks} = event.detail;
        this.setState({
            editingProductLink: {
                sku,
                links: productLinks
            }
        });
    };
    renderAdditionalNavbarButtons = () => {
        return (
            <div className=" navbar-strip--push-up-fix ">
                <NavbarButton
                    buttonLabel={'Add'}
                    onClick={this.props.addNewProductButtonClick}
                    iconClass={'fa-plus navbar-strip__button__icon--center-add'}
                />
                <NavbarButton
                    buttonLabel={'Delete'}
                    onClick={this.props.actions.deleteProducts}
                    iconClass={'sprite-sprite sprite-cancel-22-black'}
                />
            </div>
        );
    };
    onProductLinksEditorClose = () => {
        this.setState({
            editingProductLink: {
                sku: "",
                links: []
            }
        });
    };
    getVisibleRows = () => {
        return this.props.products.visibleRows;
    };
    isTabSpecificColumn = (column) => {
        return !!column.tab;
    };
    isColumnSpecificToCurrentTab = (column) => {
        return column.tab === this.props.tabs.currentTab
    };
    renderColumns = () => {
        let columnSettings = this.props.columns.columnSettings;
        let distanceFromLeftSideOfTableToStartOfCell = 0;
        let createdColumns = columnSettings.map((column) => {
            column.distanceFromLeftSideOfTableToStartOfCell = distanceFromLeftSideOfTableToStartOfCell;
            if (this.isTabSpecificColumn(column) && !this.isColumnSpecificToCurrentTab(column)) {
                return;
            }
            let CreatedColumn = columnCreator(column, this.props);
            distanceFromLeftSideOfTableToStartOfCell += column.width;
            return CreatedColumn
        });
        return createdColumns;
    };
    isReadyToRenderTable = () => {
        return !!(this.state.productsListContainer && this.state.productsListContainer.height);
    };
    hasProducts = () => {
        return this.props.products.simpleAndParentProducts && this.getVisibleRows() && this.getVisibleRows().length
    };
    rowClassNameGetter = (rows, index) => {
        return constants.ROW_CLASS_PREFIX + '-' + index + ' ' + constants.ROW_CLASS_PREFIX + ' ' + this.getExtraRowClass(rows, index);
    };
    getExtraRowClass = (rows, index) => {
        if (rows.length === 0) {
            return '';
        }
        let currentProduct = rows[index];
        if (!currentProduct || !stateUtility.isVariation(currentProduct)) {
            return '';
        }
        return 'child-row';
    };
    onVerticalScroll = () => {
        let scrollTimeout;
        clearTimeout(this.scrollTimeout);
        this.scrollTimeout = setTimeout(() => {
            this.props.actions.updateRowsForPortals();
        }, 500);
        return true;
    };
    renderProducts = () => {
        let rows = this.getVisibleRows();
        if (!this.isReadyToRenderTable() && !this.hasProducts()) {
            return;
        }
        let height = this.state.productsListContainer.height;
        let width = this.state.productsListContainer.width;
        let rowCount = rows.length;
        if (!this.hasProducts() && this.props.products.haveFetched) {
            rowCount = 50;
        }
        return (
            <Table
                rowHeight={44}
                className={'c-products-data-table'}
                // add one extra row to provide room for the portalled elements in previous row
                rowsCount={rowCount + 1}
                width={width}
                height={height}
                headerHeight={36}
                data={rows}
                footerHeight={0}
                groupHeaderHeight={0}
                showScrollbarX={true}
                showScrollbarY={true}
                scrollToColumn={this.props.tabs.currentColumnScrollIndex}
                scrollToRow={this.props.list.currentRowScrollIndex}
                rowClassNameGetter={this.rowClassNameGetter.bind(this, rows)}
                onVerticalScroll={this.onVerticalScroll}
            >
                {this.renderColumns()}
            </Table>
        )
    };
    componentDidMount() {
        this.updateDimensions();
        window.addEventListener("resize", this.updateDimensions);
        document.addEventListener("fullscreenchange", this.updateDimensions);
        window.addEventListener('productLinkEditClicked', this.onEditProductLink, false);
        window.addEventListener('productLinkRefresh', this.onProductLinkRefresh, false);

        visibleRowService.updateProductList = () => {
            this.forceUpdate();
        };
    }
    componentWillUnmount() {
        window.removeEventListener("resize", this.updateDimensions);
        document.removeEventListener("fullscreenchange", this.updateDimensions);
        window.removeEventListener('productLinkEditClicked', this.onEditProductLink, false);
        window.removeEventListener('productLinkRefresh', this.onProductLinkRefresh, false);
    }
    componentDidUpdate() {
        let horizontalScrollbar = document.getElementsByClassName("ScrollbarLayout_face ScrollbarLayout_faceHorizontal public_Scrollbar_face")[0];
        if (horizontalScrollbar) {
            horizontalScrollbar.addEventListener('mousedown', this.updateHorizontalScrollIndex);
        }
    }
    shouldRenderModal() {
        return (
            this.isReadyToRenderTable() &&
            !this.props.products.visibleRows.length &&
            !this.props.search.productSearchActive &&
            this.props.products.completeInitialLoads.simpleAndParentProducts
        );
    }
    renderBlockerModal() {
        return (
            <BlockerModal
                contentJsx={
                    <div>
                        <div>You have no products... yet!</div>
                        <div><a href={'products/listing/import'} >Click here</a> to import your active listings </div>
                        <div>or <a href={'#'} onClick={this.props.addNewProductButtonClick} >here</a> to add a new product manually. </div>
                    </div>
                }
            />
        );
    }
    render() {
        return (
            <div id='products-app'>
                <div className="top-toolbar">
                    <SearchBox
                        initialSearchTerm={this.props.initialSearchTerm}
                        submitCallback={this.props.actions.searchProducts}
                    />
                    {this.renderAdditionalNavbarButtons()}
                </div>
                <Tabs/>
                {this.shouldRenderModal() ? this.renderBlockerModal() : ''}
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
                        changePage: this.props.actions.changePage,
                        changeLimit: this.props.actions.changeLimit
                    }}
                />
            </div>
        );
    }
}

export default ProductList;