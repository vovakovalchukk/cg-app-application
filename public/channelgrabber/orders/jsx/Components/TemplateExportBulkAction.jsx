import React from 'react';
import ButtonSelectWithOptionGroups from "Common/Components/ButtonSelectWithOptionGroups";
import BulkActionService from 'Orders/js-vanilla/BulkActionService';
import dateUtility from 'Common/Utils/date';
import fileDownload from 'CommonSrc/js-vanilla/Common/Utils/xhr/fileDownload';
import progressService from 'CommonSrc/js-vanilla/Common/progressService';

const TemplateExportBulkAction = ({pdfExportOptions, pdfExportOrderBy}) => {
    return (<ButtonSelectWithOptionGroups
        optionGroups={buildOptionGroups(pdfExportOptions, pdfExportOrderBy)}
        ButtonTitle={() => (<span>Download</span>)}
        spriteClass={'sprite-download-pdf-22'}
        onButtonClick={requestTemplateExport}
        multiSelect={true}
    />);
};

export default TemplateExportBulkAction;

const baseTemplateGenerationText = 'Generating templates...';

const requestTemplateExport = async function(templateData, orders) {
    orders = orders || BulkActionService.getSelectedOrders();

    const templateIds = templateData.template;
    const groupBy = templateData.groupBy;

    if (!Array.isArray(templateIds) ||
        !Array.isArray(orders) ||
        !Array.isArray(groupBy) ||
        !templateIds.length ||
        !orders.length ||
        !groupBy.length
    ) {
        return;
    }

    const externalSuccessEventName = 'successEvent';
    const successEvent = new Event(externalSuccessEventName);

    const maxProgress = templateIds.length * orders.length;
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
                orderBy: groupBy[0],
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

const handleSuccess = (successEvent) =>{
    n.success('Templates generated successfully.');
    document.body.dispatchEvent(successEvent);
};

const buildOptionGroups = function(pdfExportOptions, pdfExportOrderBy) {
    let templateOptions = [];
    if (pdfExportOptions) {
        templateOptions = organiseOptionsByFavourite(pdfExportOptions);
        templateOptions = appendDefaultInvoiceOption(templateOptions);
    }

    let groupByOptions = [
        {
            name: 'By Order',
            id: 'order',
            selected: pdfExportOrderBy == 'order'
        },
        {
            name: 'By Template',
            id: 'template',
            selected: pdfExportOrderBy == 'template'
        }
    ];

    const templateOptionsGroup = {
        options: templateOptions,
        name: 'template',
        multiSelect: true
    };

    const groupByOptionsGroup = {
        options: groupByOptions,
        name: 'groupBy',
        multiSelect: false
    };

    return [groupByOptionsGroup, templateOptionsGroup];
};

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
