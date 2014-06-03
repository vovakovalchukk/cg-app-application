define(['jasq'], function ()
{
    describe('The PubSub module', 'InvoiceDesigner/PubSubAbstract', function ()
    {
        var subscriber;

        beforeEach(function() {
            subscriber = jasmine.createSpyObj('item', ['getId', 'publisherUpdate']);
            subscriber.getId.andReturn(1);
        });

        it('should accept subscribers', function(PubSubAbstract)
        {
            var element = new PubSubAbstract();
            
            element.subscribe(subscriber);
            expect(element.getSubscribers().length).toBe(1);
        });

        it('should not accept invalid subscribers', function(PubSubAbstract)
        {
            var element = new PubSubAbstract();
            var subscriber = {};

            expect(function() { element.subscribe(subscriber); }).toThrow();
        });

        it('should unsubscribe subscribers', function(PubSubAbstract)
        {
            var element = new PubSubAbstract();

            element.subscribe(subscriber);
            expect(element.getSubscribers().length).toBe(1);
            element.unsubscribe(subscriber);
            expect(element.getSubscribers().length).toBe(0);
        });

        it('should publish to subscribers', function(PubSubAbstract)
        {
            var element = new PubSubAbstract();

            element.subscribe(subscriber);
            element.publish();
            expect(subscriber[PubSubAbstract.PUBLISH_METHOD]).toHaveBeenCalled();
        });
    });
});