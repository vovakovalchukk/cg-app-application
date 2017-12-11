define([
], function () {
    var Entity = function (searchTerm, parentProductId, id, sku, notIfCantLinkToSku)
    {
        this.page = 1;
        this.searchTerm = searchTerm;
        this.parentProductId = parentProductId;
        this.id = id;
        this.sku = sku;
        this.notIfCantLinkToSku = notIfCantLinkToSku;

        this.getNotIfCantLinkToSku = function() {
            return this.notIfCantLinkToSku;
        }

        this.getSku = function () {
            return this.sku;
        }

        this.getSearchTerm = function() {
            return this.searchTerm;
        };

        this.getParentProductId = function() {
            return this.parentProductId;
        };

        this.getId = function() {
            return this.id;
        };

        this.setId = function(newId) {
            this.id = (newId instanceof Array ? newId : [newId]);
            return this;
        };

        this.getPage = function() {
            return this.page;
        };

        this.setPage = function(newPage)
        {
            this.page = newPage;
            return this;
        };
    };

    Entity.prototype.toObject = function()
    {
        var object = {
            "page": this.getPage()
        };

        var searchTerm = this.getSearchTerm();
        if (searchTerm) {
            object['searchTerm'] = searchTerm;
        }

        var parentProductId = this.getParentProductId();
        if (parentProductId) {
            object['parentProductId'] = [parentProductId];
        }

        var id = this.getId();
        if (id) {
            object['id'] = id;
        }

        var sku = this.getSku();
        if (sku) {
            object['sku'] = sku;
        }

        if (this.getNotIfCantLinkToSku()) {
            object['notIfCantLinkToSku'] = this.getNotIfCantLinkToSku();
        }

        return object;
    };

    return Entity;
});