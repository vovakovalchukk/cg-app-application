

import ValueCell from 'MessageCentre/Cell/ValueCell';
import HeaderCell from 'MessageCentre/Cell/HeaderCell';
import LogoCell from 'MessageCentre/Cell/LogoCell';

import React from 'react';
import {Link} from 'react-router-dom';

const GoToMessage = (props) => {
    return (

            <Link to={`/messages/thread/${props.rowData.id}`}>
                <div>hi</div>

            </Link>

    )

};


export default [
    {
        key: 'channel',
        label: 'Channel',
        cell: LogoCell,
        width: '70px'
    },
    {
        key: 'goToMessage',
        label: 'goToMessage',
        cell: GoToMessage,
        width: '50px'
    },
    {
        key: 'status',
        label: 'Status',
        cell: ValueCell,
        width: '100px'
    },
    {
        key: 'subject',
        label: 'Subject',
        cell: ValueCell,
        width: '200px',
    },
    {
        key: 'accountName',
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
        key: 'updatedFuzzy',
        label: 'Date Updated',
        cell: ValueCell,
        headerCell: HeaderCell,
        width: '100px',
    }
];
