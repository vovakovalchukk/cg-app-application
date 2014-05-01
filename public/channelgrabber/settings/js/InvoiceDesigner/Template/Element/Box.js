define(['../ElementAbstract.js'], function(ElementAbstract)
{
    var Box = function()
    {
        ElementAbstract.call(this);
    };

    Box.prototype = Object.create(ElementAbstract.prototype);

    return new Box();
});