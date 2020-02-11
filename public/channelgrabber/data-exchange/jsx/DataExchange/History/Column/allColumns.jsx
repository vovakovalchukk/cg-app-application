import LinkCell from 'DataExchange/History/Cell/LinkCell';
import StopCell from 'DataExchange/History/Cell/StopCell';
import StatusCell from "DataExchange/History/Cell/StatusCell";

export default [
    {
        key: 'fileName',
        label: 'File Name',
        cell: LinkCell
    },
    {
        key: 'type',
        label: 'Type',
        cell: LinkCell
    },
    {
        key: 'user',
        label: 'User',
        cell: LinkCell
    },
    {
        key: 'startDate',
        label: 'Start',
        cell: LinkCell
    },
    {
        key: 'endDate',
        label: 'Finish',
        cell: LinkCell
    },
    {
        key: 'status',
        label: 'Status',
        cell: StatusCell
    },
    {
        key: 'totalRows',
        label: 'Total Rows',
        cell: LinkCell
    },
    {
        key: 'unprocessed',
        label: 'Unprocessed',
        cell: LinkCell,
        getLink: (data) => (data.unprocessedLink),
        getValue: (data) => (data.totalRows - data.successfulRows - data.failedRows)
    },
    {
        key: 'file',
        label: 'File',
        cell: LinkCell,
        getLink: (data) => (data.fileLink)
    },
    {
        key: 'successfulRows',
        label: 'Successful',
        cell: LinkCell,
        getLink: (data) => (data.failedLink)
    },
    {
        key: 'failedRows',
        label: 'Failed',
        cell: LinkCell,
        getLink: (data) => (data.failedLink)
    },
    {
        key: 'end',
        label: 'End',
        cell: StopCell
    }
];