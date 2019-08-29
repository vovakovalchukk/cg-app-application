import React, {useState} from 'react';
import ButtonMultiSelect from 'Common/Components/ButtonMultiSelect';
import BulkActionService from 'Orders/js-vanilla/BulkActionService';
import dateUtility from 'Common/Utils/date';
import fileDownload from 'CommonSrc/js-vanilla/Common/Utils/xhr/fileDownload';
import progressService from 'CommonSrc/js-vanilla/Common/progressService';

const TemplateExportBulkAction = ({pdfExportOptions}) => {
    let options = [];

    if (pdfExportOptions) {
        options = organiseOptionsByFavourite(pdfExportOptions);
        options = appendDefaultInvoiceOption(options)
    }

    const baseMessage = 'Generating templates...';

    return (<ButtonMultiSelect
        options={options}
        buttonTitle={'Download'}
        spriteClass={'sprite-download-pdf-22'}
        onButtonClick={requestTemplateExport}
    />);

    async function requestTemplateExport(templateIds, orders) {
        orders = orders || BulkActionService.getSelectedOrders();
        const maxProgress = templateIds.length * orders.length;

        if (!Array.isArray(templateIds) ||
            !Array.isArray(orders) ||
            !templateIds.length ||
            !orders.length
        ) {
            return;
        }

        const handleError = () => {
            n.error('Templates could not be generated successfully.')
        };
        try {
            n.notice(baseMessage);

            const {guid} = await progressService.callCheck(
                '/orders/pdf-export/check',
                templateIds,
                orders
            );

            progressService.runProgressCheck({
                guid,
                url: "/orders/pdf-export/progress",
                maxProgress,
                successCallback: () => {
                    n.success('Templates generated successfully.');
                },
                progressCallback: progressCount => {
                    n.notice(`${baseMessage} ${progressCount} out of ${maxProgress} complete.`, false, false, false);
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
            });
        } catch (err) {
            handleError();
        }
    }

    function organiseOptionsByFavourite(options){
        return options.sort((a, b) => {
            return b.favourite - a.favourite;
        });
    }

    function appendDefaultInvoiceOption(options){
        options.splice(0, 0, {
            id: 'defaultInvoice',
            name: 'Default Invoice'
        });
        return options;
    }
};

export default TemplateExportBulkAction;