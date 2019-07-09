import React, {useState} from 'react';
import ButtonMultiSelect from 'Common/Components/ButtonMultiSelect';
import BulkActionService from 'Orders/js-vanilla/BulkActionService';
import dateUtility from 'Common/Utils/date';

const TemplateExportBulkAction = ({pdfExportOptions}) => {
    pdfExportOptions.forEach((option, index) => {
        if (index === 2) {
            option.favourite = true;
        }
    });

    let options = prepareOptions(pdfExportOptions);
    
    return (<ButtonMultiSelect
        options={options}
        buttonTitle={'Template Export'}
        spriteClass={'sprite-invoice-22-black'}
        onButtonClick={requestTemplateExport}
    />);

    async function requestTemplateExport(optionIds) {
        let orderIds = BulkActionService.getSelectedOrders();
        console.log('in requestTemplateExport', {optionIds, orderIds});

        if (!Array.isArray(optionIds) ||
            !Array.isArray(orderIds) ||
            !optionIds.length ||
            !orderIds.length
        ){
            return;
        }

        let response = await producePDFAjaxRequest(orderIds, optionIds);
        //todo - do something useful here
    }

    function prepareOptions(pdfExportOptions) {
        let result = pdfExportOptions.sort((a, b) => {
            return b.favourite - a.favourite;
        });
        result.splice(0, 0, {
            id: 'defaultInvoice',
            name: 'Default Invoice'
        });
        return result;
    }

    // todo - move this PDF request stuff into UTILS
    async function producePDFAjaxRequest(orderIds, templateIds) {
        n.notice('creating templates...');

        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function(data){
            if (this.readyState == 4 && this.status == 200){
                var url = window.URL || window.webkitURL;
                var objectUrl = url.createObjectURL(this.response);

                let link  = document.createElement('a');
                link.href = objectUrl;
                let formattedDate = `${dateUtility.getCurrentDate()}.pdf`;
                link.download = `${formattedDate}.pdf`;
                link.click();
                n.success('PDF has been successfully downloaded.');
            }
        };
        xhr.open('POST', '/orders/pdf-export');
        xhr.responseType = 'blob';
        xhr.send({
            orderIds,
            templateIds
        });
    }
};

export default TemplateExportBulkAction;