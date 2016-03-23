define([
    'ShippingAlias/DomManipulator',
    'ShippingAlias/MethodCollection',
    'ShippingAlias/AccountCollection',
    'ShippingAlias/DomListener/AddButton',
    'ShippingAlias/DomListener/DeleteButton',
    'ShippingAlias/DomListener/AliasChange',
    'ShippingAlias/DomListener/AccountChange',
    'ShippingAlias/Mapper'
], function(
    domManipulator,
    methodCollection,
    accountCollection,
    addButtonListener,
    deleteButtonListener,
    aliasChangeListener,
    accountChangeListener,
    mapper
) {
    function Service(shippingMethods, shippingAccounts, rootOuId)
    {
        var init = function()
        {
            methodCollection.setItems(mapper.fromCollectionToOptions(shippingMethods));
            var accountOptions = mapper.fromCollectionToOptions(shippingAccounts);
            accountOptions.unshift({
                title: 'None',
                value: '0'
            });
            accountCollection.setItems(accountOptions);

            addButtonListener.init();
            deleteButtonListener.init(rootOuId);
            aliasChangeListener.init(rootOuId);
            accountChangeListener.init();

            domManipulator.updateAllAliasMethodCheckboxes();
        };
        init.call(this);
    }

    return Service;
});