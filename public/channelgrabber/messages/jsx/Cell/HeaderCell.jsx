import React from 'react';

const HeaderCell = (props) => {
    let {column} = props;
    return (<div>
        {column.label}
    </div>);
};

export default HeaderCell;