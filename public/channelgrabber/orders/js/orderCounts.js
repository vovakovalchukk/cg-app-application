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
            }
        });
    };

    OrderCounts.prototype.displayCounts = function (json)
    {
        for (var status in json.status) {
            if (status == 'organisationUnitID') {
                continue;
            }
            $('#' + status + 'Count').html(json.status[status]);
            $('#' + status + 'CountSub').html(json.status[status]);
        }
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
        this.setCountWidths(json.status)
            .changeMarginOfDeleteCrossBasedOnBatchCountStringLength(maxCount);
    };

    OrderCounts.prototype.setCountWidths = function(statusCounts)
    {
        var maxLength = 0;
        var maxLengthStatus;
        for (var status in statusCounts) {
            if (String(statusCounts[status]).length <= maxLength) {
                continue;
            }
            maxLength = String(statusCounts[status]).length;
            maxLengthStatus = status;
        }
        var maxWidth = $('#' + maxLengthStatus + 'Count').width();
        var maxWidthSub = $('#' + maxLengthStatus + 'CountSub').width();
        if (maxWidthSub > maxWidth) {
            maxWidth = maxWidthSub;
        }
        $('.statusCountPillBox, .statusCountOnlyPillBox, .batchCountPillBox').width(maxWidth);
        return this;
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
        return this;
    };
    
    return OrderCounts;
});