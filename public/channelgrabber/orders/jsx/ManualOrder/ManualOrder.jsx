define([
    'react',
    'react-dom',
    'ManualOrder/Components/Root',
    'Common/Components/Notes/Root',
    'Common/Components/Popup'
], function(
    React,
    ReactDOM,
    RootComponent,
    NoteComponent,
    PopupComponent
) {
    var ManualOrder = function(mountingNodes, utilities, currentUser)
    {
        var self = this;
        this.popupContent = <p className="center-align">Create the order?</p>;
        function onPopupClickNo() {
            self.popupRef.setState({
                active: false
            });
        }
        function onPopupClickYes() {
            window.dispatchEvent(self.orderSubmitEvent);
            self.popupRef.setState({
                active: false
            });
            n.notice("Creating order...");
        }

        this.collectFormData = function() {
            var rawOrderData = self.manualOrderRef.state.order;
            var rawCurrencyData = self.manualOrderRef.state.selectedCurrency;
            var rawNoteData = self.noteRef.state;

            self.submitFormData({
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

        this.mapNotes = function (notes) {
            return notes.map(function (note) {
                return note.note;
            });
        };

        this.mapOrderItems = function (orderRows) {
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

        this.submitFormData = function (formData) {
            $.ajax({
                url: '/orders/new/create',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function (data) {
                    if (data.success) {
                        n.success('Order Created');
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
                self.popupRef.setState({
                    active: true
                });
            });
        };

        var init = function() {
            this.listenForCreateOrderAction();
        };
        init.call(this);

        this.orderSubmitEvent = new CustomEvent('orderSubmit');
        this.manualOrderRef = ReactDOM.render(<RootComponent utilities={utilities} onCreateOrder={this.collectFormData}/>, mountingNodes.productInfo);
        this.noteRef = ReactDOM.render(<NoteComponent author={currentUser}/>, mountingNodes.orderNotes);
        this.popupRef = ReactDOM.render(<PopupComponent onNoButtonPressed={onPopupClickNo} onYesButtonPressed={onPopupClickYes}>{this.popupContent}</PopupComponent>, mountingNodes.popup);
    };

    return ManualOrder;
});