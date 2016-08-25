define([
    'jquery',
    'EventCollator'
], function(
    $,
    eventCollator
) {
    var PickList = function()
    {
        this.getEventCollator = function()
        {
            return eventCollator;
        };

        var init = function()
        {
            var self = this;

            $(document).on('change', PickList.SELECTOR, function(event, data){
                self.triggerRequestMadeEvent(this);
            });

            $(document).on(eventCollator.getQueueTimeoutEventPrefix() + PickList.EVENT_COLLATOR_TYPE, function(event, data) {
                self.save();
            });
        };
        init.call(this);
    };

    PickList.SELECTOR = '.pick-list-form';
    PickList.SORT_FIELD_SELECTOR = '#sort-field-custom-select input';
    PickList.SORT_DIRECTION_SELECTOR = '#sort-direction-custom-select input';
    PickList.SHOW_PICTURES_SELECTOR = '#show-pictures-checkbox';
    PickList.SHOW_SKULESS_SELECTOR = '#show-skuless-checkbox';
    PickList.ETAG_SELECTOR = '#pick-list-eTag';
    PickList.EVENT_COLLATOR_TYPE = 'PickListForm';

    PickList.prototype.triggerRequestMadeEvent = function(domElement)
    {
        var unique = true;
        $(document).trigger(this.getEventCollator().getRequestMadeEvent(), [
            PickList.EVENT_COLLATOR_TYPE, true, unique
        ]);
    };

    PickList.prototype.save = function()
    {
        n.notice('Saving pick list settings');
        var eTag = $(PickList.ETAG_SELECTOR).val();
        var sortField = $(PickList.SORT_FIELD_SELECTOR).val();
        var sortDirection = $(PickList.SORT_DIRECTION_SELECTOR).val();
        var showPictures = $(PickList.SHOW_PICTURES_SELECTOR).prop('checked');
        var showSkuless = $(PickList.SHOW_SKULESS_SELECTOR).prop('checked');

        var pickListSettings = {
            "eTag": eTag,
            "sortField": sortField,
            "sortDirection": sortDirection,
            "showPictures": showPictures,
            "showSkuless": showSkuless
        };

        var self = this;

        $.ajax({
            url: "list/save",
            type: "POST",
            dataType : 'json',
            data: pickListSettings
        }).success(function(data) {
            if(data['eTag']) {
                $(PickList.ETAG_SELECTOR).val(data['eTag']);
            }
            n.success('Saved pick list settings');
        }).error(function(error, textStatus, errorThrown) {
            n.ajaxError(error, textStatus, errorThrown);
        });
    };

    return PickList;
});