import React, {useState} from 'react';
import ButtonMultiSelect from 'Common/Components/ButtonMultiSelect';
import BulkActionService from 'Orders/js-vanilla/BulkActionService';
import dateUtility from 'Common/Utils/date';
import fileDownload from 'CommonSrc/js-vanilla/Common/Utils/xhr/fileDownload';

const TemplateExportBulkAction = ({pdfExportOptions}) => {
    let options = [];

    if (pdfExportOptions) {
        options = organiseOptionsByFavourite(pdfExportOptions);
        options = appendDefaultInvoiceOption(options)
    }

    return (<ButtonMultiSelect
        options={options}
        buttonTitle={'Download'}
        spriteClass={'sprite-download-pdf-22'}
        onButtonClick={requestTemplateExport}
    />);

    async function requestTemplateExport(templateIds) {
        let orderIds = BulkActionService.getSelectedOrders();

        if (!Array.isArray(templateIds) ||
            !Array.isArray(orderIds) ||
            !templateIds.length ||
            !orderIds.length
        ) {
            return;
        }

        let handleError = () => {
            n.error('PDF could not be successfully downloaded.')
        };

        try {
            n.notice('creating templates...');
            let response = await fileDownload.downloadBlob({
                url: '/orders/pdf-export',
                desiredFilename: `${dateUtility.getCurrentDate()}.pdf`,
                data: {
                    orderIds,
                    templateIds
                }
            });
            if (response.status !== 200) {
                return handleError();
            }
            n.success('PDF has been successfully downloaded.');
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