import React from 'react';
import Table from 'Common/Components/Table';
import styled from 'styled-components';
import allColumns from 'MessageCentre/Columns/allColumns';
import Search from 'Common/Components/Search';

const Th = styled.th`
    position: sticky;
    top: 50px;
    width: ${props => props.width || 'auto'};
`;

const MessageList = (props) => {
    return (
        <div>
            <Search
                value={props.threads.searchBy}
                onChange={props.actions.searchInputType}
                onSearch={props.actions.searchSubmit}
            />

            <Table
            actions={props.actions}
            data={props.formattedThreads}
            maxItems={100}
            pagination={1}
            onPageChange={(newPage)=>{
               // console.log('onPageChange')
            }}
            setRowValue={[]}
            columns={allColumns}
            maxPages={1}
            styledComponents={{
                Th
            }}
            />
        </div>
    );
};

export default MessageList;