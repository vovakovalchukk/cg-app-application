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
        rootContext.templatesState.deleteTemplate(templateId);
    }
};

export default DeleteTemplate;