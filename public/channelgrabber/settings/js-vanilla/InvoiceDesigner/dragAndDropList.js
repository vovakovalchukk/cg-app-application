define([
//    'InvoiceDesigner/PubSub/Topics'
], function(
//    Topics
) {
    const dragAndDropList = function(changeListHandler) {
        this.handleListChange = changeListHandler;
        return this;
    };

    dragAndDropList.DELETE_ROW_DELETE_CLASSNAME = 'delete-row-item';

    dragAndDropList.prototype.generateList = function(columns, targetNode, listClass) {
        this.columns = columns.slice();
        this.rowMap = new Map;
        this.sortableListNode = null;
        
        let html = `<div>
                    <h3>table columns</h3>
                    <div class="${listClass}">
                        ${this.columns.map(column => {
                            return `<div>
                                    <a title="drag">drag</a>
                                        ${column.headerText}
                                    <a title="delete" class="${dragAndDropList.DELETE_ROW_DELETE_CLASSNAME}">delete</a>
                            </div>`
                        }).join('')}
                    </div>
                </div>`;

        let fragment = document.createRange().createContextualFragment(html);
        targetNode.append(fragment);

        this.sortableListNode = document.getElementsByClassName(listClass)[0];

        [...this.sortableListNode.children].forEach((node, index) => {
           this.rowMap.set(node, this.columns[index]);
        });

        this.enableDragList();

        return fragment;
    };

    dragAndDropList.prototype.removeItemClick = function(rowNode) {
        let columnForNode = this.rowMap.get(rowNode);

        this.columns = this.columns.filter(column => column !== columnForNode)
        this.rowMap.delete(rowNode);
        rowNode.parentNode.removeChild(rowNode);

        this.handleListChange(this.columns);
    };

    dragAndDropList.prototype.handleDrop = function(item) {
        item.target.classList.remove('drag-sort-active');
        this.handleListChange(this.columns);
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
        let deleteNode = row.getElementsByClassName(dragAndDropList.DELETE_ROW_DELETE_CLASSNAME)[0];
        deleteNode.onclick = this.removeItemClick.bind(this, row);
    };

    dragAndDropList.prototype.handleDrag = function(item) {
        const selectedItem = item.target,
            list = selectedItem.parentNode,
            x = event.clientX,
            y = event.clientY;

        selectedItem.classList.add('drag-sort-active');
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

    return dragAndDropList;
});