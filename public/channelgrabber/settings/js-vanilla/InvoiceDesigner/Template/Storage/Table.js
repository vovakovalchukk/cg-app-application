define([
    'Common/Common/Utils/generic'
], function(
    genericUtility
) {
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
            "displayText": "Total",
            "optionText": "Line Total inc VAT",
            "cellPlaceholder": "£12"
        },
        {
            "id": "descriptionInternal",
            "displayText": "Description Internal",
            "optionText": "Description-Internal",
            "cellPlaceholder": "10 Duracell Batteries"
        },
        {
            "id": "unitPriceExVAT",
            "displayText": "Unit Price ex VAT",
            "optionText": "Unit Price ex VAT",
            "cellPlaceholder": "£5"
        },
        {
            "id": "lineTotalExVAT",
            "displayText": "Line Total ex VAT",
            "optionText": "Line Total ex VAT",
            "cellPlaceholder": "£10"
        },
        {
            "id": "vatRateApplied",
            "displayText": "VAT Rate Applied",
            "optionText": "VAT Rate Applied",
            "cellPlaceholder": "20%"
        },
        {
            "id": "discount",
            "displayText": "Discount",
            "optionText": "Discount",
            "cellPlaceholder": "£0"
        },
        {
            "id": "pickingLocation",
            "displayText": "Picking Location",
            "optionText": "Picking Location",
            "cellPlaceholder": "Warehouse 1, Aisle 1, Shelf 1"
        },
        {
            "id": "image",
            "displayText": "Image",
            "optionText": "Image",
            "cellPlaceholder": `<i> image </i>`
        }
    ];
    const allTableTotals = [
        {
            id: 'postageAndPackagingCost',
            displayText: 'Shipping Total',
            position: 0,
            placeholder: '£4.00',
            optionText: "Shipping Total",
            default: true
        },
        {
            id: 'totalExVat',
            displayText: 'Subtotal',
            position: 1,
            placeholder: '£4.00',
            optionText: "Total ex VAT",
            default: true
        },
        {
            id: 'totalVat',
            displayText: 'VAT',
            position: 2,
            placeholder: '£0.80',
            optionText: "Total VAT",
            default: true
        },
        {
            id: 'total',
            displayText: 'Total',
            position: 3,
            placeholder: '£4.80',
            optionText: "Total",
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
                return genericUtility.deepClone(defaultSortBy);
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
            return genericUtility.deepClone(getTableTotals().filter(total => {
                return total.default;
            }));
        }

        function getDefaultColumns() {
            return genericUtility.deepClone(getColumns().filter(column => {
                column.widthMeasurementUnit = 'mm';
                return column.default;
            }));
        }
    }());

    return TableStorage;
});