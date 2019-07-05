import React, {useState} from 'react';
import ButtonMultiSelect from 'Common/Components/ButtonMultiSelect';
import BulkActionService from 'Orders/js-vanilla/BulkActionService';

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
        console.log('response: ', response);
        
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

    async function producePDFAjaxRequest(orderIds, templateIds) {
        n.notice('creating templates...');
        debugger;

        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function(data){
            if (this.readyState == 4 && this.status == 200){
                //this.response is what you're looking for
                debugger;

                console.log(this.response, typeof this.response);
                var url = window.URL || window.webkitURL;
                var objectUrl = url.createObjectURL(this.response);

                let link  = document.createElement('a');
                link.href = objectUrl;
                link.download = `PDF-${PDF}`;
                link.click();
            }
        }
        xhr.open('POST', '/orders/pdf-export');
        xhr.responseType = 'arraybuffer';
//        xhr.responseType = 'blob';
        xhr.send({
            orderIds,
            templateIds
        });


//        return $.ajax({
//            "url": '/orders/pdf-export',
//            "data": {
//                orderIds,
//                templateIds
//            },
//            "type": "POST",
//            'dataType': 'json',
//            "success": function() {
//                n.success('Templates have been successfully created.')
//            },
//            "error": function() {
//                n.error('Templates could not be created.')
//            }
//        });
    }
};

export default TemplateExportBulkAction;