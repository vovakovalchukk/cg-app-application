define([
], function () {
    var Save = function () {
    };

    Save.prototype.save = function(username, password, accountId)
    {
        n.notice('Connecting ekm account');
        $.ajax({
            'url': '/settings/channel/sales/ekm/ajax',
            'method': 'POST',
            'data': {'username': username, 'password': password, 'accountId': accountId},
            'dataType': 'JSON',
            'success': function(data) {
                n.success('Ekm account connected');
                window.location = data.redirectUrl;
            },
            'error': function(error, textStatus, errorThrown) {
                return n.ajaxError(error, textStatus, errorThrown);
            }
        });
    };

    return new Save();
});