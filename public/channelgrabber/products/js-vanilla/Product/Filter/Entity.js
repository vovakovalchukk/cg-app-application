define([
], function () {
    var Entity = function (searchTerm, parentProductId, id, sku, skuThatProductsCantLinkFrom, limit, replaceVariationWithParent)
    {
        this.page = 1;
        this.searchTerm = searchTerm;
        this.parentProductId = parentProductId;
        this.id = id;
        this.sku = sku;
        this.skuThatProductsCantLinkFrom = skuThatProductsCantLinkFrom;
        this.limit = limit;
        this.replaceVariationWithParent = replaceVariationWithParent;

        this.getSkuThatProductsCantLinkFrom = function() {
            return this.skuThatProductsCantLinkFrom;
        };

        this.getSku = function () {
            return this.sku;
        };

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
        
        this.getLimit = function()
        {
            return this.limit;
        };
        
        this.setLimit = function(newLimit)
        {
            this.limit = newLimit;
            return this;
        };

        this.getReplaceVariationWithParent = function()
        {
            return this.replaceVariationWithParent;
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
        
        let limit = this.getLimit();
        if(limit){
            object["limit"] = limit;
        }

        if (this.getSkuThatProductsCantLinkFrom()) {
            object['skuThatProductsCantLinkFrom'] = this.getSkuThatProductsCantLinkFrom();
        }

        if (typeof this.getReplaceVariationWithParent() === 'boolean') {
            object['replaceVariationWithParent'] = this.getReplaceVariationWithParent();
        }

        return object;
    };

    return Entity;
});