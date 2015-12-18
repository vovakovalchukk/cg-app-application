define([
    'Messages/Module/ThreadDetails/PanelAbstract',
    'Messages/Module/ThreadDetails/Panel/Controls/EventHandler',
    'Messages/Thread/Service',
    'cg-mustache'
], function(
    PanelAbstract,
    EventHandler,
    service,
    CGMustache
) {
    var Controls = function(module, thread, assignableUsers)
    {
        PanelAbstract.call(this, module, thread);

        var assignableUserOptions = [];

        this.getAssignableUserOptions = function()
        {
            return assignableUserOptions;
        };

        this.getService = function()
        {
            return service;
        };

        var init = function()
        {
            this.setEventHandler(new EventHandler(this));
            assignableUserOptions.push({value: null, title: 'Unassigned'});
            for (var id in assignableUsers) {
                assignableUserOptions.push({value: id, title: assignableUsers[id]});
            }
            this.render(thread);
        };
        init.call(this);
    };

    Controls.SELECTOR = '.preview-header';
    Controls.TEMPLATE = '/channelgrabber/messages/template/Messages/ThreadDetails/Panel/controls.mustache';
    Controls.TEMPLATE_SELECT = '/channelgrabber/zf2-v4-ui/templates/elements/custom-select.mustache';

    Controls.prototype = Object.create(PanelAbstract.prototype);

    Controls.prototype.nonAssignableStatuses = {'resolved': true};
    Controls.prototype.nonResolvableStatuses = {'resolved': true};

    Controls.prototype.render = function(thread)
    {
        var self = this;
        var resolvable = !(this.nonResolvableStatuses[thread.getStatus()]);
        var assignable = !(this.nonAssignableStatuses[thread.getStatus()]);
        var assignableUserOptions = this.getAssignableUserOptionsForThread(thread);
        // global userId
        var releasable = (assignable && thread.getAssignedUserId() == userId);
        var takable = (assignable && !releasable);
        CGMustache.get().fetchTemplates({main: Controls.TEMPLATE, select: Controls.TEMPLATE_SELECT}, function(templates, cgmustache) {
            var selectHtml = cgmustache.renderTemplate(templates, {
                'id': 'control-assignee',
                'title': 'Assigned',
                'options': assignableUserOptions
            }, 'select');
            var html = cgmustache.renderTemplate(templates, {
                'subject': thread.getSubject(),
                'channel': thread.getChannel(),
                'accountName': thread.getAccountName(),
                'name': thread.getName(),
                'status': thread.getStatus().replace(/ /g, '-').toLowerCase(),
                'statusText': thread.getStatus().replace(/_-/g, ' ').ucfirst(),
                'assignedUserId': thread.getAssignedUserId(),
                'externalUserName': thread.getExternalUsername(),
                'ordersCount': thread.getOrdersCount(),
                'ordersLink': thread.getOrdersLink,
                'takable': takable,
                'releasable': releasable,
                'resolvable': resolvable,
                'assignable': assignable,
                'singleUserMode': self.getModule().getApplication().getSingleUserMode()
            }, 'main', {assigneeSelect: selectHtml});
            self.getDomManipulator().append(PanelAbstract.SELECTOR_CONTAINER, html);
        });
    };

    Controls.prototype.getAssignableUserOptionsForThread = function(thread)
    {
        // Clone the assignee options
        var assigneeOptions = JSON.parse(JSON.stringify(this.getAssignableUserOptions()));
        for (var key in assigneeOptions) {
            if (assigneeOptions[key].value != thread.getAssignedUserId()) {
                continue;
            }
            assigneeOptions[key].selected = true;
            break;
        }
        return assigneeOptions;
    };

    Controls.prototype.take = function()
    {
        var self = this;
        // global n, represents the notifications class from zf2-v4-ui
        n.notice('Updating assigned user');
        this.getService().assignToActiveUser(this.getThread(), function(updatedThread)
        {
            self.setThread(updatedThread);
            n.success('The assignee has been updated to you');
            // Expected to be picked up by Module\Filter\EventHandler, Module\ThreadDetails\EventHandler
            self.getEventHandler().triggerAssigneeChanged(updatedThread);
        });
    };

    Controls.prototype.release = function()
    {
        this.assign(null);
    };

    Controls.prototype.assign = function(userId)
    {
        var self = this;
        if (isNaN(parseInt(userId))) {
            userId = null;
        }

        this.getThread().setAssignedUserId(userId);
        n.notice('Updating assigned user');
        this.getService().saveAssigned(this.getThread(), function(thread)
        {
            n.success('The assignee has been updated successfully');
            // Need to update the ui, trigger an event.
            // Expected to be picked up by Module\Filter\EventHandler, Module\ThreadDetails\EventHandler
            self.getEventHandler().triggerAssigneeChanged(thread);
        });
    };

    Controls.prototype.resolve = function()
    {
        var self = this;
        n.notice('Updating status');
        this.getService().resolve(this.getThread(), function(updatedThread)
        {
            self.setThread(updatedThread);
            n.success('The status has been updated successfully');
            // Expected to be picked up by Module\Filter\EventHandler, Module\ThreadDetails\EventHandler
            self.getEventHandler().triggerStatusChanged(updatedThread);
        });
    };

    Controls.prototype.getNonResolvableStatuses = function()
    {
        return this.nonResolvableStatuses;
    };

    return Controls;
});
