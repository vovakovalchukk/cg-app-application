define([
    'ShippingAlias/DomManipulator',
    'ShippingAlias/MethodCollection',
    'ShippingAlias/AccountCollection',
    'ShippingAlias/DomListener/AddButton',
    'ShippingAlias/DomListener/DeleteButton',
    'ShippingAlias/DomListener/AliasChange',
    'ShippingAlias/DomListener/AccountChange',
    'ShippingAlias/DomListener/ServiceChange',
    'ShippingAlias/Mapper'
], function(
    domManipulator,
    methodCollection,
    accountCollection,
    addButtonListener,
    deleteButtonListener,
    aliasChangeListener,
    accountChangeListener,
    serviceChangeListener,
    mapper
) {
    function Service(shippingMethods, shippingAccountOptions, rootOuId, templatePath)
    {
        var init = function()
        {
            methodCollection.setItems(mapper.fromCollectionToOptions(shippingMethods));
            accountCollection.setItems(shippingAccountOptions);

            addButtonListener.init();
            deleteButtonListener.init(rootOuId);
            aliasChangeListener.init(rootOuId);
            accountChangeListener.init();
            serviceChangeListener.init(templatePath);

            domManipulator.updateAllAliasMethodCheckboxes();
        };
        init.call(this);
    }

    return Service;
});