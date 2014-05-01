define(['./TextAbstract.js'], function(TextAbstract)
{
    var Text = function()
    {
        TextAbstract.call(this);
    };

    Text.prototype = Object.create(TextAbstract.prototype);

    return new Text();
});