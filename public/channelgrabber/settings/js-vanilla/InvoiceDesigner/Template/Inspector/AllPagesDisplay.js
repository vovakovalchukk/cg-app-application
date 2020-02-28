define([
    'InvoiceDesigner/Template/InspectorAbstract',
    'InvoiceDesigner/Template/Inspector/DomListener/AllPagesDisplay',
    'cg-mustache'
], function(
    InspectorAbstract,
    allPagesDisplayDomListener,
    CGMustache
) {
    const AllPagesDisplay = function() {
        InspectorAbstract.call(this);

        this.setId('allPagesDisplay');
        this.setInspectedAttributes(['displayOnAllPages']);
    };

    AllPagesDisplay.ALL_PAGES_DISPLAY_INSPECTOR_SELECTOR = '#all-pages-display-inspector';
    AllPagesDisplay.ALL_PAGES_DISPLAY_CHECKBOX_ID = 'all-pages-display-checkbox';

    AllPagesDisplay.prototype = Object.create(InspectorAbstract.prototype);

    AllPagesDisplay.prototype.hide = function() {
        this.getDomManipulator().render(AllPagesDisplay.ALL_PAGES_DISPLAY_INSPECTOR_SELECTOR, "");
    };

    AllPagesDisplay.prototype.showForElement = function(element, template, service) {
        const templateUrlMap = {
            checkbox: '/channelgrabber/zf2-v4-ui/templates/elements/checkbox.mustache'
        };
        CGMustache.get().fetchTemplates(templateUrlMap, (templates, cgmustache) => {
            const existingSelection = element.getDisplayOnAllPages();

            const checkbox = cgmustache.renderTemplate(
                templates,
                {
                    class: 'u-margin-right-small',
                    selected: false || existingSelection,
                    id: AllPagesDisplay.ALL_PAGES_DISPLAY_CHECKBOX_ID,
                    name: 'display-on-all-pages',
                    label: 'Display On All Pages'
                },
                'checkbox'
            );
            const checkboxNode = document.createRange().createContextualFragment(checkbox);

            const inspectorContainer = document.createElement('div');
            inspectorContainer.className = 'inspector-holder u-margin-top-med';
            inspectorContainer.appendChild(checkboxNode);

            this.getDomManipulator().render(
                AllPagesDisplay.ALL_PAGES_DISPLAY_INSPECTOR_SELECTOR, inspectorContainer
            );

            allPagesDisplayDomListener.init(this, element);
        });
    };

    AllPagesDisplay.prototype.setDisplayOnAllPages = (element, desiredValue) => {
        element.setDisplayOnAllPages(desiredValue);
    };

    AllPagesDisplay.prototype.removeElement = function(template, element) {
        template.removeElement(element);
    };

    AllPagesDisplay.prototype.getAllPagesDisplayCheckboxId = function() {
        return AllPagesDisplay.ALL_PAGES_DISPLAY_CHECKBOX_ID;
    };

    return new AllPagesDisplay();
});