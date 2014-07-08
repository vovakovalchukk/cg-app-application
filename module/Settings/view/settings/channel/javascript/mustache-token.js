$("#<?= $tableId ?>").on("renderColumn", function(event, cgmustache, template, column, data) {
    data.tokenClass = function() {
        if (data.expiryDate == 'N/A') {
            return "empty";
        }

        if (data.expiryDate == 'Expired') {
            return "expired";
        }

        return "";
    };
});