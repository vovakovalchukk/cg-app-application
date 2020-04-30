import React from 'react';
import ReactDOM from 'react-dom';
import RootComponent from 'ManualOrder/Components/Root';
import NoteComponent from 'Common/Components/Notes/Root';
import Select from 'Common/Components/Select';
import PopupComponent from 'Common/Components/Popup';

const ManualOrder = function(mountingNodes, utilities, currentUser)
{
    document.body.addEventListener('keydown', function (event) {
        //  Prevent Enter key from submitting the form
        if(event.keyCode == 13) {
            event.preventDefault();
            return false;
        }
    });
    var self = this;
    this.popupContent = <p className="center-align">Create the order?</p>;
    this.orderSubmitEvent = new CustomEvent('orderSubmit');
    this.manualOrder = ReactDOM.render(
        <RootComponent utilities={utilities} onCreateOrder={this.collectFormData.bind(this)}/>, mountingNodes.productInfo);
    this.note = ReactDOM.render(<NoteComponent author={currentUser}/>, mountingNodes.orderNotes);
    this.popup = ReactDOM.render(<PopupComponent onNoButtonPressed={onPopupClickNo} onYesButtonPressed={onPopupClickYes}>{this.popupContent}</PopupComponent>, mountingNodes.popup);

    var tradingCompanies = utilities.ou.getTradingCompanies();
    var selectedCompany = null;
    tradingCompanies.forEach(function(company) {
        if (company.selected) {
            selectedCompany = company;
        }
    });
    this.companySelect = ReactDOM.render(<Select options={utilities.ou.getTradingCompanies()} selectedOption={selectedCompany}/>, mountingNodes.companySelect);

    function onPopupClickNo() {
        self.popup.setState({
            active: false
        });
    }
    function onPopupClickYes() {
        window.dispatchEvent(self.orderSubmitEvent);
        self.popup.setState({
            active: false
        });
        n.notice("Creating order...");
    }

    this.getOrderData = function () {
        return this.manualOrder.state.order;
    };

    this.getCurrencyData = function () {
        return this.manualOrder.state.selectedCurrency
    };

    this.getNoteData = function () {
        return this.note.state;
    };

    this.getCompanySelectData = function () {
        return this.companySelect ? this.companySelect.state: null
    };

    this.listenForCreateOrderAction = function()
    {
        var self = this;
        $('#create-order-button').click(function(e) {
            self.popup.setState({
                active: true
            });
        });
    };

    var init = function() {
        this.listenForCreateOrderAction();
    };
    init.call(this);
};

ManualOrder.prototype.submitFormData = function (formData) {
    $.ajax({
        url: '/orders/new/create',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function (data) {
            if (data.success) {
                n.success('Order Created');
                window.location = data.url;
                return;
            }
            n.error(data.message);
        }.bind(this),
        error: function (error, textStatus, errorThrown) {
            n.ajaxError(error, textStatus, errorThrown);
        }
    });
};

ManualOrder.prototype.collectFormData = function() {
    var rawOrderData = this.getOrderData();
    var rawCurrencyData = this.getCurrencyData();
    var rawNoteData = this.getNoteData();
    var rawCompanySelectData = this.getCompanySelectData();

    if (rawOrderData.orderRows === undefined || rawOrderData.orderRows.length === 0) {
        n.error("Please add at least one product to the order.");
        return;
    }

    this.submitFormData({
        "organisationUnitId": rawCompanySelectData ? rawCompanySelectData.selectedOption.value : '',
        "currencyCode": rawCurrencyData.name,
        "shippingPrice": rawOrderData.shippingMethod.cost,
        "shippingMethod": rawOrderData.shippingMethod.name ? rawOrderData.shippingMethod.name : 'N/A',
        "totalDiscount": (rawOrderData.discount.active ? rawOrderData.discount.value : 0),
        "item": this.mapOrderItems(rawOrderData.orderRows),
        "note": this.mapNotes(rawNoteData.notes),
        "billingAddressCompanyName": $("input[name='billingAddressCompanyName']").val(),
        "billingAddressFullName": $("input[name='billingAddressFullName']").val(),
        "billingAddress1": $("input[name='billingAddress1']").val(),
        "billingAddress2": $("input[name='billingAddress2']").val(),
        "billingAddress3": $("input[name='billingAddress3']").val(),
        "billingAddressCity": $("input[name='billingAddressCity']").val(),
        "billingAddressCounty": $("input[name='billingAddressCounty']").val(),
        "billingAddressCountry": $("input[name='billingAddressCountry']").val(),
        "billingAddressPostcode": $("input[name='billingAddressPostcode']").val(),
        "billingEmailAddress": $("input[name='billingEmailAddress']").val(),
        "billingPhoneNumber": $("input[name='billingPhoneNumber']").val(),
        "billingAddressCountryCode": "",
        "shippingAddressSameAsBilling": $("input[name='shippingAddressUseBilling']").is(":checked"),
        "shippingAddressCompanyName": $("input[name='shippingAddressCompanyName']").val(),
        "shippingAddressFullName": $("input[name='shippingAddressFullName']").val(),
        "shippingAddress1": $("input[name='shippingAddress1']").val(),
        "shippingAddress2": $("input[name='shippingAddress2']").val(),
        "shippingAddress3": $("input[name='shippingAddress3']").val(),
        "shippingAddressCity": $("input[name='shippingAddressCity']").val(),
        "shippingAddressCounty": $("input[name='shippingAddressCounty']").val(),
        "shippingAddressCountry": $("input[name='shippingAddressCountry']").val(),
        "shippingAddressPostcode": $("input[name='shippingAddressPostcode']").val(),
        "shippingEmailAddress": $("input[name='shippingEmailAddress']").val(),
        "shippingPhoneNumber": $("input[name='shippingPhoneNumber']").val(),
        "shippingAddressCountryCode": "",
        "buyerMessage":$('#buyer-message').val(),
        "alert": $("input[name='orderAlertText']").val()
    });
};

ManualOrder.prototype.mapNotes = function (notes) {
    return notes.map(function (note) {
        return note.note;
    });
};

ManualOrder.prototype.mapOrderItems = function (orderRows) {
    return orderRows.map(function (row) {
        return {
            "itemName": row.product.name,
            "itemSku": row.sku,
            "individualItemPrice": row.price,
            "itemQuantity": row.quantity,
            "productId": row.product.id
        }
    });
};

export default ManualOrder;
