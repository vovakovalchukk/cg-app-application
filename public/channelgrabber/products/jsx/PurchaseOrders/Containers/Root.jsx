import React from 'react';
import PropTypes from 'prop-types';
import RootComponent from 'PurchaseOrders/Components/Root';


class RootContainer extends React.Component {
    state = {
        filterStatus: 'All',
        sortAsc: true,
        purchaseOrders: [],
        isEditorEmpty: true,
        supplierOptions: [],
        onNewPurchaseOrderConfirm: () => {}
    };

    getChildContext() {
        return {
            imageUtils: this.props.utilities.image
        };
    }

    componentDidMount() {
        this.purchaseOrderRequest = this.doPurchaseOrderRequest();
        window.addEventListener('purchaseOrderListRefresh', this.doPurchaseOrderRequest);
    }

    componentWillUnmount() {
        this.purchaseOrderRequest.abort();
        window.removeEventListener('purchaseOrderListRefresh', this.doPurchaseOrderRequest);
    }

    doPurchaseOrderRequest = () => {
        $.ajax({
            method: 'POST',
            url: '/products/purchaseOrders/list',
            success: function (response) {
                if (response.list === undefined || response.list.length === 0) {
                    return;
                }
                this.setState({
                    purchaseOrders: response.list
                });
            }.bind(this)
        });
    };

    onCreateNewPurchaseOrderButtonPressed = () => {
        this.setState({
            onNewPurchaseOrderConfirm: () => {window.triggerEvent('createNewPurchaseOrder')}
        }, () => {
            window.triggerEvent('triggerPopup')
        });
    };

    createNewPurchaseOrderWithLowStockProducts = () => {
        this.setState({
            onNewPurchaseOrderConfirm: () => {window.triggerEvent('createNewPurchaseOrderForLowStockProducts')}
        }, () => {
            window.triggerEvent('triggerPopup')
        });
    };

    onDateColumnClicked = () => {
        this.setState({
            sortAsc: !this.state.sortAsc
        });
    };

    setEditorEmptyFlag = (isEditorEmpty) => {
        this.setState({
            isEditorEmpty: isEditorEmpty
        });
    };

    buildSuppliersOptions = () => {
        return Object.keys(this.props.supplierOptions).map((supplierId) => {
            return {
                name: this.props.supplierOptions[supplierId],
                value: supplierId
            }
        });
    };

    onSupplierChange = (option) => {
        this.setState({
            onNewPurchaseOrderConfirm: this.fetchProductsBySupplier.bind(this, option)
        }, () => {
            window.triggerEvent('triggerPopup');
        })
    };

    fetchProductsBySupplier = (option) => {
        const event = new CustomEvent('createNewPurchaseOrderForSupplier', {detail: {supplierId: option.value}});
        window.dispatchEvent(event);
    };

    render() {
        return (
            <RootComponent
                filterStatus={this.state.filterStatus}
                sortAsc={this.state.sortAsc}
                purchaseOrders={this.state.purchaseOrders}
                onFilterSelected={function(selection){this.setState({filterStatus: selection.value})}.bind(this)}
                onCreateNewPurchaseOrderButtonPressed={this.onCreateNewPurchaseOrderButtonPressed}
                onCreateNewPurchaseOrderWithLowStockProducts={this.createNewPurchaseOrderWithLowStockProducts}
                onDateColumnClicked={this.onDateColumnClicked}
                newButtonDisabled={this.state.isEditorEmpty}
                setEditorEmptyFlag={this.setEditorEmptyFlag}
                supplierOptions={this.buildSuppliersOptions()}
                onSupplierChange={this.onSupplierChange}
                onNewPurchaseOrderConfirm={this.state.onNewPurchaseOrderConfirm}
            />
        );
    }
}

RootContainer.childContextTypes = {
    imageUtils: PropTypes.object
};

export default RootContainer;
