define([], function() {
    const TableStorage = (function() {

        return {
            getColumns,
            getDefaultColumns: function() {
                // todo - to be completed in TAC-513
                return getColumns().map(column => {
                    if (!column.default) {
                        return;
                    }
                    return column;
                })
            },
            getTotals: function() {

            },
            getSortBy: function() {

            }
        }

        function getColumns() {
            return [
                {
                    id: 'quantity',
                    position: 1,
                    default: true,
                    headerText: 'Quantity',
                    cellPlaceholder: '2'
                }, {
                    id: 'description',
                    position: 2,
                    default: true,
                    headerText: 'Description',
                    cellPlaceholder: 'Duracell Battery 10pc'
                },
                {
                    id: 'price',
                    position: 3,
                    default: true,
                    headerText: 'Price',
                    cellPlaceholder: '£4.00'
                }
            ]
        }
    }());

    return TableStorage;
});