define([], function() {
    const dragAndDropList = function({setItems, allItems, items, targetNode, listClasses}) {
        this.handleListChange = setItems;
        this.allItems = allItems;
        this.renderedItems = items.slice();

        this.listClasses = listClasses;
        this.rowMap = new Map;
        this.sortableListNode = null;
        this.targetNode = targetNode;

        return this.generateList();
    };

    const mustache = {};

    dragAndDropList.ADD_ROW_CLASSNAME = 'add-row-item';
    
    async function getTemplates () {
        if (mustache.templates && mustache.cgmustache) {
            return mustache;
        }
        const promise = new Promise(function(resolve, reject) {
            var templateUrlMap = {
                select: '/channelgrabber/zf2-v4-ui/templates/elements/custom-select.mustache',
                colourPicker: '/channelgrabber/zf2-v4-ui/templates/elements/colour-picker.mustache',
                align: '/channelgrabber/zf2-v4-ui/templates/elements/align.mustache',
                font: '/channelgrabber/settings/template/InvoiceDesigner/Template/Inspector/font.mustache',
                collapsible: '/channelgrabber/zf2-v4-ui/templates/elements/collapsible.mustache'
            };
            CGMustache.get().fetchTemplates(templateUrlMap, function(templates, cgmustache)
            {
                mustache.templates = templates;
                mustache.cgmustache = cgmustache;
                resolve({templates, cgmustache});
            });
        });
        return promise;
    }
    
    dragAndDropList.prototype.generateList = async function() {
        let mustache = await getTemplates();

        let html = `<div>
            <h3>table columns</h3>
            <ul class="${this.listClasses.itemsContainer} drag-and-drop-item-list">
                ${this.renderedItems.map(column => {
            return this.createItemRowHTML(column)
        }).join('')}
            </ul>
            <a title="add" class="${dragAndDropList.ADD_ROW_CLASSNAME}">add</a>
        </div>`;

        let fragment = document.createRange().createContextualFragment(html);
        this.targetNode.append(fragment);

        this.sortableListNode = document.getElementsByClassName(this.listClasses.itemsContainer)[0];

        [...this.sortableListNode.children].forEach((node, index) => {
            this.rowMap.set(node, this.renderedItems[index]);
        });

        this.enableDragList();
        this.addAddOnClick();
//
        return fragment;
    };

    function processOptions(options){
        return options.map(option => {
            return {
                title : option.optionText,
                value: option.id
            }
        });
    }

    dragAndDropList.prototype.createItemRowHTML = function(column) {
        let defaultInputText = column.displayText ? column.displayText : column.optionText;
        let select = mustache.cgmustache.renderTemplate(mustache.templates.select, {
//            id:
              name: `${column.id}`,
              options: processOptions(this.allItems),
              sizeClass: 'invoice-designer-drag-list-select',
              holder: '-'
        });

//
//        <span style="border:solid 1px red; width:100px;">${column.optionText}</span>
//

        return `<li class="${this.listClasses.listItem}">
            <div title="drag" class="${this.listClasses.dragIcon}"></div>
            ${select}
            <input value="${defaultInputText}" class="inputbox invoice-designer-drag-list-input" />
            <div title="delete" class="${this.listClasses.deleteClass} invoice-designer-delete-icon"></div>
        </li>

`
    };

    dragAndDropList.prototype.getNewItem = function() {
        let availableItems = getAvailableItems.call(this);
        if (!availableItems.length) {
            return;
        }
        return availableItems[0];
    };

    dragAndDropList.prototype.addAddOnClick = function() {
        const addNode = document.querySelector(`.${dragAndDropList.ADD_ROW_CLASSNAME}`);

        addNode.onclick = this.addClick.bind(this);
    };

    dragAndDropList.prototype.addClick = function() {
        let newItem = this.getNewItem();

        if (!newItem) {
            return;
        }

        newItem.position = this.renderedItems.length;
        this.renderedItems.push(newItem);

        let newItemHTML = this.createItemRowHTML(newItem);
        let newRowNode = document.createRange().createContextualFragment(newItemHTML);

        this.sortableListNode.append(newRowNode);

        let rowNodeInDom = this.sortableListNode.children[this.sortableListNode.children.length - 1];

        this.enableDragItem(rowNodeInDom);

        this.rowMap.set(rowNodeInDom, newItem);
        this.handleListChange(this.renderedItems);
    };

    dragAndDropList.prototype.removeItemClick = function(rowNode) {
        let columnForNode = this.rowMap.get(rowNode);

        this.renderedItems = this.renderedItems.filter(column => column !== columnForNode);
        this.rowMap.delete(rowNode);
        rowNode.parentNode.removeChild(rowNode);

        this.handleListChange(this.renderedItems);
    };

    dragAndDropList.prototype.handleDrop = function(item) {
        item.target.classList.remove(this.listClasses.dragActive);
        this.handleListChange(this.renderedItems);
    };

    dragAndDropList.prototype.enableDragList = function() {
        [...this.sortableListNode.children].forEach((item) => {
            this.enableDragItem(item)
        });
    };

    dragAndDropList.prototype.enableDragItem = function(rowNode) {
        rowNode.setAttribute('draggable', true)
        rowNode.ondrag = this.handleDrag.bind(this);
        rowNode.ondragend = this.handleDrop.bind(this);
        let deleteNode = rowNode.getElementsByClassName(this.listClasses.deleteClass)[0];
        deleteNode.onclick = this.removeItemClick.bind(this, rowNode);
    };

    dragAndDropList.prototype.handleDrag = function(item) {
        const selectedItem = item.target,
            list = selectedItem.parentNode,
            x = event.clientX,
            y = event.clientY;

        selectedItem.classList.add(this.listClasses.dragActive);
        let swapItem = document.elementFromPoint(x, y) === null ? selectedItem : document.elementFromPoint(x, y);

        if (list !== swapItem.parentNode) {
            return
        }

        swapItem = swapItem !== selectedItem.nextSibling ? swapItem : swapItem.nextSibling;
        list.insertBefore(selectedItem, swapItem);

        [...list.children].forEach((node, index) => {
            const columnJson = this.rowMap.get(node);
            columnJson.position = index;
        });
    };

    function getAvailableItems() {
        return this.allItems.filter(item => {
            for (let renderedItem of this.renderedItems) {
                if (renderedItem.id === item.id) {
                    return false;
                }
            }
            return true
        });
    }

    return dragAndDropList;
});