import React from 'react';

import EditIcon from 'zf2-v4-ui/img/icons/edit.svg';
import CreateIcon from 'zf2-v4-ui/img/icons/plus.svg';
import DuplicateIcon from 'zf2-v4-ui/img/icons/copy.svg';
import BuyLabelIcon from 'zf2-v4-ui/img/icons/shopping-cart.svg';

const IconActionMap = {
    'edit': EditIcon,
    'create': CreateIcon,
    'duplicate': DuplicateIcon,
    'buy': BuyLabelIcon
};

let LinkAction = props => {
    console.log('in link action');

    let {className, href, trimmedName} = props;

    let Icon = IconActionMap[trimmedName];

    
    return (
        <a className={className} href={href}>
            <Icon
                className={`template-overview-${trimmedName}-icon ${className}`}
            />
        </a>
    )
};

export default LinkAction;