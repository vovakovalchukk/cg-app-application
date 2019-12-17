import React from 'react';

const LogoCell = (props) => {
    let {rowData, column} = props;
    const value = rowData[column.key] || null;
    return (<div className={`u-margin-center sprite-messages-${value}-36`}></div>);
};

export default LogoCell;