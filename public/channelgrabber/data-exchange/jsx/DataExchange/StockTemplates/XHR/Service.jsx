import FormattingService from 'DataExchange/StockTemplates/Formatting/Service';

const DEFAULT_SAVE_ERROR_MESSAGE = 'There was an error submitting your template. Please contact support for assistance.';
const DEFAULT_DELETE_ERROR_MESSAGE = 'There was an error deleting your template. Please contact support for assistance.';

const XHRService = {
    saveTemplate: async function saveTemplate(templateState, templateName) {
        let formattedTemplate = FormattingService.formatTemplateForSave(templateState.template, templateName.value);
        const data = {
            template: formattedTemplate
        };

        if (!data.template.name) {
            n.error('Please choose a name for your template.');
            return;
        }

        let response = null;
        try {
            response = await $.ajax({
                url: '/dataExchange/stock/templates/save',
                type: 'POST',
                dataType: 'json',
                data
            });
        } catch (error) {
            console.log('error: ', error);
            n.error(DEFAULT_SAVE_ERROR_MESSAGE);
        }
        if (response.success) {
            n.success("You have successfully saved your template.");
            return response;
        }
        n.error(response.message);
        if (!response.message) {
            n.error(DEFAULT_SAVE_ERROR_MESSAGE);
        }
        return response;
    },
    deleteTemplate: async function deleteTemplate(templateSelectValue) {
        if (!templateSelectValue) {
            return;
        }
        let response = null;
        try {
            response = await $.ajax({
                url: '/dataExchange/stock/templates/remove',
                type: 'POST',
                dataType: 'json',
                data: {id: templateSelectValue.id}
            });
        } catch(error) {
            n.error(DEFAULT_DELETE_ERROR_MESSAGE)
        }
        if (response.success) {
            n.success(`You have successfully deleted the template '${templateSelectValue.name}'.`);
            response.templateId = templateSelectValue.id;
            return response;
        }

        n.error(DEFAULT_DELETE_ERROR_MESSAGE)
        return response;
    }
};

export default XHRService;