define([], function() {
    const dragAndDropList = function({setItems, allItems, items, targetNode, listClasses = {
        dragActive : 'drag-sort-active',
        itemsContainer : 'drag-sort-enable',
        listItem: 'drag-item',
        dragIcon: 'drag-icon'
    }}) {
        this.handleListChange = setItems;
        this.allItems = allItems;
        this.listClasses = listClasses;

        this.renderedItems = items.slice();
        this.rowMap = new Map;
        this.sortableListNode = null;
        this.targetNode = targetNode;

        return this.generateList();
    };

    dragAndDropList.DELETE_ROW_CLASSNAME = 'delete-row-item';
    dragAndDropList.ADD_ROW_CLASSNAME = 'add-row-item';

    dragAndDropList.prototype.generateList = function() {
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

        return fragment;
    };

    dragAndDropList.prototype.createItemRowHTML = function(column) {
        //todo - NB - displayText to be later wrapped in an Input
        //todo - NB - optionText to be interpretted as an option in a mustacheSelect
        let defaultInputText = column.displayText ? column.displayText : column.optionText;
        console.log('in createItemRow');
        return `<li class="${this.listClasses.listItem}">
            <a title="drag" class="${this.listClasses.dragIcon}"></a>
            <span>${column.displayText}</span>
            <span style="border:solid 1px red; width:100px;">${defaultInputText}</span>
            <a title="delete" class="${dragAndDropList.DELETE_ROW_CLASSNAME}">delete</a>
        </li>`
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

        this.rowMap.set(newRowNode, newItem);
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

    dragAndDropList.prototype.enableDragItem = function(row) {
        row.setAttribute('draggable', true)
        row.ondrag = this.handleDrag.bind(this);
        row.ondragend = this.handleDrop.bind(this);
        let deleteNode = row.getElementsByClassName(dragAndDropList.DELETE_ROW_CLASSNAME)[0];
        deleteNode.onclick = this.removeItemClick.bind(this, row);
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