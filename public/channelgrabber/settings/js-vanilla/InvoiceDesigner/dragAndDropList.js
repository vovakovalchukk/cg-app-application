define([], function() {
    const dragAndDropList = function({setItems, allItems, items, targetNode, listClasses}) {
        this.handleListChange = setItems;
        this.allItems = allItems;
        this.renderedItems = items.slice();

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

        let html = `<div class="inspector-holder">
            <ul class="${this.listClasses.itemsContainer} drag-and-drop-item-list">
                ${this.renderedItems.map(column => {
            return this.createItemRowHTML(column)
        }).join('')}
            </ul>
            <a title="add" class="${dragAndDropList.ADD_ROW_CLASSNAME}">add</a>
        </div>`;

        return html;
//        let fragment = document.createRange().createContextualFragment(html);
//        this.targetNode.append(fragment);
//
//        this.sortableListNode = document.getElementsByClassName(this.listClasses.itemsContainer)[0];
//
//        [...this.sortableListNode.children].forEach((node, index) => {
//            this.rowMap.set(node, this.renderedItems[index]);
//        });
//
//        this.enableDragList();
//        this.addAddOnClick();
//        this.addSelectsOnChange();
//
//        return fragment;
    };

    dragAndDropList.prototype.initList = function(html) {
        this.sortableListNode = document.getElementsByClassName(this.listClasses.itemsContainer)[0];

        [...this.sortableListNode.children].forEach((node, index) => {
            this.rowMap.set(node, this.renderedItems[index]);
        });

        this.enableDragList();
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
        this.enableDeleteItem(rowNodeInDom);

        this.rowMap.set(rowNodeInDom, newItem);
        this.handleListChange(this.renderedItems);
    };

    dragAndDropList.prototype.addSelectsOnChange = function() {
        this.rowMap.forEach((columnJson, node) => {
            const userInput = node.querySelector(`.${this.listClasses.listItemInput}`);

            const selectForRow = node.querySelector(`.${dragAndDropList.DRAG_LIST_SELECT_CLASS}`);
            const selectInput = selectForRow.querySelector('input');

            const config = {attributes: true};
            const callback = (mutationsList) => {
                for (let mutation of mutationsList) {
                    if (mutation.type !== 'attributes' && mutation.attributeName !== 'value') {
                        return;
                    }
                    console.log('this is the callback')
                    // todo - change the associated INPUT....
                    let optionSelected = this.allItems.find(item => (item.id === selectInput.value));

                    userInput.value = getDefaultInputValueFromOption(optionSelected);

                    //todo - trigger the handleChange
                }
            };
            const observer = new MutationObserver(callback);
            observer.observe(selectInput, config);
        });
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
    };

    dragAndDropList.prototype.enableDeleteItem = function(rowNode) {
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

    function getDefaultInputValueFromOption(optionSelected) {
        return optionSelected.displayText || optionSelected.optionText;
    }

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