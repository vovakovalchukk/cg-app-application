import React, {useState} from "react";

import DeleteIcon from 'zf2-v4-ui/img/icons/delete.svg';


let DeleteTemplate = function(props){
    let {className, trimmedName, templateId} = props;

    return (
        <a className={className} onClick={deleteClick}>
            <DeleteIcon/>
        </a>
    );

    function deleteClick(){
        console.log('in deleteClick', templateId);


    }
};

export default DeleteTemplate;