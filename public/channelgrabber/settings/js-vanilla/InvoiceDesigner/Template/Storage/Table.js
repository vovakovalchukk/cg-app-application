define([], function() {
    const allColumns = [
        {
            "id": "quantityOrdered",
            default: true,
            "cellPlaceholder": "2",
            displayText: "QTY",
            "optionText": "Quantity Ordered"
        },
        {
            "id": "skuOrdered",
            default: true,
            displayText: 'Item #',
            "optionText": "Sku Ordered",
            "cellPlaceholder": "BATTERY10pc"
        },
        {
            "id": "unitPriceIncVAT",
            default: true,
            "displayText": "Price",
            "optionText": "Unit Price inc VAT",
            "cellPlaceholder": "£6"
        },
        {
            "id": "descriptionChannel",
            default: true,
            displayText: "Description",
            "optionText": "Description-Channel",
            "cellPlaceholder": "10 x Duracell 1.5V Alkaline Batteries Long Life"
        },

        {
            "id": "lineTotalIncVAT",
            default: true,
            displayText: "Total",
            "optionText": "Line Total inc VAT",
            "cellPlaceholder": "£12"
        },
        {
            "id": "descriptionInternal",
            "optionText": "Description-Internal",
            "cellPlaceholder": "10 Duracell Batteries"
        },
        {
            "id": "unitPriceExVAT",
            "optionText": "Unit Price ex VAT",
            "cellPlaceholder": "£5"
        },
        {
            "id": "lineTotalExVAT",
            "optionText": "Line Total ex VAT",
            "cellPlaceholder": "£10"
        },
        {
            "id": "vatRateApplied",
            "optionText": "VAT Rate Applied",
            "cellPlaceholder": "20%"
        },
        {
            "id": "discount",
            "optionText": "Discount",
            "cellPlaceholder": "£0"
        },
        {
            "id": "pickingLocation",
            "optionText": "Picking Location",
            "cellPlaceholder": "Warehouse 1, Aisle 1, Shelf 1"
        }
    ];
    const allTableTotals = [
        {
            total: 'postageAndPackagingCost',
            displayText: 'Postage & Packing',
            position: 0,
            placeholder: '£4.00',
            default: true
        },
        {
            total: 'totalExVat',
            displayText: 'Subtotal',
            position: 1,
            placeholder: '£4.00',
            default: true
        },
        {
            total: 'totalVat',
            displayText: 'VAT',
            position: 2,
            placeholder: '£0.80',
            default: true
        },
        {
            total: 'total',
            displayText: 'Total',
            position: 3,
            placeholder: '£4.80',
            default: true
        }
    ];

    const TableStorage = (function() {
        return {
            getColumns,
            getTableTotals,
            getDefaultSortBy: function() {
                const defaultSortBy = getColumns().filter(column => {
                    return column.id === "descriptionInternal"
                });
                defaultSortBy[0].position = 0;
                return defaultSortBy;
            },
            getDefaultTableTotals,
            getDefaultColumns
        };

        function getColumns() {
            return allColumns
        }

        function getTableTotals() {
            return allTableTotals;
        }

        function getDefaultTableTotals() {
            return getTableTotals().filter(total => {
                return total.default;
            });
        }

        function getDefaultColumns() {
            return getColumns().filter(column => {
                column.widthMeasurementUnit = 'mm';
                return column.default;
            });
        }
    }());

    return TableStorage;
});