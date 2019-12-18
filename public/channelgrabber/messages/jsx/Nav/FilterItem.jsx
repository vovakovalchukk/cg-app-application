import React from 'react';
import { NavLink } from 'react-router-dom'

const FilterItem = (props) => {
    let {
        id,
        displayText,
        filterCount,
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
                <span>{displayText} {filterCount}</span>
            </NavLink>
        </li>
    )
};

export default FilterItem;