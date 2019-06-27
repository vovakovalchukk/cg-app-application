import React from "react";
import styled from 'styled-components';

const ActionIconUrlMap = {
    'favourite': '/cg-built/zf2-v4-ui/img/icons/star.svg'
};

const FavouriteIcon = styled.div`
  -webkit-mask: url('/cg-built/zf2-v4-ui/img/icons/star.svg') no-repeat 100% 100%;
  mask: url('/cg-built/zf2-v4-ui/img/icons/star.svg') no-repeat 100% 100%;
  -webkit-mask-size: cover;
  mask-size: cover;
  background-color: yellow;
`;


const Actions = props => {
    let {actions} = props;
    if (!actions) {
        return null;
    }

    let result = [];

    for(let actionKey in actions){
        let action = actions[actionKey];

        let linkProps = getLinkPropsForAction(action);



        result.push(<a {...linkProps}>{action.name}
                <FavouriteIcon
                    className={'invoice-template-action-link ' + action.name.toLowerCase()}
                />
            </a>
        )
//        result.push(<a {...linkProps}>{action.name}
//                <div className={'invoice-template-action-link ' + action.name.toLowerCase()}></div>
//            </a>
//        )
    }

    return result;

    function getLinkPropsForAction(action){
        let linkProps = {};

        let getLinkPropsMap = {
            'favourite': getLinkPropsForFavourite,
            'deleteTemplate': getLinkPropsForDelete
        };

        //todo do something different for favourite
        if(typeof getLinkPropsMap[action.name] == 'function'){
            return getLinkPropsMap[action.name]();
        }

        linkProps['href'] = action.linkHref;
        return linkProps;
    }

    function getLinkPropsForFavourite(){
        let linkProps = {};
        linkProps.onClick = function(){
            console.log('on favourite click');
        };
        return linkProps;
    }

    function getLinkPropsForDelete(){

    }
};

export default Actions;