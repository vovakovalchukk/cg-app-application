import React from 'react';
import FixedDataTable, {Table} from 'fixed-data-table-2';
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
import styleVars from 'Product/Components/ProductList/styleVars';
import utility from 'Product/Components/ProductList/utility';
import PopupComponent from "Common/Components/Popup";
import ProductExpandHeader from "./Cell/Header/ProductExpand";
"use strict";

class ProductList extends React.Component {
    static defaultProps = {
        products: [],
        features: {},
        accounts: {},
        actions: {},
        tabs: {},
        incPOStockInAvailableOptions: {},
        initialSearchTerm: ''
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

    componentDidUpdate = function(prevProps) {
        if (prevProps.products.visibleRows.length !== this.props.products.visibleRows.length) {
            this.props.actions.updateRowsForPortals();
        }
        this.focusInputIfApplicable();
    };
    focusInputIfApplicable = () => {
        if (!this.props.focus.focusedInputInfo.columnKey) {
            return
        }
        var inputs = document.querySelectorAll('[data-inputinfo]');
        for (let input of inputs) {
            let parsedInfo = JSON.parse(input.dataset.inputinfo);
            if (!utility.areObjectsShallowPropsEqual(parsedInfo, this.props.focus.focusedInputInfo)) {
                continue;
            }
            input.focus();
            break;
        }
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
        let sku = event.detail;
        this.props.actions.getLinkedProducts([sku]);
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
    addProductClick = () => {
        this.props.actions.changeView();
        this.props.addNewProductButtonClick();
    };
    renderAdditionalNavbarButtons = () => {
        return (
            <div className=" navbar-strip--push-up-fix ">
                <NavbarButton
                    buttonLabel={'Add'}
                    onClick={this.addProductClick}
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
        const {columnSettings} = this.props.columns;
        let horizontalDistanceOfColumn = 0;
        const createdColumns = columnSettings.map((column) => {
            column.distanceFromLeftSideOfTableToStartOfCell = horizontalDistanceOfColumn - this.props.scroll.currentColumnScrollIndex;
            if (this.isTabSpecificColumn(column) && !this.isColumnSpecificToCurrentTab(column)) {
                return;
            }
            let CreatedColumn = columnCreator(column, this.props);
            horizontalDistanceOfColumn += column.width;
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
        if (!this.props.scroll.userScrolling) {
            this.props.actions.setUserScrolling();
        }
        clearTimeout(this.scrollTimeout);
        this.scrollTimeout = setTimeout(() => {
            this.props.actions.unsetUserScrolling();
            this.props.actions.updateRowsForPortals();
        }, 120);
        return true;
    };
    onHorizontalScroll = (index) => {
        if (!this.props.scroll.userScrolling) {
            this.props.actions.setUserScrolling();
        }
        clearTimeout(this.scrollTimeout);
        this.scrollTimeout = setTimeout(() => {
            this.props.actions.unsetUserScrolling();
            this.props.actions.updateHorizontalScrollIndex(index)
        }, 120);
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
                rowHeight={styleVars.heights.rowHeight}
                className={'c-products-data-table'}
                // add one extra row to provide room for the portalled elements in previous row
                rowsCount={rowCount + 1}
                width={width}
                height={height}
                headerHeight={styleVars.heights.headerHeight}
                data={rows}
                footerHeight={0}
                groupHeaderHeight={0}
                showScrollbarX={true}
                showScrollbarY={true}
                scrollToColumn={this.props.scroll.currentColumnScrollIndex}
                scrollToRow={this.props.scroll.currentRowScrollIndex}
                rowClassNameGetter={this.rowClassNameGetter.bind(this, rows)}
                onVerticalScroll={this.onVerticalScroll}
                onHorizontalScroll={this.onHorizontalScroll}
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
            !this.props.products.fetching &&
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
                        <div><a href={'products/listing/import'}>Click here</a> to import your active listings</div>
                        <div>or <a href={'#'} onClick={this.props.addNewProductButtonClick}>here</a> to add a new
                            product manually.
                        </div>
                    </div>
                }
            />
        );
    }
    renderExpandVariationsConfirmation() {
        if (!this.isReadyToRenderTable() && !this.hasProducts()) {
            return;
        }

        const totalVariationsCount = stateUtility.getAllVariationsCount(this.props.products.visibleRows);
        return <div>
            <PopupComponent
                initiallyActive={false}
                onYesButtonPressed={this.props.actions.toggleExpandAll.bind(this, [true])}
                headerText={"Confirm"}
                name={ProductExpandHeader.CONFIRMATION_POPUP_NAME}
            >
                <p>Do you want to expand all the variation for the products on this page?</p>
                <p>There are {totalVariationsCount} variations to be loaded, this may take a while.</p>
            </PopupComponent>
        </div>
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
                {this.renderExpandVariationsConfirmation()}
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