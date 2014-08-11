define(function() {
    var Product = function()
    {
        this.successMessage = 'Settings Saved';
        this.errorMessage = 'Error: Settings could not be saved';

        var container = '.products-list';
        var selector = container + ' .submitable-input-save-button';
        
        var init = function() {
            var self = this;
            $(document).on('change', selector, function () {
                self.save();
            });
        };

        this.getLocationId()
        {

        }

        this.getStockId()
        {

        }

        this.getNewTotal()
        {

        }

        this.getAllocated()
        {
            
        }

        this.getStockLocationEntity = function()
        {
            return {
                'default': getDefault(),
                'locationId': getLocationId(),
                'stockId' : getStockId(),
                'onHand' : getNewTotal(),
                'allocated' : getAllocated(),
                'eTag': "36dcb9e4e366b89d4982131e5f4542b7b24e4040" //$('#setting-etag').val()
            };
        };

        init.call(this);
    };

    Product.prototype.save = function()
    {
        var self = this;
        $.ajax({
            url: "stockLocation/" + self.getStockId() + "-" + self.getLocationId(),
            type: "POST",
            dataType : 'json',
            data: self.getStockLocationEntity()
        }).success(function(data) {
            $('#setting-etag').val(data.eTag);
            if (n) {
                n.success(self.successMessage);
            }
        }).error(function(error, textStatus, errorThrown) {
            if (n) {
                n.ajaxError(error, textStatus, errorThrown);
            }
        });
    };

    return Product;
});