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
                activeClassName="sidebar-item-selected"
            >
                <span>{displayText}</span>
            </NavLink>
        </div>
    )
};

export default Item;