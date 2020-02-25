import React from 'react';

const HeaderCell = (props) => {
    let {column} = props;

    return (<div onClick={sortBy}>
        {column.label}
    </div>);

    function sortBy() {
        props.actions.sortBy(props.column.key)
    }
};

export default HeaderCell;