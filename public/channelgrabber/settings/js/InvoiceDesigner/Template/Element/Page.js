define(['InvoiceDesigner/Template/ElementAbstract'], function(ElementAbstract)
{
    var Page = function()
    {
        var contents;
        var additionalData = {
            backgroundImage: undefined
        };

        ElementAbstract.call(this, additionalData);

        this.getBackgroundImage = function()
        {
            return this.get('backgroundImage');
        };

        this.setBackgroundImage = function(newBackgroundImage)
        {
            this.set('backgroundImage', newBackgroundImage);
            return this;
        };

        /**
         * Used to store the generated HTML contents during rendering
         */
        this.htmlContents = function(htmlContents)
        {
            contents = htmlContents;
            return this;
        };

        this.getHtmlContents = function()
        {
            return contents;
        };
    };

    Page.prototype = Object.create(ElementAbstract.prototype);

    return Page;
});