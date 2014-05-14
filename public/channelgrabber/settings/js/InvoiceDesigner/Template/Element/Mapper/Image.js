define([
    'InvoiceDesigner/Template/Element/MapperAbstract'
], function(
    MapperAbstract
) {
    var Image = function()
    {
        MapperAbstract.call(this);
    };

    Image.prototype = Object.create(MapperAbstract.prototype);

    Image.prototype.getHtmlContents = function(element)
    {
        var html = '<div class="placeholder">Image\
        <input type="file" />\n\
        <input type="button" class="button" value="Upload" /></div>\n\
        <script type="text/javascript">\n\
            require([\'jquery\'], function($) {\n\
                $(document).ready(function() {\n\
                    $(\'#'+MapperAbstract.getDomId(element)+' input[type=button]\').click(function() {\n\
                        $(this).parent().find(\'input[type=file]\').click();\n\
                    });\n\
                });\n\
            });\n\
        </script>\n';

        return html;
    };

    return new Image();
});