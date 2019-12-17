import React from 'react';

const HeaderCell = (props) => {
    // console.log(props);

    let {column} = props;
    return (<div onClick={sortBy}>
        {column.label}foobar
    </div>);

    function sortBy() {
        props.actions.sortBy(props.column.key)
    }
};

export default HeaderCell;