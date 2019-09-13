define([], function() {
    const dragAndDropList = function({setItems, allItems, items, targetNode, listClasses}) {
        this.handleListChange = setItems;
        this.allItems = allItems;
        this.initialItems = items.slice();

        this.listClasses = listClasses;
        this.rowMap = new Map;
        this.sortableListNode = null;
        this.targetNode = targetNode;
    };

    const mustache = {};

    dragAndDropList.ADD_ROW_CLASSNAME = 'add-row-item';
    dragAndDropList.DRAG_LIST_SELECT_CLASS = 'js-drag-list-select';

    dragAndDropList.prototype.generateList = async function() {
        await getTemplates();
        
        console.log('generating list.. creating new rows');
        const html = `<div class="inspector-holder">
            <ul class="${this.listClasses.itemsContainer} drag-and-drop-item-list">
                ${this.initialItems.map(column => {
                    return this.createItemRowHTML(column)
                }).join('')}
            </ul>
            <div title="add" class="${dragAndDropList.ADD_ROW_CLASSNAME} ${this.listClasses.addIcon}"></div>
        </div>`;

        return html;
    };

    dragAndDropList.prototype.initList = function(html) {
        this.sortableListNode = document.getElementsByClassName(this.listClasses.itemsContainer)[0];

        [...this.sortableListNode.children].forEach((node, index) => {
            this.rowMap.set(node, this.initialItems[index]);
            this.enableDragItem(node);
            this.enableDeleteItem(node);
        });

        this.addAddOnClick();
        this.addSelectsOnChange();
    };

    dragAndDropList.prototype.createItemRowHTML = function(column) {
        const defaultInputText = getDefaultInputValueFromOption(column);
        const options = processOptions(this.allItems);
        const selectedOption = options.find(option => (option.title === column.optionText));

        let select = mustache.cgmustache.renderTemplate(mustache.templates.select, {
//            id:
            name: `${column.id}`,
            options,
            sizeClass: 'invoice-designer-drag-list-select',
            holder: dragAndDropList.DRAG_LIST_SELECT_CLASS,
            initialValue: selectedOption.value,
            initialTitle: selectedOption.title
        });

        return `<li class="${this.listClasses.listItem}">
            <div class="${this.listClasses.dragContainer}">
                <div title="drag" class="${this.listClasses.dragIcon}"></div>
            </div>
            <div class="invoice-designer-input-positioner">
                <input value="${defaultInputText}" class="inputbox ${this.listClasses.listItemInput}" />
                ${select}
            </div>
            <div title="delete" class="${this.listClasses.deleteClass} invoice-designer-delete-icon"></div>
        </li>`;
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

    dragAndDropList.prototype.changeList = function() {
        this.updateColumnPositions();
        const renderedColumns = this.getRenderedColumns();
        this.handleListChange(renderedColumns);
    };

    dragAndDropList.prototype.addClick = function() {
        let newItem = this.getNewItem();

        if (!newItem) {
            return;
        }

        newItem.position = this.initialItems.length;
        this.initialItems.push(newItem);

        let newItemHTML = this.createItemRowHTML(newItem);
        let newRowNode = document.createRange().createContextualFragment(newItemHTML);

        this.sortableListNode.append(newRowNode);

        let rowNodeInDom = this.sortableListNode.children[this.sortableListNode.children.length - 1];

        this.enableDragItem(rowNodeInDom);
        this.enableDeleteItem(rowNodeInDom);

        this.rowMap.set(rowNodeInDom, newItem);

        this.changeList();
    };

    dragAndDropList.prototype.addSelectsOnChange = function() {
        this.rowMap.forEach((columnJson, node) => {
            const userInput = node.querySelector(`.${this.listClasses.listItemInput}`);

            const selectForRow = node.querySelector(`.${dragAndDropList.DRAG_LIST_SELECT_CLASS}`);
            const selectInput = selectForRow.querySelector('input');

            const config = {attributes: true};
            const callback = (mutationsList) => {
                for (let mutation  of mutationsList) {
                    if (mutation.type !== 'attributes' && mutation.attributeName !== 'value') {
                        return;
                    }
                    let optionSelected = this.allItems.find(item => (item.id === selectInput.value));
                    optionSelected = Object.assign({}, optionSelected);
                    optionSelected.position = columnJson.position;

                    userInput.value = getDefaultInputValueFromOption(optionSelected);
                    debugger;
                    this.rowMap.set(node, optionSelected);
//                    const renderedColumns = this.getRenderedColumns();
//                    this.handleListChange(renderedColumns);
                    this.changeList();
                }
            };
            const observer = new MutationObserver(callback);
            observer.observe(selectInput, config);
        });
    };

    dragAndDropList.prototype.getRenderedColumns = function() {
        return Array.from(this.rowMap, ([key, value]) => value);
    };

    dragAndDropList.prototype.removeItemClick = function(rowNode) {
        console.log('in removeClick');
        let columnForNode = this.rowMap.get(rowNode);

        this.initialItems = this.initialItems.filter(column => column !== columnForNode);
        this.rowMap.delete(rowNode);
        rowNode.parentNode.removeChild(rowNode);

        this.changeList();
    };

    dragAndDropList.prototype.handleDrop = function(item) {
        item.target.classList.remove(this.listClasses.dragActive);
        this.changeList();
    };

    dragAndDropList.prototype.enableDragItem = function(rowNode) {
        rowNode.setAttribute('draggable', true)
        rowNode.ondrag = this.handleDrag.bind(this);
        rowNode.ondragend = this.handleDrop.bind(this);
    };

    dragAndDropList.prototype.enableDeleteItem = function(rowNode) {
        let deleteNode = rowNode.getElementsByClassName(this.listClasses.deleteClass)[0];
        deleteNode.onclick = this.removeItemClick.bind(this, rowNode);
    };

    dragAndDropList.prototype.updateColumnPositions = function(list) {
        [...this.sortableListNode.children].forEach((node, index) => {
            const columnJson = this.rowMap.get(node);
            columnJson.position = index;
            console.log('this.rowMap: ', this.rowMap);

            this.rowMap.set(node, columnJson);
        });
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
    };

    function getDefaultInputValueFromOption(optionSelected) {
        return optionSelected.displayText || optionSelected.optionText;
    }

    function getAvailableItems() {
        return this.allItems.filter(item => {
            for (let renderedItem of this.initialItems) {
                if (renderedItem.id === item.id) {
                    return false;
                }
            }
            return true
        });
    }

    function processOptions(options) {
        return options.map(option => {
            return {
                title: option.optionText,
                value: option.id
            }
        });
    }

    async function getTemplates() {
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
            CGMustache.get().fetchTemplates(templateUrlMap, function(templates, cgmustache) {
                mustache.templates = templates;
                mustache.cgmustache = cgmustache;
                resolve({templates, cgmustache});
            });
        });
        return promise;
    }

    return dragAndDropList;
});