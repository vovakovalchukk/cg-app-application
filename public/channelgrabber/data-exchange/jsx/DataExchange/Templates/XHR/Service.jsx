import FormattingService from 'DataExchange/Templates/Formatting/Service';

const DEFAULT_SAVE_ERROR_MESSAGE = 'There was an error submitting your template. Please contact support for assistance.';
const DEFAULT_DELETE_ERROR_MESSAGE = 'There was an error deleting your template. Please contact support for assistance.';

const XHRService = {
    saveTemplate: async function saveTemplate(templateState, templateName, xhrRoute) {
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
                url: `/dataExchange/${xhrRoute}/templates/save`,
                type: 'POST',
                dataType: 'json',
                data
            });
        } catch (error) {
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
    deleteTemplate: async function deleteTemplate(templateSelectValue, xhrRoute) {
        if (!templateSelectValue) {
            return;
        }
        let response = null;
        try {
            response = await $.ajax({
                url: `/dataExchange/${xhrRoute}/templates/remove`,
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

        n.error(DEFAULT_DELETE_ERROR_MESSAGE);
        return response;
    }
};

export default XHRService;