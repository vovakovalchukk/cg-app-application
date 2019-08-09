import React, {useState} from 'react';
import ButtonMultiSelect from 'Common/Components/ButtonMultiSelect';
import BulkActionService from 'Orders/js-vanilla/BulkActionService';
import dateUtility from 'Common/Utils/date';
import fileDownload from 'CommonSrc/js-vanilla/Common/Utils/xhr/fileDownload';

//todo - consider extracting all this logic to a service
async function getGUIDFromCheck() {
    const data = await $.ajax({
        url: '/orders/picklist/check',
        type: "POST",
        dataType: 'json'
    });
    return data.guid;
}
//
//todo - need to put this in a map otherwise we are going to see issues
let timeout = null;
async function initialiseProgressCheck(guid) {
    timeout = setInterval(async function() {
        console.log('in interval');
        let data = null;
        try {
            data = await $.ajax({
                context: self,
                url: "orders/picklist/progress",
                type: "POST",
                data: {
                    invoiceProgressKey: guid
                },
                dataType: 'json'
            });
        } catch (error) {
            clearTimeout(timeout);
            n.ajaxError(error);

        }
        if (!data.hasOwnProperty('progressCount')) {
            return;
        }
        if (data.progressCount === null
            || data.progressCount === 100
//            || data.progressCount == this.getRecordCountForProgress()
        ) {
            debugger;
            clearTimeout(timeout);
            n.success("end message", true);
            return;
        }
//                        var fadeOut = false;
//                        var message = this.getCountMessage().replace('##count##', data.progressCount);
//                        if (this.getRecordCountForProgress() !== null) {
//                            message += ' of ' + this.getRecordCountForProgress();
//                        }
//                        this.getNotificationHandler().notice(message, fadeOut);
//                    })
//                    .fail(function(error, textStatus, errorThrown)
//                    {
//                        clearTimeout(this.notifyTimeoutHandle);
//                        return this.getNotificationHandler().ajaxError(error, textStatus, errorThrown);
//                    });
        },
        //todo - get an appropriate polling time
        2000
    );
}
//todo- consider intilising the progress check only if you are requested > x amount of templates

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

    //todo - HACK - remove orders Ids
    async function requestTemplateExport(templateIds) {
        console.log('in render');
        let orders = BulkActionService.getSelectedOrders();
        //todo - HACK - remove
//        templateIds = [
//            "2",
//            "3"
//        ];
//        orders = [
//            "7-1000",
//            "7-1172"
//        ];
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
            const guid = await getGUIDFromCheck();
            console.log('guid: ', guid);
//            let data = {};
//            data[this.getProgressKeyName()] = progressKey;
            n.notice('Generating documents...');
            let response = fileDownload.downloadBlob({
                url: '/orders/pdf-export',
                desiredFilename: `${dateUtility.getCurrentDate()}.pdf`,
                data: {
                    orders,
                    templateIds,
                    invoiceProgressKey: guid
                }
            }).then((response) => {
                if (response.status !== 200) {
                    debugger;

                    return handleError();
                }
            });
            initialiseProgressCheck(guid);
            n.success('Documents generated successfully.');
        } catch (err) {
            handleError();
        }
    }

    function organiseOptionsByFavourite(options) {
        return options.sort((a, b) => {
            return b.favourite - a.favourite;
        });
    }

    function appendDefaultInvoiceOption(options) {
        options.splice(0, 0, {
            id: 'defaultInvoice',
            name: 'Default Invoice'
        });
        return options;
    }
};

export default TemplateExportBulkAction;