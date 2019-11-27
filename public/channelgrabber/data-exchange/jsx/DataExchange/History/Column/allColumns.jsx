import React from 'react';

const DirectKeyValue = (props) => {
    let {rowData, column} = props;

    let value = typeof column.getValue === 'function' ?
        column.getValue(rowData) : rowData[column.key];

//    console.log('value: ', value);
//    if (column.key === 'unprocessed') {
//        debugger;
//    }

    if (value === null || typeof value === "undefined") {
        return null;
    }

//    console.log(': ', );
    let link = typeof column.getLink === 'function' ?
        column.getLink(rowData) : '';

    return (<div>
        {value}
        {link && <a className="u-margin-left-small" href={link}>
            link
        </a>}
    </div>);
};

export default [
    {
        key: 'fileName',
        name: 'File Name',
        cell: DirectKeyValue
    },
    {
        key: 'type',
        label: 'Type',
        cell: DirectKeyValue
    },
    {
        key: 'user',
        label: 'User',
        cell: DirectKeyValue
    },
    {
        key: 'start',
        label: 'Start'
    },
    {
        key: 'totalRows',
        label: 'Total Rows',
        cell: DirectKeyValue
    },
    {
        key: 'unprocessed',
        label: 'Unprocessed',
        cell: DirectKeyValue,
        getLink: (data) => (data.unprocessedLink),
        getValue: (data) => (data.totalRows - data.successfulRows - data.failedRows)
    },
    {
        key: 'file',
        label: 'file',
        cell: DirectKeyValue,
        getLink: (data) => (data.file)
    },
    {
        key: 'endDate',
        label: 'Finish',
        cell: DirectKeyValue
    },
    {
        key: 'successful',
        label: 'Successful',
        cell: DirectKeyValue,
        getLink: (data) => (data.failedLink)
    },
    {
        key: 'failed',
        label: 'Failed',
        cell: DirectKeyValue,
        getLink: (data) => (data.failedLink)
    },
    {
        key: 'end',
        label: 'End'
    }
];