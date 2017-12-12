define([], function() {
    const Image = function(options) {
        this.imageBasePath = options.imageBasePath;
    };
    Image.prototype.getImageSource = function(product) {
        if (product && product.images && product.images.length > 0 && product.images[0].url) {
            return product.images[0].url;
        }
        return this.imageBasePath+'/noproductsimage.png';
    };

    Image.prototype.getProductImage = function(product, sku) {
        sku = sku || null;

        if (! product || ! product.variations || sku === null) {
            return this.getImageSource(product);
        }

        const variation = product.variations.find(function (variation) {
            if (variation.sku === sku && variation.images && variation.images.length > 0) {
                return true;
            }
        });
        if (! variation) {
            return this.getImageSource(product);
        }
        return this.getImageSource(variation);
    };

    return Image;
});
