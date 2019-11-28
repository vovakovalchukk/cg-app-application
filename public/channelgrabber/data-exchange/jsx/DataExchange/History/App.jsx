import React, {useState, useCallback, useEffect} from 'react';
import historyFetch from './Service/historyFetch';
import Paginator from "Common/Components/Pagination/Paginator";
import allColumns from 'DataExchange/History/Column/allColumns';
import styled from 'styled-components';

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
//        historyFetch(pagination, setData);
//         todo - remove this hack
        setData([
            {
                "id": 37,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Stock Export",
                "startDate": "2019-11-22 14:48:52",
                "fileName": "stockExport.csv",
                "userId": 2,
                "endDate": "In Progress",
                "totalRows": 285,
                "successfulRows": 285,
                "failedRows": 0,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": "/dataExchange/history/files/37/file"
            },
            {
                "id": 36,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Stock Export",
                "startDate": "2019-11-22 14:48:25",
                "fileName": "stockExport.csv",
                "userId": 2,
                "endDate": "Ended by User",
                "totalRows": null,
                "successfulRows": null,
                "failedRows": null,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": null
            },
            {
                "id": 35,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Order Export",
                "startDate": "2019-11-22 14:45:16",
                "fileName": "orderExport.csv",
                "userId": 2,
                "endDate": "2019-11-22 14:45:19",
                "totalRows": 32,
                "successfulRows": 32,
                "failedRows": 0,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": "/dataExchange/history/files/35/file"
            },
            {
                "id": 34,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Order Export",
                "startDate": "2019-11-22 14:17:29",
                "fileName": "orderExport.csv",
                "userId": 2,
                "endDate": "2019-11-22 14:17:34",
                "totalRows": 32,
                "successfulRows": 32,
                "failedRows": 0,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": "/dataExchange/history/files/34/file"
            },
            {
                "id": 33,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Order Export",
                "startDate": "2019-11-22 14:10:53",
                "fileName": "orderExport.csv",
                "userId": 2,
                "endDate": "2019-11-22 14:10:57",
                "totalRows": 32,
                "successfulRows": 32,
                "failedRows": 0,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": "/dataExchange/history/files/33/file"
            },
            {
                "id": 32,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Order Export",
                "startDate": "2019-11-22 14:07:47",
                "fileName": "orderExport.csv",
                "userId": 2,
                "endDate": "Ended by User",
                "totalRows": null,
                "successfulRows": null,
                "failedRows": null,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": null
            },
            {
                "id": 31,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Order Export",
                "startDate": "2019-11-22 14:04:29",
                "fileName": "orderExport.csv",
                "userId": 2,
                "endDate": "2019-11-22 14:04:32",
                "totalRows": 33,
                "successfulRows": 33,
                "failedRows": 0,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": "/dataExchange/history/files/31/file"
            },
            {
                "id": 30,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Order Export",
                "startDate": "2019-11-22 14:04:06",
                "fileName": "orderExport.csv",
                "userId": 2,
                "endDate": "2019-11-22 14:04:10",
                "totalRows": 33,
                "successfulRows": 33,
                "failedRows": 0,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": "/dataExchange/history/files/30/file"
            },
            {
                "id": 29,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Order Export",
                "startDate": "2019-11-22 14:04:05",
                "fileName": "orderExport.csv",
                "userId": 2,
                "endDate": "2019-11-22 14:04:09",
                "totalRows": 33,
                "successfulRows": 33,
                "failedRows": 0,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": "/dataExchange/history/files/29/file"
            },
            {
                "id": 28,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Order Export",
                "startDate": "2019-11-22 14:02:55",
                "fileName": "orderExport.csv",
                "userId": 2,
                "endDate": "2019-11-22 14:03:04",
                "totalRows": 33,
                "successfulRows": 33,
                "failedRows": 0,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": "/dataExchange/history/files/28/file"
            },
            {
                "id": 27,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Order Export",
                "startDate": "2019-11-22 14:02:46",
                "fileName": "orderExport.csv",
                "userId": 2,
                "endDate": "2019-11-22 14:02:51",
                "totalRows": 33,
                "successfulRows": 33,
                "failedRows": 0,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": "/dataExchange/history/files/27/file"
            },
            {
                "id": 26,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Order Export",
                "startDate": "2019-11-22 13:54:01",
                "fileName": "orderExport.csv",
                "userId": 2,
                "endDate": "2019-11-22 13:54:05",
                "totalRows": 33,
                "successfulRows": 33,
                "failedRows": 0,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": "/dataExchange/history/files/26/file"
            },
            {
                "id": 25,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Order Export",
                "startDate": "2019-11-22 13:53:52",
                "fileName": "orderExport.csv",
                "userId": 2,
                "endDate": "2019-11-22 13:53:56",
                "totalRows": 33,
                "successfulRows": 33,
                "failedRows": 0,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": "/dataExchange/history/files/25/file"
            },
            {
                "id": 24,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Order Export",
                "startDate": "2019-11-22 13:53:16",
                "fileName": "orderExport.csv",
                "userId": 2,
                "endDate": "2019-11-22 13:53:20",
                "totalRows": 33,
                "successfulRows": 33,
                "failedRows": 0,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": "/dataExchange/history/files/24/file"
            },
            {
                "id": 23,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Order Export",
                "startDate": "2019-11-22 13:52:42",
                "fileName": "orderExport.csv",
                "userId": 2,
                "endDate": "2019-11-22 13:52:46",
                "totalRows": 33,
                "successfulRows": 33,
                "failedRows": 0,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": "/dataExchange/history/files/23/file"
            },
            {
                "id": 22,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Order Export",
                "startDate": "2019-11-22 13:52:29",
                "fileName": "orderExport.csv",
                "userId": 2,
                "endDate": "2019-11-22 13:52:34",
                "totalRows": 33,
                "successfulRows": 33,
                "failedRows": 0,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": "/dataExchange/history/files/22/file"
            },
            {
                "id": 21,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Stock Export",
                "startDate": "2019-11-22 13:47:18",
                "fileName": "stockExport.csv",
                "userId": 2,
                "endDate": "2019-11-22 13:47:28",
                "totalRows": 285,
                "successfulRows": 285,
                "failedRows": 0,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": "/dataExchange/history/files/21/file"
            },
            {
                "id": 20,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Stock Export",
                "startDate": "2019-11-19 11:00:51",
                "fileName": "stockExport.csv",
                "userId": 2,
                "endDate": "2019-11-19 11:01:00",
                "totalRows": 285,
                "successfulRows": 285,
                "failedRows": 0,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": "/dataExchange/history/files/20/file"
            },
            {
                "id": 19,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Stock Export",
                "startDate": "2019-11-19 10:54:12",
                "fileName": "stockExport.csv",
                "userId": 2,
                "endDate": "2019-11-19 10:54:19",
                "totalRows": 285,
                "successfulRows": 285,
                "failedRows": 0,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": "/dataExchange/history/files/19/file"
            },
            {
                "id": 18,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Stock Export",
                "startDate": "2019-11-19 10:16:11",
                "fileName": "stockExport.csv",
                "userId": 2,
                "endDate": "2019-11-19 10:16:18",
                "totalRows": 285,
                "successfulRows": 285,
                "failedRows": 0,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": "/dataExchange/history/files/18/file"
            },
            {
                "id": 17,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Stock Export",
                "startDate": "2019-11-19 10:06:35",
                "fileName": "stockExport.csv",
                "userId": 2,
                "endDate": "2019-11-19 10:06:41",
                "totalRows": 285,
                "successfulRows": 285,
                "failedRows": 0,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": "/dataExchange/history/files/17/file"
            },
            {
                "id": 16,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Stock Export",
                "startDate": "2019-11-19 10:05:40",
                "fileName": "stockExport.csv",
                "userId": 2,
                "endDate": "2019-11-19 10:05:48",
                "totalRows": 285,
                "successfulRows": 285,
                "failedRows": 0,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": "/dataExchange/history/files/16/file"
            },
            {
                "id": 15,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Stock Export",
                "startDate": "2019-11-19 09:58:12",
                "fileName": "stockExport.csv",
                "userId": 2,
                "endDate": "2019-11-19 09:58:20",
                "totalRows": 285,
                "successfulRows": 285,
                "failedRows": 0,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": "/dataExchange/history/files/15/file"
            },
            {
                "id": 14,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Stock Export",
                "startDate": "2019-11-19 09:40:33",
                "fileName": "stockExport.csv",
                "userId": 2,
                "endDate": "2019-11-19 09:40:40",
                "totalRows": 285,
                "successfulRows": 285,
                "failedRows": 0,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": "/dataExchange/history/files/14/file"
            },
            {
                "id": 13,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Stock Export",
                "startDate": "2019-11-19 09:35:48",
                "fileName": "stockExport.csv",
                "userId": 2,
                "endDate": "2019-11-19 09:35:55",
                "totalRows": 285,
                "successfulRows": 285,
                "failedRows": 0,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": "/dataExchange/history/files/13/file"
            },
            {
                "id": 12,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Stock Export",
                "startDate": "2019-11-19 09:35:20",
                "fileName": "stockExport.csv",
                "userId": 2,
                "endDate": "2019-11-19 09:35:27",
                "totalRows": 285,
                "successfulRows": 285,
                "failedRows": 0,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": "/dataExchange/history/files/12/file"
            },
            {
                "id": 11,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Stock Export",
                "startDate": "2019-11-19 09:33:58",
                "fileName": "stockExport.csv",
                "userId": 2,
                "endDate": "2019-11-19 09:34:05",
                "totalRows": 285,
                "successfulRows": 285,
                "failedRows": 0,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": "/dataExchange/history/files/11/file"
            },
            {
                "id": 10,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Stock Export",
                "startDate": "2019-11-18 14:31:38",
                "fileName": "stockExport.csv",
                "userId": 2,
                "endDate": "2019-11-18 14:31:48",
                "totalRows": 285,
                "successfulRows": 285,
                "failedRows": 0,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": "/dataExchange/history/files/10/file"
            },
            {
                "id": 9,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Stock Export",
                "startDate": "2019-11-18 14:28:54",
                "fileName": "stockExport.csv",
                "userId": 2,
                "endDate": "2019-11-18 14:29:05",
                "totalRows": 285,
                "successfulRows": 285,
                "failedRows": 0,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": "/dataExchange/history/files/9/file"
            },
            {
                "id": 8,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Stock Export",
                "startDate": "2019-11-18 14:26:59",
                "fileName": "stockExport.csv",
                "userId": 2,
                "endDate": "2019-11-18 14:27:10",
                "totalRows": 285,
                "successfulRows": 285,
                "failedRows": 0,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": "/dataExchange/history/files/8/file"
            },
            {
                "id": 7,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Stock Export",
                "startDate": "2019-11-18 14:25:28",
                "fileName": "stockExport.csv",
                "userId": 2,
                "endDate": "Ended by User",
                "totalRows": null,
                "successfulRows": null,
                "failedRows": null,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": null
            },
            {
                "id": 6,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Stock Export",
                "startDate": "2019-11-18 14:22:22",
                "fileName": "stockExport.csv",
                "userId": 2,
                "endDate": "Ended by User",
                "totalRows": null,
                "successfulRows": null,
                "failedRows": null,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": null
            },
            {
                "id": 5,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Stock Export",
                "startDate": "2019-11-18 14:05:59",
                "fileName": "stockExport.csv",
                "userId": 2,
                "endDate": "Ended by User",
                "totalRows": null,
                "successfulRows": null,
                "failedRows": null,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": null
            },
            {
                "id": 4,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Stock Export",
                "startDate": "2019-11-18 13:59:15",
                "fileName": "stockExport.csv",
                "userId": 2,
                "endDate": "Ended by User",
                "totalRows": null,
                "successfulRows": null,
                "failedRows": null,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": null
            },
            {
                "id": 3,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Stock Export",
                "startDate": "2019-11-18 12:40:34",
                "fileName": "stockExport.csv",
                "userId": 2,
                "endDate": "Ended by User",
                "totalRows": null,
                "successfulRows": null,
                "failedRows": null,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": null
            },
            {
                "id": 2,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Stock Export",
                "startDate": "2019-11-18 12:40:22",
                "fileName": "stockExport.csv",
                "userId": 2,
                "endDate": "Ended by User",
                "totalRows": null,
                "successfulRows": null,
                "failedRows": null,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": "/dataExchange/history/files/2/file"
            },
            {
                "id": 1,
                "organisationUnitId": 2,
                "scheduleId": null,
                "operation": "export",
                "type": "Stock Export",
                "startDate": "2019-11-18 12:40:12",
                "fileName": "stockExport.csv",
                "userId": 2,
                "endDate": "Ended by User",
                "totalRows": null,
                "successfulRows": null,
                "failedRows": null,
                "jobId": null,
                "user": "christian walker-spiers",
                "unprocessedLink": null,
                "successfulLink": null,
                "failedLink": null,
                "fileLink": "/dataExchange/history/files/1/file"
            }
        ])
    }, []);

//    console.log('------RENDER',data);
    let maxPages = 100;

    // todo - do this better with a spinner or something
    if (!data) {
        return null;
    }

    return (<div>
        <Table className={"u-margin-top-small"}>
            <thead>
                <tr>
                    {renderCells(data[0], (Cell, column) => (
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

        return columns.map(column => {
            const Cell = column.cell ? column.cell : () => {
                return <div></div>
            };
            return renderCell(Cell, column);
        })
    }

    function renderRows(data, renderRow) {
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