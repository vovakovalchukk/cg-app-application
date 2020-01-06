import React from 'react';
import { NavLink } from 'react-router-dom'

const Item = (props) => {
    let {
        displayText,
        to
    } = props;

    return (
        <li>
            <NavLink
                to={to}
                activeStyle={{
                    color: 'green'
                }}
            >
                <span>{displayText}</span>
            </NavLink>
        </li>
    )
};

export default Item;