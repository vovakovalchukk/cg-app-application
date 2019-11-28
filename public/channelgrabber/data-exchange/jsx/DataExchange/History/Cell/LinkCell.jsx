import React from 'react';

const LinkCell = (props) => {
    let {rowData, column} = props;

    let value = typeof column.getValue === 'function' ?
        column.getValue(rowData) : rowData[column.key];

    if (value === null || typeof value === "undefined") {
        return null;
    }

    let link = typeof column.getLink === 'function' ?
        column.getLink(rowData) : '';

    return (<div>
        {value}
        {link && <a className="u-margin-left-small" href={link}>
            link
        </a>}
    </div>);
};

export default LinkCell;