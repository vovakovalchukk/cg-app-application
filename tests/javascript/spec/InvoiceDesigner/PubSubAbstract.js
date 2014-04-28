define(['jasq'], function ()
{
    describe('The PubSub module', 'InvoiceDesigner/PubSubAbstract', function ()
    {
        it('should accept subscribers', function(PubSubAbstract)
        {
            var element = new PubSubAbstract();
            var subscriber = getMockSubscriber(PubSubAbstract);
            
            element.subscribe(subscriber);
            expect(element.getSubscribers().length).toBe(1);
        });

        it('should not accept invalid subscribers', function(PubSubAbstract)
        {
            var element = new PubSubAbstract();
            var subscriber = {};

            try {
                element.subscribe(subscriber);
                var errored = false;
            } catch (e) {
                var errored = true;
            }
            expect(errored).toBe(true);
        });

        it('should unsubscribe subscribers', function(PubSubAbstract)
        {
            var element = new PubSubAbstract();
            var subscriber = getMockSubscriber(PubSubAbstract);

            element.subscribe(subscriber);
            expect(element.getSubscribers().length).toBe(1);
            element.unsubscribe(subscriber);
            expect(element.getSubscribers().length).toBe(0);
        });

        it('should publish to subscribers', function(PubSubAbstract)
        {
            var element = new PubSubAbstract();
            var subscriber = getMockSubscriber(PubSubAbstract);
            spyOn(subscriber, PubSubAbstract.PUBLISH_METHOD);

            element.subscribe(subscriber);
            element.publish();
            expect(subscriber[PubSubAbstract.PUBLISH_METHOD]).toHaveBeenCalled();
        });

        var getMockSubscriber = function(PubSubAbstract)
        {
            var subscriber = {
                getId: function()
                {
                    return 1;
                }
            };
            subscriber[PubSubAbstract.PUBLISH_METHOD] = function() {};
            return subscriber;
        };
    });
});