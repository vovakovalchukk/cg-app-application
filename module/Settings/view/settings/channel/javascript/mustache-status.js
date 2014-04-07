$("#<?= $tableId ?>").on("renderColumn", function(event, cgmustache, template, column, data) {
    data.enabled = data.active && !data.deleted;
    data.status = function() {
        if (data.deleted) {
            return "Deleted";
        }

        if (!data.active) {
            return "Inactive";
        }

        return "Active";
    };
});