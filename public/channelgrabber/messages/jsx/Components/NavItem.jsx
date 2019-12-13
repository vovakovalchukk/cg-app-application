import React from 'react';
import { NavLink } from 'react-router-dom'

const NavItem = (props) => {
    let {
        id,
        displayText,
        filterCount,
        shouldDisplay,
        to
    } = props;

    if (!shouldDisplay) {
        return null;
    }

    return (
        <li>
            <NavLink
                to={to}
                activeStyle={{
                    color: 'green'
                }}
            >
                <span>{displayText} {filterCount}</span>
            </NavLink>
        </li>
    )
};
NavItem.defaultProps = {
    shouldDisplay: true
};

export default NavItem;