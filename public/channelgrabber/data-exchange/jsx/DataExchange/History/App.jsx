import React, {useState, useEffect} from 'react';
import historyFetch from './Service/historyFetch';
import Paginator from "Common/Components/Pagination/Paginator";
import allColumns from 'DataExchange/History/Column/allColumns';
import styled from 'styled-components';
import Skeleton from 'react-skeleton-loader';

const Table = styled.table`
    width: calc(100% + 40px);
    margin-left: calc(-20px);
    margin-right: calc(-20px);
`;

const TH = styled.th`
    position: sticky;
    top: 50px;
`;

const HistoryApp = (props) => {
    const {data, setData, setRowValue} = useDataState(null);
    const [pagination, setPagination] = useState(1);

    useEffect(() => {
        historyFetch(pagination, setData);
    }, []);

    let maxPages = 100;
    
    return (<div>
        <Table className={"u-margin-top-small"}>
            <thead>
                <tr>
                    {renderCells(data ? data[0] : {} , (Cell, column) => (
                        <TH key={`header-${column.key}`}>
                            <div>
                                {column.label}
                            </div>
                        </TH>
                    ))}
                </tr>
            </thead>
            <tbody>
                {renderRows(data, (rowData) => (
                    <tr key={rowData.id}>
                        {renderCells(rowData, (Cell, column) => (
                            <td key={`${rowData.id}-${column.key}`}>
                                <Cell
                                    rowData={rowData}
                                    column={column}
                                    setRowValue={setRowValue}
                                    // width & height props only used for Skeleton components
                                    width={`${getRandomNumber(40, 120)}px`}
                                    height={'100ox'}
                                />
                            </td>
                        ))}
                    </tr>
                ))}
            </tbody>
        </Table>
        <div className={"u-inline-block u-margin-top-large"}>
            <Paginator
                incrementPage={incrementPage}
                decrementPage={decrementPage}
                currentPage={pagination}
                maxPages={maxPages}
                displayOfTotalPages={false}
            />
        </div>
    </div>);

    function renderCells(rowData, renderCell) {
        const columns = [...allColumns];

        const isEmptyDataSet = typeof rowData === 'object' && !Object.keys(rowData).length;

        return columns.map(column => {
            if (isEmptyDataSet) {
                return renderCell(Skeleton, column);
            }

            const Cell = column.cell ? column.cell : () => {
                return <div></div>
            };

            return renderCell(Cell, column);
        })
    }

    function renderRows(data, renderRow) {
        if (!data) {
           // render single row for the Skeleton components when there is no data.
           return renderRow({});
        }

        return data.map(rowData => {
            return renderRow(rowData);
        })
    }

    function incrementPage() {
        const newPage = incrementToMax(pagination, maxPages);
        setPagination(newPage);
        historyFetch(newPage, setData);
    }

    function decrementPage() {
        const newPage = decrementTo0(pagination);
        setPagination(newPage);
        historyFetch(newPage, setData);
    }

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

function decrementTo0(number) {
    return number -1 >= 0 ? number - 1 : 0;
}

function incrementToMax(number, max) {
    return number + 1 <= max ? number + 1 : max;
}

function getRandomNumber(min, max) {
    return Math.round(Math.random() * (max - min) + min);
}