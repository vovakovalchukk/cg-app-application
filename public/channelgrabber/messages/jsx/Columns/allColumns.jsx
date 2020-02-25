import React from 'react';
import ValueCell from 'MessageCentre/Cell/ValueCell';
import HeaderCell from 'MessageCentre/Cell/HeaderCell';
import LogoCell from 'MessageCentre/Cell/LogoCell';

export default [
    {
        key: 'channel',
        label: 'Channel',
        cell: LogoCell,
        width: '70px'
    },
    {
        key: 'status',
        label: 'Status',
        cell: ValueCell,
        width: '100px',
    },
    {
        key: 'subject',
        label: 'Subject',
        cell: ValueCell,
        width: '200px',
    },
    {
        key: 'externalUsername',
        label: 'Customer Name',
        cell: ValueCell,
        width: '150px',
    },
    {
        key: 'lastMessage',
        label: 'Last Message',
        cell: ValueCell,
    },
    {
        key: 'assignedUserName',
        label: 'Assigned User',
        cell: ValueCell,
        width: '200px',
    },
    {
        key: 'createdFuzzy',
        label: 'Date Created',
        cell: ValueCell,
        headerCell: HeaderCell,
        width: '100px',
    },
    {
        key: 'updatedFuzzy',
        label: 'Date Updated',
        cell: ValueCell,
        headerCell: HeaderCell,
        width: '100px',
    }
];
