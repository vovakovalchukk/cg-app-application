define([
    'react',
    'react-dom',
    'ManualOrder/Components/Root',
    'Common/Components/Notes/Root',
    'Common/Components/Select',
    'Common/Components/Popup'
], function(
    React,
    ReactDOM,
    RootComponent,
    NoteComponent,
    Select,
    PopupComponent
) {
    var ManualOrder = function(mountingNodes, utilities, currentUser)
    {
        var self = this;
        this.popupContent = <p className="center-align">Create the order?</p>;
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

        this.collectFormData = function() {
            var rawOrderData = self.manualOrder.state.order;
            var rawCurrencyData = self.manualOrder.state.selectedCurrency;
            var rawNoteData = self.note.state;
            var rawCompanySelectData = self.companySelect ? self.companySelect.state: null;

            self.submitFormData({
                "organisationUnitId": self.companySelect ? rawCompanySelectData.selectedOption.value : '',
                "currencyCode": rawCurrencyData.name,
                "shippingPrice": rawOrderData.shippingMethod.cost,
                "shippingMethod": rawOrderData.shippingMethod.name,
                "totalDiscount": (rawOrderData.discount.active ? rawOrderData.discount.value : 0),
                "item": self.mapOrderItems(rawOrderData.orderRows),
                "note": self.mapNotes(rawNoteData.notes),
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
                "shippingAddressSameAsBilling": $("input[name='shippingAddressUseBilling']").val(),
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

        this.submitFormData = function (formData) {
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

        this.orderSubmitEvent = new CustomEvent('orderSubmit');
        this.manualOrder = ReactDOM.render(<RootComponent utilities={utilities} onCreateOrder={this.collectFormData}/>, mountingNodes.productInfo);
        this.note = ReactDOM.render(<NoteComponent author={currentUser}/>, mountingNodes.orderNotes);
        this.popup = ReactDOM.render(<PopupComponent onNoButtonPressed={onPopupClickNo} onYesButtonPressed={onPopupClickYes}>{this.popupContent}</PopupComponent>, mountingNodes.popup);

        var tradingCompanies = utilities.ou.getTradingCompanies();
        if (tradingCompanies.length > 1) {
            var selectedCompany = null;
            tradingCompanies.forEach(function(company) {
                if (company.selected) {
                    selectedCompany = company;
                }
            });
            this.companySelect = ReactDOM.render(<Select options={utilities.ou.getTradingCompanies()} selectedOption={selectedCompany}/>, mountingNodes.companySelect);
        }
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

    return ManualOrder;
});