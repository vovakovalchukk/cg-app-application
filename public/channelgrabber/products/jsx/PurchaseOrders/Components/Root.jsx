import React from 'react';
import Select from 'Common/Components/Select';
import PurchaseOrdersList from 'PurchaseOrders/Components/List';
import PurchaseOrdersEditor from 'PurchaseOrders/Containers/Editor';
import Button from 'Common/Components/Button';
import PopupComponent from 'Common/Components/Popup';


const FILTER_OPTIONS = [
    {
        name: 'All',
        value: 'All'
    }, {
        name: 'Complete',
        value: 'Complete',
    }, {
        name: 'In Progress',
        value: 'In Progress'
    }
];

class RootComponent extends React.Component {
    render() {
        return (
        <div className="purchase-orders-root">
            <PopupComponent onYesButtonPressed={this.props.onCreateNewPurchaseOrder}>
                <p>Do you want to create a new Purchase Order?</p>
                <p>Any unsaved changes to the current Purchase Order will be lost.</p>
            </PopupComponent>
            <div className="purchase-orders-actions">
                <div className="purchase-orders-action">
                    <Select prefix="Show" options={FILTER_OPTIONS} onOptionChange={this.props.onFilterSelected}/>
                </div>
                <div className="purchase-orders-action">
                    <Button
                        onClick={this.props.onCreateNewPurchaseOrderButtonPressed}
                        sprite="sprite-plus-18-black"
                        text="New"
                        disabled={this.props.newButtonDisabled}
                    />
                </div>
            </div>
            <div className="purchase-orders-container">
                <PurchaseOrdersList
                    filterStatus={this.props.filterStatus}
                    sortAsc={this.props.sortAsc}
                    purchaseOrders={this.props.purchaseOrders}
                    onPurchaseOrderSelected={this.props.onPurchaseOrderSelected}
                    onDateColumnClicked={this.props.onDateColumnClicked}
                />
                <PurchaseOrdersEditor
                    setEditorEmptyFlag={this.props.setEditorEmptyFlag}
                />
            </div>
        </div>
        );
    }
}

export default RootComponent;
