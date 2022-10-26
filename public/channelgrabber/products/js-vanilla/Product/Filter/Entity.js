define([
], function () {
    var Entity = function (searchTerm, parentProductId, id, sku, skuThatProductsCantLinkFrom, limit, replaceVariationWithParent, embedVariationsAsLinks, embeddedDataToReturn, returnOnlyFirstImage, order)
    {
        this.page = 1;
        this.searchTerm = searchTerm;
        this.parentProductId = parentProductId;
        this.id = id;
        this.sku = sku;
        this.skuThatProductsCantLinkFrom = skuThatProductsCantLinkFrom;
        this.limit = limit;
        this.replaceVariationWithParent = replaceVariationWithParent;
        this.embedVariationsAsLinks = embedVariationsAsLinks;
        this.embeddedDataToReturn = embeddedDataToReturn;
        this.returnOnlyFirstImage = returnOnlyFirstImage;
        this.order = order

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

        this.getOrder = function() {
            return this.order;
        }

        this.setOrder = function(order) {
            this.order = order;
            return this;
        }

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

        this.setEmbedVariationsAsLinks = function(newValue)
        {
            this.embedVariationsAsLinks = newValue;
        }

        this.getEmbedVariationsAsLinks = function()
        {
            return this.embedVariationsAsLinks;
        }

        this.getEmbeddedDataToReturn = function()
        {
            return this.embeddedDataToReturn;
        }

        this.setEmbeddedDataToReturn = function(embeddedDataToReturn)
        {
            this.embeddedDataToReturn = embeddedDataToReturn;
            return this;
        }

        this.getReturnOnlyFirstImage = function()
        {
            return this.returnOnlyFirstImage;
        }

        this.setReturnOnlyFirstImage = function(returnOnlyFirstImage)
        {
            this.returnOnlyFirstImage = returnOnlyFirstImage;
            return this;
        }
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
            object['parentProductId'] = formatParentProductId(parentProductId);
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

        if (typeof this.getEmbedVariationsAsLinks() === 'boolean') {
            object['embedVariationsAsLinks'] = this.getEmbedVariationsAsLinks();
        }

        let embeddedDataToReturn = this.getEmbeddedDataToReturn();
        if (Array.isArray(embeddedDataToReturn) && embeddedDataToReturn !== []) {
            object['embeddedDataToReturn'] = embeddedDataToReturn;
        }

        if (typeof this.getReturnOnlyFirstImage() === 'boolean') {
            object['returnOnlyFirstImage'] = this.getReturnOnlyFirstImage();
        }

        if (this.getOrder()) {
            object['order'] = this.getOrder();
        }

        return object;
    };

    return Entity;
});

function formatParentProductId(parentProductId) {
    if (parentProductId.constructor === Array) {
        return parentProductId;
    }
    return[parentProductId];
}