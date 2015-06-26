define([
    'Messages/ModuleAbstract',
    'Messages/Module/ThreadDetails/EventHandler',
    'Messages/Thread/Service',
    'Messages/Module/ThreadDetails/Panel/Controls',
    'Messages/Module/ThreadDetails/Panel/Body',
    'Messages/Module/ThreadDetails/Panel/Respond',
], function(
    ModuleAbstract,
    EventHandler,
    service,
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
            return this;
        };

        var init = function()
        {
            this.setEventHandler(new EventHandler(this));

        };
        init.call(this);
    };

    ThreadDetails.prototype = Object.create(ModuleAbstract.prototype);

    ThreadDetails.prototype.threadSelected = function(thread)
    {
        var self = this;
        this.getService().fetch(thread.getId(), function(thread)
        {
            self.setThread(thread);
            self.loadPanels(thread);
        });
    };

    ThreadDetails.prototype.loadPanels = function(thread)
    {
        this.resetPanels()
            .addPanel(new ControlsPanel(thread))
            .addPanel(new BodyPanel(thread))
            .addPanel(new RespondPanel(thread));
    };

    return ThreadDetails;
});