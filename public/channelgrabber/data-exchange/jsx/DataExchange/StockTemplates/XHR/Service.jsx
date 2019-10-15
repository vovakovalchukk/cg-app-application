import FormattingService from 'DataExchange/StockTemplates/Formatting/Service';

const DEFAULT_SAVE_ERROR_MESSAGE = 'There was an error submitting your template. Please contact support for assistance.';

const XHRService = {
    saveTemplate: async function(templateState, templateName) {
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
    }
};

export default XHRService;