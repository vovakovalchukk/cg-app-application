import React from 'react';
import { NavLink } from 'react-router-dom'

const Item = (props) => {
    let {
        displayText,
        to
    } = props;

    return (
        <div>
            <NavLink
                to={to}
                activeStyle={{
                    color: 'green'
                }}
            >
                <span>{displayText}</span>
            </NavLink>
        </div>
    )
};

export default Item;