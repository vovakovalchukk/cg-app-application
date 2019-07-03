import React, {useState} from 'react';
import ButtonSelect from 'Common/Components/ButtonSelect';
import OrdersBulkActionAbstract from 'Orders/js-vanilla/OrdersBulkActionAbstract';

const TemplateExportBulkAction = ({pdfExportOptions}) => {
    let OrdersBulkActions = new OrdersBulkActionAbstract();

    //todo -remove this hack
    pdfExportOptions.forEach((option,index)=>{
        if(index === 2){
            option.favourite=true;
        }
    });
    console.log('pdfExportOptions (post hack): ', JSON.stringify(pdfExportOptions,null,1));


    let options = prepareOptions(pdfExportOptions);

    console.log('options (after prepared): ', JSON.stringify(options,null,1));


    return (<ButtonSelect
        options={options}
        buttonTitle={'Template Export'}
        spriteClass={'sprite-invoice-22-black'}
        onButtonClick={requestTemplateExport}
    />);

    function requestTemplateExport(){
        console.log('in requestTemplateExport');
        let orderIds = OrdersBulkActions.getOrders();
        console.log('orderIds: ', orderIds);
    }

    function prepareOptions(pdfExportOptions){
        let result = pdfExportOptions.sort((a,b) => {
           return b.favourite - a.favourite;
        });

        return result;
    }
};

export default TemplateExportBulkAction;