import React from 'react';
import Table from 'Common/Components/Table';
import styled from 'styled-components';
import allColumns from 'MessageCentre/Columns/allColumns';

const StyledTable = styled.table`
    width: calc(100% + 40px);
    margin-left: calc(-20px);
    margin-right: calc(-20px);
`;

const Th = styled.th`
    position: sticky;
    top: 50px;
`;

const MessageList = (props) => {
    return (
        <div>
            <Table
                data={props.formattedThreads}
                maxItems={100}
                pagination={1}
                onPageChange={(newPage)=>{
//                    console.log('onPageChange')
                }}
                setRowValue={[]}
                columns={allColumns}
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
