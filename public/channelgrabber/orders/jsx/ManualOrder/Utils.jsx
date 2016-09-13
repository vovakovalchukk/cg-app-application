define([
], function(
) {
    var ManualOrderUtils = function(imageBasePath) {
        this.imageBasePath = imageBasePath;
    };

    ManualOrderUtils.prototype.getImageSource = function(product) {
        var noProductImage = this.imageBasePath+'/noproductsimage.png';
        return product.images.length > 0 ? product.images[0]['url'] : noProductImage;
    };

    ManualOrderUtils.prototype.getProductImage = function(product, sku) {
        var sku = sku || null;

        if (! product.variations || sku === null) {
            return this.getImageSource(product);
        }

        var variation = product.variations.find(function (variation) {
            if (variation.sku === sku && variation.images.length > 0) {
                return true;
            }
        });
        if (! variation) {
            return this.getImageSource(product);
        }
        return this.getImageSource(variation);
    };

    return ManualOrderUtils;
});