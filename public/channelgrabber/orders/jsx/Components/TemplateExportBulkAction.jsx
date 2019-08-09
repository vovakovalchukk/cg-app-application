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

    //todo - remove this - just for speeding up testing process
    requestTemplateExport([
        "2",
        "3"
    ],[
        "7-1000",
        "7-1172"
    ]);

    return (<ButtonMultiSelect
        options={options}
        buttonTitle={'Download'}
        spriteClass={'sprite-download-pdf-22'}
        onButtonClick={requestTemplateExport}
    />);

    //todo - HACK - remove orders Ids
    async function requestTemplateExport(templateIds, orderIds) {
        let orders = orderIds || BulkActionService.getSelectedOrders();

        //todo - HACK - remove
        templateIds = [
            "2",
            "3"
        ];
        orders = [
            "7-1000",
            "7-1172"
        ];

        if (!Array.isArray(templateIds) ||
            !Array.isArray(orders) ||
            !templateIds.length ||
            !orders.length
        ) {
            return;
        }

        let handleError = () => {
            n.error('Documents could not be generated successfully.')
        };

        try {
            n.notice('Generating documents...');
            let response = await fileDownload.downloadBlob({
                url: '/orders/pdf-export',
                desiredFilename: `${dateUtility.getCurrentDate()}.pdf`,
                data: {
                    orders,
                    templateIds
                }
            });
            if (response.status !== 200) {
                return handleError();
            }
            n.success('Documents generated successfully.');
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