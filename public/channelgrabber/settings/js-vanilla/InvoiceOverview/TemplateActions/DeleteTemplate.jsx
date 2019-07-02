import React, {useState, useContext} from "react";
import {RootContext} from 'InvoiceOverview/Root';
import service from 'InvoiceOverview/service';
import DeleteIcon from 'zf2-v4-ui/img/icons/delete.svg';

let DeleteTemplate = props => {
    let {className, templateId} = props;

    const rootContext = useContext(RootContext);

    return (
        <a className={className} onClick={deleteClick}>
            <DeleteIcon/>
        </a>
    );

    async function deleteClick(){
        let {templates} = rootContext.templatesState;
        if(!templates){
            return;
        }
        let response = await service.deleteTemplate(templateId);
        if(!response.success){
            return;
        }
        rootContext.templatesState.deleteTemplate(templateId);
    }
};

export default DeleteTemplate;