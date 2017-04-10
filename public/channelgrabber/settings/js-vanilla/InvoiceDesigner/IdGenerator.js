define(function()
{
    var IdGenerator = function()
    {

    };

    IdGenerator.prototype.generate = function()
    {
        return (new Date()).getTime()+String(Math.random()).substr(2);
    };

    return new IdGenerator();
});