import React from 'react';

const ValueCell = (props) => {
    let {rowData, column} = props;
    const value = rowData[column.key] || null;
    return (<p>{value}</p>);
};

export default ValueCell;