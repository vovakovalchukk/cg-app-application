import ValueCell from 'MessageCentre/Cell/ValueCell';
import HeaderCell from 'MessageCentre/Cell/HeaderCell';

export default [
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
        cell: ValueCell,
        headerCell: HeaderCell
    }
];
