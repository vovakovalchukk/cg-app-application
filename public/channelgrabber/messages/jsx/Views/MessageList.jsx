import React from 'react';
import Table from 'Common/Components/Table';
import styled from 'styled-components';

const StyledTable = styled.table`
    width: calc(100% + 40px);
    margin-left: calc(-20px);
    margin-right: calc(-20px);
`;

const Th = styled.th`
    position: sticky;
    top: 50px;
`;

const LinkCell = (props) => {
    return (<h1>foobar</h1>);
};

const columns = [
    {
        key: 'channelLogo',
        label: 'Channel Logo',
        cell: LinkCell
    },
    {
        key: 'status',
        label: 'Status',
        cell: LinkCell
    },
    {
        key: 'subject',
        label: 'Subject',
        cell: LinkCell
    },
    {
        key: 'customerName',
        label: 'Customer Name',
        cell: LinkCell
    },
    {
        key: 'lastMessage',
        label: 'Last Message',
        cell: LinkCell
    },
    {
        key: 'dateUpdated',
        label: 'Date Updated',
        cell: LinkCell
    }
];

const pagination = [];

const setRowValue = [];

const MessageList = (props) => {
    const data = []; // props.threads.byId; ?

    return (
        <div>
            <Table
            data={data}
            pagination={pagination}
            onPageChange={(newPage)=>{
                console.log('onPageChange')
            }}
            setRowValue={setRowValue}
            columns={columns}
            maxPages={1}
            styledComponents={{
                Table: StyledTable,
                Th
            }}
            />
        </div>
    );
};

export default MessageList;
