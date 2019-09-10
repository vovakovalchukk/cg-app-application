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

    const TableStorage = (function() {
        return {
            getColumns,
            getDefaultColumns: function() {
                return getColumns().map(column => {
                    if (!column.default) {
                        return;
                    }
                    return column;
                })
            },
            getTotals: function() {},
            getSortBy: function() {}
        };

        function getColumns() {
            return allColumns
        }
    }());

    return TableStorage;
});