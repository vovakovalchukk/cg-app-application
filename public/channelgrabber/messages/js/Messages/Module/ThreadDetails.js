define([
    'Messages/ModuleAbstract',
    'Messages/Module/ThreadDetails/EventHandler',
    'Messages/Thread/Service',
    'DomManipulator',
    'Messages/Module/ThreadDetails/Panel/Controls',
    'Messages/Module/ThreadDetails/Panel/Body',
    'Messages/Module/ThreadDetails/Panel/Respond',
], function(
    ModuleAbstract,
    EventHandler,
    service,
    domManipulator,
    ControlsPanel,
    BodyPanel,
    RespondPanel
) {
    var ThreadDetails = function(application)
    {
        ModuleAbstract.call(this, application);

        var thread;
        var panels = [];

        this.getService = function()
        {
            return service;
        };

        this.getDomManipulator = function()
        {
            return domManipulator;
        };

        this.getThread = function()
        {
            return thread;
        };

        this.setThread = function(newThread)
        {
            thread = newThread;
            return this;
        };

        this.getPanels = function()
        {
            return panels;
        };

        this.setPanels = function(newPanels)
        {
            panels = newPanels;
            return this;
        };

        this.addPanel = function(panel)
        {
            panels.push(panel);
            return this;
        };

        var init = function()
        {
            this.setEventHandler(new EventHandler(this));

        };
        init.call(this);
    };

    ThreadDetails.SELECTOR = '.message-preview';
    ThreadDetails.SELECTOR_NO_MSG = '.no-messages-content';

    ThreadDetails.prototype = Object.create(ModuleAbstract.prototype);

    ThreadDetails.prototype.loadThread = function(thread, force)
    {
        if (!force && this.getThread() && this.getThread().getId() == thread.getId()) {
            return;
        }
        this.setThread(thread);
        this.loadPanels(thread);
    };

    ThreadDetails.prototype.loadPanels = function(thread)
    {
        var assignableUsers = this.getApplication().getAssignableUsers();
        this.resetPanels()
            .addPanel(new ControlsPanel(thread, assignableUsers))
            .addPanel(new BodyPanel(thread))
            .addPanel(new RespondPanel(thread));
        this.getDomManipulator().hide(ThreadDetails.SELECTOR+' '+ThreadDetails.SELECTOR_NO_MSG);
    };

    ThreadDetails.prototype.resetPanels = function()
    {
        this.setPanels([]);
        this.getDomManipulator().remove(ThreadDetails.SELECTOR+' > *:not('+ThreadDetails.SELECTOR_NO_MSG+')');
        this.getDomManipulator().show(ThreadDetails.SELECTOR+' '+ThreadDetails.SELECTOR_NO_MSG);
        return this;
    };

    ThreadDetails.prototype.refresh = function()
    {
        var self = this;
        this.getApplication().busy();
        this.getService().fetch(this.getThread().getId(), function(thread)
        {
            self.loadThread(thread, true);
            self.getApplication().unbusy();
        }, function(response)
        {
            self.getApplication().unbusy();
            n.ajaxError(response);
        });
    };

    return ThreadDetails;
});