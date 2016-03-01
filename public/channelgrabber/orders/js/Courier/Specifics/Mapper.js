define([], function()
{
    function Mapper()
    {
    }

    Mapper.prototype.dataTableRecordsToOrderData = function(records)
    {
        var orderData = {};
        for (var index in records) {
            var record = records[index];
            var orderId = record.orderId;
            if (typeof orderData[orderId] == 'undefined') {
                orderData[orderId] = {
                    "items": [],
                    "parcels": []
                };
            }
            if (record.itemRow) {
                orderData[orderId].items.push({
                    "id": record.itemId,
                    "quantity": record.quantity,
                    "name": record.itemName
                });
            }
            if (record.parcelRow) {
                orderData[orderId].parcels.push(record.parcelNumber);
            }
        }
        return orderData;
    };

    return new Mapper();
});