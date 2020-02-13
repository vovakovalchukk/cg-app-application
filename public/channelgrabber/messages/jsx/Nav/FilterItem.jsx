import React from 'react';
import { NavLink } from 'react-router-dom'

const FilterItem = (props) => {
    let {
        id,
        displayText,
        filterCount,
        to,
        className
    } = props;

    return (
        <div>
            <NavLink
                to={to}
                activeClassName="sidebar-item-selected"
            >
                <span>
                    {displayText}
                    <span className={className || 'statusCountPillBox'}>
                        {filterCount}
                    </span>
                </span>
            </NavLink>
        </div>
    )
};

export default FilterItem;