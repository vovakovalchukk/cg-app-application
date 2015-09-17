define(['AjaxRequester'], function(ajaxRequester) {
    var Ajax = function() {};

    Ajax.prototype.save = function(url, accountId, host, key, secret) {
        n.notice('Connecting WooCommerce account');
        ajaxRequester.sendRequest(
            url,
            {
                'accountId': accountId,
                'host': host,
                'key': key,
                'secret': secret
            },
            function(data) {
                n.success('WooCommerce account connected');
                window.location = data.redirectUrl;
            }
        );
    };

    return new Ajax();
});
