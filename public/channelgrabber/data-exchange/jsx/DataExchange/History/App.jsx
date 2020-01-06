import React, {useState, useEffect} from 'react';
import historyFetch from './Service/historyFetch';
import allColumns from 'DataExchange/History/Column/allColumns';
import styled from 'styled-components';
import Table from 'Common/Components/Table';

const StyledTable = styled.table`
    width: calc(100% + 40px);
    margin-left: calc(-20px);
    margin-right: calc(-20px);
`;

const Th = styled.th`
    position: sticky;
    top: 50px;
`;

const HistoryApp = (props) => {
    const {data, setData, setRowValue} = useDataState(null);
    const [pagination, setPagination] = useState(1);

    useEffect(() => {
        historyFetch(pagination, setData);
    }, []);

    return <Table
        data={data}
        pagination={pagination}
        onPageChange={(newPage)=>{
            setPagination(newPage);
            historyFetch(newPage, setData);
        }}
        setRowValue={setRowValue}
        columns={allColumns}
        maxPages={100}
        styledComponents={{
            Table: StyledTable,
            Th
        }}
    />;

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

export default HistoryApp;