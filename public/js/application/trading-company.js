$(function() {
    $("#tradingCompaniesDetails").on("click", ".action .delete", function(event) {
        event.preventDefault();

        var self = $(this);

        $.ajax({
            "url": self.attr("href"),
            "dataType": "json",
            "success": function(data) {
                if (!data.deleted) {
                    alert(data.exception);
                    return;
                }

                self.closest("div.entity-row").remove();
            }
        });
    });
});