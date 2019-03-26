define([
    'AjaxRequester',
    'cg-mustache',
    'element/loadingIndicator'
],
function(
    ajaxRequester,
    CGMustache,
    loadingIndicator
) {

    function ProductImage(tableElement, templatePath) {
        this.getTableElement = function()
        {
            return tableElement;
        };

        this.getTemplatePath = function()
        {
            return templatePath;
        };

        this.getAjaxRequester = function()
        {
            return ajaxRequester;
        };

        var init = function()
        {
            this.listenForColumnToggle();
        };

        init.call(this);
    }

    ProductImage.SELECTOR_CONTAINERS = '.order-product-image-container';
    ProductImage.PREFIX_CONTAINER = '#order-product-image-container_';
    ProductImage.URI = '/orders/images';
    ProductImage.LOADER = `<div class="indicator-sizer -default u-margin-center">
                                ${loadingIndicator.getIndicator()}
                           </div>`;

    ProductImage.prototype.listenForColumnToggle = function()
    {
        var self = this;
        $(this.getTableElement()).on('fnSetColumnVis', function(event, columnIndex, on)
        {
            if (!on) {
                return;
            }
            var column = $(this).dataTable().fnSettings().aoColumns[columnIndex];
            if (column.mData != 'image') {
                return;
            }
            if (self.areImagesAlreadyLoaded()) {
                return;
            }
            self.loadImages();
        });
    };

    ProductImage.prototype.areImagesAlreadyLoaded = function()
    {
        var alreadyLoaded = false;
        $(this.getTableElement()).find(ProductImage.SELECTOR_CONTAINERS).each(function()
        {
            var container = this;
            var image = $(container).find('img');
            if (image.length > 0 && image.attr('src') != '-') {
                alreadyLoaded = true;
                return false; // break
            }
        });
        return alreadyLoaded;
    };

    ProductImage.prototype.loadImages = function()
    {
        var self = this;
        var data = {
            orders: []
        };
        $(this.getTableElement()).find('tr').each(function()
        {
            var tr = this;
            $(tr).find(ProductImage.SELECTOR_CONTAINERS).html(ProductImage.LOADER);
            var checkbox = $(this).find('input.checkbox-id');
            data.orders.push($(checkbox).val());
        });
        CGMustache.get().fetchTemplate(this.getTemplatePath(), function(template, cgMustache)
        {
            self.loadImagesWithTemplateAndData(template, data);
        });
    };

    ProductImage.prototype.loadImagesWithTemplateAndData = function(template, data)
    {
        this.getAjaxRequester().sendRequest(ProductImage.URI, data, function(response)
        {
            for (var orderId in response) {
                var imageUrl = response[orderId];
                var contents = CGMustache.get().renderTemplate(template, {image: imageUrl, id: orderId});
                $(ProductImage.PREFIX_CONTAINER + orderId).replaceWith(contents);
            }
        });
    };

    return ProductImage;
});