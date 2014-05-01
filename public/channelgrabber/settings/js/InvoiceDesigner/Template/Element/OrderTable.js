define(['../ElementAbstract.js'], function(ElementAbstract)
{
    var OrderTable = function()
    {
        ElementAbstract.call(this);
    };

    OrderTable.prototype = Object.create(ElementAbstract.prototype);

    return new OrderTable();
});