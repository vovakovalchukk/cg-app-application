import React from 'react';
import PropTypes from 'prop-types';
import RootComponent from 'PurchaseOrders/Components/Root';


class RootContainer extends React.Component {
    state = {
        filterStatus: 'All',
        sortAsc: true,
        purchaseOrders: [],
        isEditorEmpty: true
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
        window.triggerEvent('triggerPopup');
    };

    onCreateNewPurchaseOrder = () => {
        window.triggerEvent('createNewPurchaseOrder');
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

    render() {
        return (
            <RootComponent
                filterStatus={this.state.filterStatus}
                sortAsc={this.state.sortAsc}
                purchaseOrders={this.state.purchaseOrders}
                onFilterSelected={function(selection){this.setState({filterStatus: selection.value})}.bind(this)}
                onCreateNewPurchaseOrder={this.onCreateNewPurchaseOrder}
                onCreateNewPurchaseOrderButtonPressed={this.onCreateNewPurchaseOrderButtonPressed}
                onDateColumnClicked={this.onDateColumnClicked}
                newButtonDisabled={this.state.isEditorEmpty}
                setEditorEmptyFlag={this.setEditorEmptyFlag}
            />
        );
    }
}

RootContainer.childContextTypes = {
    imageUtils: PropTypes.object
};

export default RootContainer;
