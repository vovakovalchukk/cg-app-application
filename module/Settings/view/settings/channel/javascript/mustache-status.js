$("#<?= $tableId ?>").on("renderColumn", function(event, cgmustache, template, column, data) {
    data.enabled = data.active && !data.deleted;
    data.status = function() {
        if (data.deleted) {
            return "deleted";
        }

        if (!data.active) {
            return "inactive";
        }

        return "active";
    };
});