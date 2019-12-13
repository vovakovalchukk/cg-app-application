import React, {usestate} from 'react';
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

const ValueCell = (props) => {
    let {rowData, column} = props;
    const value = rowData[column.key] || null;
    return (<p>{value}</p>);
};

const columns = [
    {
        key: 'channel',
        label: 'Channel Logo',
        cell: ValueCell
    },
    {
        key: 'status',
        label: 'Status',
        cell: ValueCell
    },
    {
        key: 'subject',
        label: 'Subject',
        cell: ValueCell
    },
    {
        key: 'accountName',
        label: 'Customer Name',
        cell: ValueCell
    },
    {
        key: 'lastMessage',
        label: 'Last Message',
        cell: ValueCell
    },
    {
        key: 'updatedFuzzy',
        label: 'Date Updated',
        cell: ValueCell
    }
];

const MessageList = (props) => {
    console.log(props);
    return (
        <div>
            <Table
            data={props.formattedThreads}
            pagination={1}
            onPageChange={(newPage)=>{
                console.log('onPageChange')
            }}
            setRowValue={[]}
            columns={columns}
            maxPages={1}
            styledComponents={{
                Table: StyledTable,
                Th
            }}
            />
        </div>
    );

    function useDataState(initialValue) {
        let [data, setData] = useState(initialValue);
        function setRowValue(rowId, key, value) {
            let newData = [...data];
            let rowIndex = newData.findIndex(data => (data.id === rowId));
                newData[rowIndex][key] = value;
                setData(newData);
            }
            return {
                data,
                setData,
                setRowValue
            }
        }
};

export default MessageList;
