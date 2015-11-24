function orderCountsByAjax(organisationUnitId) {

    var d = new Date();
    var n = d.getTime();
    var dataString = +'&time=' + n;
    $.ajax
            ({
                type: 'POST',
                url: 'https://cg_app.orderhub.io.local/orderCounts/' + organisationUnitId,
                data: dataString,
                cache: false,
                success: function (json)
                {

                    displayCounts(json);



                }
            });
}

function displayCounts() {


    ///this will eventually be the real json response
    var json = ' { '
            + '"status": {'
            + '  "organisationUnitID": 1,'
            + ' "allOrders": 26,'
            + '  "awaitingPayment": 2,'
            + '  "newOrders": 2,'
            + '  "processing": 6,'
            + ' "dispatched": 2,'
            + '  "cancelledAndRefunded": 4'
            + '},'
            + '"batches":{"2-1":11,"2-2":21,"2-3":41,"2-4":51,"2-5":61}'
            + '}';


    var jsonArray = JSON.parse(json);

    //status'
    var status = jsonArray.status;
    $('#allOrdersCount').html(status.allOrders);
    $('#awaitingPaymentCount').html(status.awaitingPayment);
    $('#newOrdersCount').html(status.newOrders);
    $('#processingCount').html(status.processing);
    $('#dispatchedCount').html(status.dispatched);
    $('#cancelledAndRefundedCount').html(status.cancelledAndRefunded);

    //batches
    var batches = jsonArray.batches;
    var batchesKeys = Object.keys(batches);
    for (var b = 0; b < batchesKeys.length; b++) {
        var batchId = batchesKeys[b];
        var batchesSpanId = "batchCount-" + batchId;
        var count = batches[batchId];
        $('#' + batchesSpanId).html(count);
    }
}