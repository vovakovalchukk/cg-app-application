define([], function() {
    var OrderCounts = function()
    {
        var init = function() {
            var self = this;
            self.ajax();
            $('#datatable').on('fnDrawCallback', function() {
                self.ajax();
            });
        };

        init.call(this);
    };

    OrderCounts.prototype.ajax = function ()
    {
        var self = this;

        $.ajax  ({
            type: 'GET',
            url: '/orders/orderCounts',
            cache: false,
            success: function (json)
            {
                self.displayCounts(json);
            },
            error: function()
            {
                alert('Order count request failed');
            }
        });
    };

    OrderCounts.prototype.displayCounts = function (json)
    {
        var self = this;
        var status = json.status;
        $('#allOrdersCount').html(status.allOrders);
        $('#awaitingPaymentCount').html(status.awaitingPayment);
        $('#newOrdersCount').html(status.newOrders);
        $('#processingCount').html(status.processing);
        $('#dispatchedCount').html(status.dispatched);
        $('#cancelledAndRefundedCount').html(status.cancelledAndRefunded);
        var maxCount = 0;
        var batches = json.batches;
        var batchesKeys = Object.keys(batches);
        for (var b = 0; b < batchesKeys.length; b++) {
            var batchId = batchesKeys[b];
            var batchesSpanId = "batchCount-" + batchId;
            var count = batches[batchId];
            if(count > maxCount){maxCount = count;}
            $('#' + batchesSpanId).html(count);
        };
        self.changeMarginOfDeleteCrossBasedOnBatchCountStringLength(maxCount);
    };

    OrderCounts.prototype.changeMarginOfDeleteCrossBasedOnBatchCountStringLength = function(maxCount)
    {
        var maxBatchCountCharacterLengthAllowed = 4;
        var pixelsPerCharacter = 5;
        var maxCountLength = maxCount.toString().length;
        if(maxCountLength > maxBatchCountCharacterLengthAllowed){
            var difference = maxCountLength - maxBatchCountCharacterLengthAllowed;
            var pixelsToMoveCross = difference * pixelsPerCharacter;
            var currentRight = $('.deletebatch').css("right").slice(0,-2);
            var newRight = parseInt(currentRight) + parseInt(pixelsToMoveCross);
            var cssPixels = newRight + "px";
            $('.deletebatch').css("right",cssPixels);
        }
    };
    
    return OrderCounts;
});