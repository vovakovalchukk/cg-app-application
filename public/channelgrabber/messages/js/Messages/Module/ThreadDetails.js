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

        this.addPanel = function(panel)
        {
            panels.push(panel);
            return this;
        };

        this.resetPanels = function()
        {
            panels = [];
            this.getDomManipulator().remove(ThreadDetails.SELECTOR+' > *:not('+ThreadDetails.SELECTOR_NO_MSG+')');
            this.getDomManipulator().show(ThreadDetails.SELECTOR+' '+ThreadDetails.SELECTOR_NO_MSG);
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

    ThreadDetails.prototype.loadThread = function(thread)
    {
        this.setThread(thread);
        this.loadPanels(thread);
    };

    ThreadDetails.prototype.loadPanels = function(thread)
    {
        this.resetPanels()
            .addPanel(new ControlsPanel(thread))
            .addPanel(new BodyPanel(thread))
            .addPanel(new RespondPanel(thread));
        this.getDomManipulator().hide(ThreadDetails.SELECTOR+' '+ThreadDetails.SELECTOR_NO_MSG);
    };

    ThreadDetails.prototype.refresh = function()
    {
        var self = this;
        this.getService().fetch(this.getThread().getId(), function(thread)
        {
            self.loadThread(thread);
        });
    };

    return ThreadDetails;
});