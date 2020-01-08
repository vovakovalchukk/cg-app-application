import React from 'react';
import ButtonMultiSelect from 'Common/Components/ButtonMultiSelect';
import BulkActionService from 'Orders/js-vanilla/BulkActionService';
import dateUtility from 'Common/Utils/date';
import fileDownload from 'CommonSrc/js-vanilla/Common/Utils/xhr/fileDownload';
import progressService from 'CommonSrc/js-vanilla/Common/progressService';

const organiseOptionsByFavourite = function(options) {
    return options.sort((a, b) => {
        return b.favourite - a.favourite;
    });
};

const appendDefaultInvoiceOption = function(options) {
    options.splice(0, 0, {
        id: 'defaultInvoice',
        name: 'Default Invoice'
    });
    return options;
};

const handleSuccess = (successEvent) =>{
    n.success('Templates generated successfully.');
    document.body.dispatchEvent(successEvent);
};

const baseTemplateGenerationText = 'Generating templates...';

const TemplateExportBulkAction = ({pdfExportOptions}) => {
    let options = [];

    if (pdfExportOptions) {
        options = organiseOptionsByFavourite(pdfExportOptions);
        options = appendDefaultInvoiceOption(options)
    }

    const requestTemplateExport = async function(templateIds, orders) {
        orders = orders || BulkActionService.getSelectedOrders();
        const maxProgress = templateIds.length * orders.length;

        if (!Array.isArray(templateIds) ||
            !Array.isArray(orders) ||
            !templateIds.length ||
            !orders.length
        ) {
            return;
        }

        const externalSuccessEventName = 'successEvent';
        const successEvent = new Event(externalSuccessEventName);

        const handleError = () => {
            n.error('Templates could not be generated successfully.')
        };
        try {
            n.notice(baseTemplateGenerationText);

            const {guid} = await progressService.callCheck(
                '/orders/pdf-export/check',
                templateIds,
                orders
            );

            progressService.runProgressCheck({
                guid,
                url: "/orders/pdf-export/progress",
                maxProgress,
                externalSuccessEventName,
                successCallback: handleSuccess,
                progressCallback: progressCount => {
                    n.notice(`${baseTemplateGenerationText} ${progressCount} out of ${maxProgress} complete.`, false, false, false);
                }
            });

            fileDownload.downloadBlob({
                url: '/orders/pdf-export',
                desiredFilename: `${dateUtility.getCurrentDate()}.pdf`,
                data: {
                    orders,
                    templateIds,
                    invoiceProgressKey: guid
                }
            }).then(response => {
                if (response.status !== 200) {
                    return handleError();
                }
                handleSuccess(successEvent);
            });
        } catch (err) {
            handleError();
        }
    };

    return (<ButtonMultiSelect
        options={options}
        ButtonTitle={() => (<span>Download</span>)}
        spriteClass={'sprite-download-pdf-22'}
        onButtonClick={requestTemplateExport}
        multiSelect={true}
    />);
};

export default TemplateExportBulkAction;