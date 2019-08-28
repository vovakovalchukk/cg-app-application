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

    // todo - remove this before ticket finishes. This is to speed up dev process
    // medium sized request
//    requestTemplateExport( ["2", "3", "4", "5", "6"], ["7-1000", "7-1172", "7-1324", "7-1623", "7-1795", "7-1833", "7-400", "7-602"]);
    // larger sized request
//    requestTemplateExport( [
//        "2",
//        "3",
//        "4",
//        "5",
//        "6",
//        "7",
//        "8",
//        "9",
//        "10",
//        "11",
//        "12",
//        "default-formsPlusFPD-1_OU2",
//        "default-formsPlusFPS-15_OU2"
//    ], [
//        "7-1000",
//        "7-1172",
//        "7-1324",
//        "7-1623",
//        "7-1795",
//        "7-1833",
//        "7-400",
//        "7-602",
//        "7-826",
//        "7-1200",
//        "7-1359",
//        "7-151",
//        "7-394",
//        "7-591",
//        "7-628"
//    ]);

    return (<ButtonMultiSelect
        options={options}
        buttonTitle={'Download'}
        spriteClass={'sprite-download-pdf-22'}
        onButtonClick={requestTemplateExport}
    />);

    async function requestTemplateExport(templateIds, orders) {
        orders = orders || BulkActionService.getSelectedOrders();
        const baseMessage = 'Generating templates...';
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
            const {guid} = await progressService.callCheck(
                '/orders/pdf-export/check',
                templateIds,
                orders
            );
            n.notice(baseMessage);

            progressService.runProgressCheck({
                guid,
                url: "/orders/pdf-export/progress",
                maxProgress,
                successCallback: () => {
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
                n.success('Templates generated successfully.');
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