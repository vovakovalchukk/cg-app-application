define(function()
{
    function InputData()
    {
    }

    InputData.SELECTOR_ORDER_ID_INPUT = '#datatable input[name="order[]"]';
    InputData.SELECTOR_ORDER_LABEL_STATUS_TPL = '#datatable input[name="orderInfo[_orderId_][labelStatus]"]';
    InputData.SELECTOR_ORDER_CANCELLABLE_TPL = '#datatable input[name="orderInfo[_orderId_][cancellable]"]';

    InputData.prototype.getInputDataForOrdersOfLabelStatuses = function(labelStatuses, idsOnly, cancellableOnly)
    {
        var self = this;
        var data = {"order": []};
        $(InputData.SELECTOR_ORDER_ID_INPUT).each(function()
        {
            var element = this;
            var orderId = $(element).val();
            var labelStatusSelector = InputData.SELECTOR_ORDER_LABEL_STATUS_TPL.replace('_orderId_', orderId);
            var labelStatus = $(labelStatusSelector).val();
            if (!labelStatuses[labelStatus] && labelStatuses.indexOf(labelStatus) == -1) {
                return true; // continue
            }
            if (cancellableOnly) {
                var cancellableSelector = InputData.SELECTOR_ORDER_CANCELLABLE_TPL.replace('_orderId_', orderId);
                if (!$(cancellableSelector).val()) {
                    return true; // continue
                }
            }
            data.order.push(orderId);
            if (idsOnly) {
                return true; // continue
            }
            var orderInputData = self.getInputDataForOrder(orderId);
            if (!orderInputData) {
                data = false;
                return false; // break
            }
            var orderData = self.convertInputDataToAjaxData(orderInputData);
            for (var key in orderData) {
                data[key] = orderData[key];
            }
        });
        return data;
    };

    InputData.prototype.getInputDataForOrder = function(orderId)
    {
        var inputDataSelector = '#datatable td input[name^="orderData['+orderId+']"], ';
        inputDataSelector +=    '#datatable td input[name^="parcelData['+orderId+']"], ';
        inputDataSelector +=    '#datatable td input[name^="itemData['+orderId+']"]';
        var inputData = this.getInputData(inputDataSelector);
        // Add the service name as well as its code to save us looking it up again later
        var serviceName = $('#courier-service-options-select-' + orderId + ' li.active').text();
        if (serviceName) {
            inputData.push({
                name: 'orderData[' + orderId + '][serviceName]',
                value: serviceName.trim()
            });
        }
        return inputData;
    };

    InputData.prototype.convertInputDataToAjaxData = function(inputData)
    {
        var ajaxData = {};
        for (var count in inputData) {
            var name = inputData[count].name;
            var value = inputData[count].value;
            if (name.match(/\[\]$/)) {
                name = name.replace(/\[\]$/, '');
                if (!ajaxData.hasOwnProperty(name)) {
                    ajaxData[name] = [];
                }
                ajaxData[name].push(value);
            } else {
                ajaxData[name] = value;
            }
        }
        return ajaxData;
    };

    InputData.prototype.getInputData = function(selector, validate)
    {
        if (validate === undefined) {
            validate = true;
        }
        var self = this;
        var inputData = [];
        var valid = true;
        var invalidInput = null;
        $(selector).each(function()
        {
            var input = this;
            var name = $(input).attr('name');
            if (!name || (!name.match(/^orderData/) && !name.match(/^parcelData/) && !name.match(/^itemData/))) {
                return true; // continue
            }
            var value = $(input).val();
            if ($(input).attr('type') == 'checkbox') {
                value = ($(input).is(':checked') ? 1 : 0);
            }
            if (validate && !self.isInputValid(input)) {
                valid = false;
                invalidInput = input;
                return false; // break
            }
            inputData.push({
                name: name,
                value: value
            });
        });
        if (!valid) {
            n.error('Please complete all required fields in the correct format', true);
            this.highlightInvalidInput(invalidInput);
            return false;
        }
        return inputData;
    };

    InputData.prototype.isInputValid = function(input)
    {
        var value = $(input).val();
        if ($(input).hasClass('required') && !value) {
            return false;
        }
        if (($(input).hasClass('number') || $(input).attr('type') == 'number') && value && parseFloat(value) === NaN) {
            return false;
        }
        if ($(input).hasClass('datepicker') && value && !value.match(/\d{2}\/\d{2}\/\d{4}/)) {
            return false;
        }
        return true;
    };

    InputData.prototype.highlightInvalidInput = function(input)
    {
        var offsetTop = $(input).closest('td').get(0).offsetTop;
        document.querySelector('.dataTables_scrollBody').scrollTop = offsetTop;
        input.focus();
        return this;
    };

    return new InputData();
});