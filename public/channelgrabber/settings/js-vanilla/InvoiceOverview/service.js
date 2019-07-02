export default (function invoiceOverviewService() {
    return {
        deleteTemplate: async function removeTemplate(templateId) {
            n.notice('deleting template...');
            return $.ajax({
                "url": '/settings/invoice/settings/delete',
                "type": "POST",
                'dataType': 'json',
                "data": {
                    templateId: templateId
                },
                "success": function() {
                    n.success('Template has been successfully deleted.')
                },
                "error": function() {
                    n.error('Template could not be deleted.')
                }
            });

        },
        addFavourite: async function addFavourite(templateId) {
            n.notice('adding favourite...');
            return $.ajax({
                "url": '/settings/invoice/settings/addFavourite',
                "type": "POST",
                'dataType': 'json',
                "data": {
                    templateId: templateId
                },
                "success": function() {
                    n.success('Template has been successfully added as a favourite.')
                },
                "error": function() {
                    n.error('Template could not be added as a favourite.')
                }
            });
        },
        removeFavourite: async function addFavourite(templateId) {
            n.notice('removing favourite...');
            return $.ajax({
                "url": '/settings/invoice/settings/removeFavourite',
                "type": "POST",
                'dataType': 'json',
                "data": {
                    templateId: templateId
                },
                "success": function() {
                    n.success('Template has been successfully removed as a favourite.')
                },
                "error": function() {
                    n.error('Template could not be removed as a favourite.')
                }
            });
        }
    }
}())