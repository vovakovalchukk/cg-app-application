define([], function() {
    const dragAndDropHelper = function() {
        return this;
    };

    dragAndDropHelper.prototype.getDefaultDragAndDropCSSClasses = function() {
        return {
            dragActive: 'invoice-designer-list-item-drag-active',
            itemsContainer: 'drag-and-drop-list-list-item',
            listItem: 'invoice-designer-list-item',
            dragIcon: 'sprite sprite-drag-handle-2-black-24 invoice-designer-drag-icon',
            dragContainer: 'invoice-designer-drag-icon-container',
            deleteClass: 'sprite sprite-delete-18-black',
            addIcon: 'invoice-designer-drag-list-add-icon sprite sprite-plus-18-black',
            listItemInput: 'invoice-designer-drag-list-input'
        };
    };

    return new dragAndDropHelper;
});