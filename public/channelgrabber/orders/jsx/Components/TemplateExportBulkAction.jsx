import React, {useState} from 'react';
import ButtonMultiSelect from 'Common/Components/ButtonMultiSelect';
import OrdersBulkActionAbstract from 'Orders/js-vanilla/OrdersBulkActionAbstract';

const TemplateExportBulkAction = ({pdfExportOptions}) => {
    let OrdersBulkActions = new OrdersBulkActionAbstract();

    //todo -remove this hack
    pdfExportOptions.forEach((option,index)=>{
        if(index === 2){
            option.favourite=true;
        }
    });

    let options = prepareOptions(pdfExportOptions);

    return (<ButtonMultiSelect
        componentId={'template-export'}
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
        result.splice(0,0,{
           id: 'defaultInvoice',
           name: 'Default Invoice'
        });
        return result;
    }
};

export default TemplateExportBulkAction;