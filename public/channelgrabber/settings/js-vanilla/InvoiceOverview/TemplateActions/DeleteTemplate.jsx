import React, {useState} from "react";
import {RootContext} from 'InvoiceOverview/RootComponent';

import DeleteIcon from 'zf2-v4-ui/img/icons/delete.svg';


let DeleteTemplate = function(props){
    let {className, trimmedName, templateId} = props;

    return (
        <RootContext.Consumer>
            {RootContext => (
                <div >sdsdg</div>
            )}
        </RootContext.Consumer>

    );
//    return (
//        <RootContext.Consumer>
//            {function(contextValue){
//                console.log('contextValue: ', contextValue);
//
//                return <div>sddsg</div>
////                return (
////                    <a className={className} onClick={deleteClick}>
////                        <DeleteIcon/>
////                    </a>
////                )
//            }}
//        </RootContext.Consumer>
//
//    );

    function deleteClick(){
        console.log('in deleteClick', templateId);


    }
};

export default DeleteTemplate;