define(['../InspectorAbstract'], function(InspectorAbstract)
{
    var TextArea = function()
    {
        InspectorAbstract.call(this);

        this.setId('text');
        this.setSupportedTypes(['text']);
    };

    TextArea.prototype = Object.create(InspectorAbstract.prototype);

    TextArea.prototype.clear = function()
    {
        /*
         * TODO (CGIV-2014)
         */
    };

    TextArea.prototype.showForElement = function(element)
    {
        /*
         * TODO (CGIV-2014)
         */
    };

    return new TextArea();
});