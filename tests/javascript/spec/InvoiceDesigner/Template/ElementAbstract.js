define(['jasq', 'InvoiceDesigner/Template/Entity'], function ()
{
    describe('The Element module', 'InvoiceDesigner/Template/ElementAbstract', function ()
    {
        it('should accept subscribers', function(ElementAbstract)
        {
            var element = new ElementAbstract();
            var subscriber = getMockSubscriber(ElementAbstract);
            
            element.subscribe(subscriber);
            expect(element.getSubscribers().length).toBe(1);
        });

        it('should not accept invalid subscribers', function(ElementAbstract)
        {
            var element = new ElementAbstract();
            var subscriber = {};

            try {
                element.subscribe(subscriber);
                var errored = false;
            } catch (e) {
                var errored = true;
            }
            expect(errored).toBe(true);
        });

        it('should unsubscribe subscribers', function(ElementAbstract)
        {
            var element = new ElementAbstract();
            var subscriber = getMockSubscriber(ElementAbstract);

            element.subscribe(subscriber);
            expect(element.getSubscribers().length).toBe(1);
            element.unsubscribe(subscriber);
            expect(element.getSubscribers().length).toBe(0);
        });

        it('should publish to subscribers', function(ElementAbstract)
        {
            var element = new ElementAbstract();
            var subscriber = getMockSubscriber(ElementAbstract);
            spyOn(subscriber, ElementAbstract.PUBLISH_METHOD);

            element.subscribe(subscriber);
            element.publish();
            expect(subscriber[ElementAbstract.PUBLISH_METHOD]).toHaveBeenCalled();
        });

        var getMockSubscriber = function(ElementAbstract)
        {
            var subscriber = {
                getId: function()
                {
                    return 1;
                }
            };
            subscriber[ElementAbstract.PUBLISH_METHOD] = function() {};
            return subscriber;
        };
    });
});