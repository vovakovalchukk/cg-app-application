$("#<?= $tableId ?>").on("renderColumn", function(event, cgmustache, template, column, data) {
    data.enabled = data.active && !data.deleted;
    data.status = function() {
        if (data.deleted) {
            return "deleted";
        }

        if (!data.active) {
            return "inactive";
        }

        if (data.expiryDate !== null && data.expiryDate <= 0) {
            return "expired";
        }

        return "active";
    };
});