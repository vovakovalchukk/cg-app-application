import React, {useState, useContext} from "react";
import {RootContext} from 'InvoiceOverview/RootComponent';
import DeleteIcon from 'zf2-v4-ui/img/icons/delete.svg';

let DeleteTemplate = function(props){
    let {className, trimmedName, templateId} = props;

    const rootContext = useContext(RootContext);

    return (
        <a className={className} onClick={deleteClick}>
            <DeleteIcon/>
        </a>
    );

    function deleteClick(){
        let {templates} = rootContext.templatesState;
        if(!templates){
            return;
        }

        $.ajax({
//            "url" : '/orders/pdf-export',
            "url" : '/settings/invoice/settings/delete',
//            "url" : '/settings/invoice/settings/removeFavourite',



            "type" : "POST",
            'dataType' : 'json',
            "data" : {
                templateId: templateId
            },
            "complete" : function() {
                console.log('complete!');
                
                
            },
            "success" : function(data) {
                console.log('success !data: ', data);
                rootContext.templatesState.deleteTemplate(templateId);
            },
            "error" : function() {
                console.log('error...');
            }
        });

    }
};

export default DeleteTemplate;