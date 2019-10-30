define([], function() {
    let selectCounter = 0;

    const dragAndDropList = function(settings) {
        let {
            id,
            setItems,
            allItems,
            items,
            itemLimit,
            targetNode,
            listClasses,
            renderTextInput
        } = settings;
        this.id = id;
        this.handleListChange = setItems;
        this.allItems = allItems;
        this.items = items.slice();
        this.itemLimit = itemLimit;
        this.renderTextInput = !!renderTextInput;
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
        const addButton = this.renderAddButton();
        const html = `<div id="${this.id}" class="inspector-holder">
            <ul class="${this.listClasses.itemsContainer} drag-and-drop-item-list">
                ${this.items.map(column => {
                    return this.createItemRowHTML(column)
                }).join('')}
            </ul>
            ${addButton}
        </div>`;

        return html;
    };

    dragAndDropList.prototype.initList = function() {
        const listContainer = document.getElementById(this.id).getElementsByTagName('ul')[0];
        this.sortableListNode = listContainer;

        [...this.sortableListNode.children].forEach((node, index) => {
            this.rowMap.set(node, this.items[index]);
            this.enableDragItem(node);
            this.enableDeleteItem(node);
            if (!this.renderTextInput) {
                return;
            }
            this.addInputChangeListener(node);
        });

        this.addAddOnClick();
        this.addSelectsOnChange();
    };

    dragAndDropList.prototype.renderAddButton = function() {
        if (this.hasReachedItemLimit()) {
            return '';
        }
        return `<div title="add" class="${dragAndDropList.ADD_ROW_CLASSNAME} ${this.listClasses.addIcon}"></div>`
    };

    dragAndDropList.prototype.createItemRowHTML = function(column) {
        const options = processOptions(this.allItems);
        const selectedOption = options.find(option => (
            option.value === column.id || option.value === column.column
        ));

        const selectName = this.createSelectName(column);
        selectCounter ++;

        const select = mustache.cgmustache.renderTemplate(mustache.templates.select, {
            name: `${selectName}`,
            options,
            sizeClass: 'invoice-designer-drag-list-select',
            holder: dragAndDropList.DRAG_LIST_SELECT_CLASS,
            initialValue: selectedOption.value,
            initialTitle: selectedOption.title
        });
        const textInput = this.getTextInput(column);

        return `<li class="${this.listClasses.listItem}">
            <div class="${this.listClasses.dragContainer}">
                <div title="click and hold to drag items" class="${this.listClasses.dragIcon}"></div>
            </div>
            <div class="invoice-designer-input-positioner u-width-100pc">
                ${textInput}
                ${select}
            </div>
            <div title="delete" class="${this.listClasses.deleteClass} invoice-designer-delete-icon"></div>
        </li>`;
    };

    dragAndDropList.prototype.getTextInput = function(column) {
        if (!this.renderTextInput) {
            return '';
        }
        const defaultInputText = getDefaultInputValueFromOption(column);
        return `<input 
                    value="${defaultInputText}" 
                    class="inputbox ${this.listClasses.listItemInput}" 
                />`
    };

    dragAndDropList.prototype.addInputChangeListener = function(rowNode) {
        const columnValue = this.rowMap.get(rowNode);
        const inputNode = rowNode.querySelector(`.${this.listClasses.listItemInput}`);

        let timeoutId = null;
        const timeout = 400;

        const onChange = event => {
            const value = event.target.value;
            clearTimeout(timeoutId);

            timeoutId = setTimeout(() => {
                columnValue.displayText = value;
                this.changeList();
            }, timeout);
        };

        inputNode.oninput = onChange;
    };

    dragAndDropList.prototype.getNewItem = function() {
        const renderedColumns = this.getRenderedColumns();
        const availableItems = getAvailableItems(this.allItems, renderedColumns);
        if (!availableItems.length) {
            return;
        }
        return availableItems[0];
    };

    dragAndDropList.prototype.addAddOnClick = function() {
        const addNode = document.getElementById(this.id).querySelector(`.${dragAndDropList.ADD_ROW_CLASSNAME}`);
        addNode.onclick = this.addClick.bind(this);
    };

    dragAndDropList.prototype.changeList = function() {
        this.updateColumnPositions();
        const renderedColumns = this.getRenderedColumns();
        this.handleListChange(renderedColumns);
    };

    dragAndDropList.prototype.hasReachedItemLimit = function() {
        if (typeof this.itemLimit == 'undefined') {
            return false;
        }
        return this.rowMap.size >= this.itemLimit;
    };

    dragAndDropList.prototype.addClick = function() {
        const newItem = this.getNewItem();

        if (!newItem || this.hasReachedItemLimit()) {
            return;
        }

        const renderedItems = this.getRenderedColumns();
        newItem.position = renderedItems.length;
        renderedItems.push(newItem);

        let newItemHTML = this.createItemRowHTML(newItem);
        let newRowNode = document.createRange().createContextualFragment(newItemHTML);

        this.sortableListNode.append(newRowNode);

        const rowNodeInDom = this.sortableListNode.children[this.sortableListNode.children.length - 1];

        this.enableDragItem(rowNodeInDom);
        this.enableDeleteItem(rowNodeInDom);

        this.addSelectMutationObserverForItem(rowNodeInDom, newItem);
        if (this.renderInput) {
            this.addInputChangeListener(rowNodeInDom);
        }

        this.rowMap.set(rowNodeInDom, newItem);

        this.changeList();
    };

    dragAndDropList.prototype.addSelectsOnChange = function() {
        this.rowMap.forEach((columnJson, node) => {
            this.addSelectMutationObserverForItem(node, columnJson);
        });
    };

    dragAndDropList.prototype.addSelectMutationObserverForItem = function(node, columnJson) {
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

                if (this.renderTextInput) {
                    userInput.value = getDefaultInputValueFromOption(optionSelected);
                }

                this.rowMap.set(node, optionSelected);

                this.changeList();
            }
        };
        const observer = new MutationObserver(callback);
        observer.observe(selectInput, config);
    };

    dragAndDropList.prototype.createSelectName = function(column) {
        return `${this.id}-${selectCounter}-${column.position}`
    };

    dragAndDropList.prototype.getRenderedColumns = function() {
        return Array.from(this.rowMap, ([key, value]) => value);
    };

    dragAndDropList.prototype.removeItemClick = function(rowNode) {
        this.rowMap.delete(rowNode);
        rowNode.parentNode.removeChild(rowNode);
        this.changeList();
    };

    dragAndDropList.prototype.handleDrop = function(item) {
        item.target.classList.remove(this.listClasses.dragActive);
        this.changeList();
    };

    dragAndDropList.prototype.enableDragItem = function(rowNode) {
        rowNode.setAttribute('draggable', true);
        rowNode.ondrag = this.handleDrag.bind(this);
        rowNode.ondragend = this.handleDrop.bind(this);
    };

    dragAndDropList.prototype.enableDeleteItem = function(rowNode) {
        const deleteNode = rowNode.getElementsByClassName(this.listClasses.deleteClass)[0];
        deleteNode.onclick = this.removeItemClick.bind(this, rowNode);
    };

    dragAndDropList.prototype.updateColumnPositions = function() {
        [...this.sortableListNode.children].forEach((node, index) => {
            const columnJson = this.rowMap.get(node);
            columnJson.position = index;
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

    function getAvailableItems(allItems, renderedItems) {
        return allItems.filter(item => {
            for (let renderedItem of renderedItems) {
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