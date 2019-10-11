import React, {useReducer} from 'react';
import styled from "styled-components";
import scheduleReducer from "../ScheduleReducer";

const Container = styled.div`
    margin-top: 45px;
`;
const TypeCellContainer = styled.td`
    overflow: visible;
`;
const TableHeader = styled.th`
    width: ${props => props.width ? props.width : 'auto'};
`;
const Input = styled.input`
    width: auto;
    max-width: 70%;
`;

const Table = (props) => {
    const initialSchedules = [...props.stockExportSchedules, buildEmptySchedule()];
    const [schedules, dispatch] = useReducer(scheduleReducer, initialSchedules);

    const renderTableHeader = () => {
        return <tr>
            <TableHeader width={'80px'}>Enabled</TableHeader>
            <TableHeader>Rule name</TableHeader>
            <TableHeader>Template</TableHeader>
            <TableHeader>Send to</TableHeader>
            <TableHeader>Send from</TableHeader>
            <TableHeader>Email Subject</TableHeader>
            <TableHeader>File name</TableHeader>
            <TableHeader>Frequency</TableHeader>
            <TableHeader>When</TableHeader>
            <TableHeader width={'80px'}>Actions</TableHeader>
        </tr>;
    };

    const renderRows = () => {
        return schedules.map((schedule, index) => {
            return <tr>
                <td>{schedule.active ? 'Active' : 'Not active'}</td>
                <td>{renderInputColumnForType(schedule, index, 'name')}</td>
                <td>{schedule.templateId}</td>
                <td>{schedule.toDataExchangeAccountId}</td>
                <td>{schedule.fromDataExchangeAccountType}</td>
                <td>Subject</td>
                <td>{renderInputColumnForType(schedule, index, 'filename')}</td>
                <td>{schedule.frequency}</td>
                <td>When</td>
                <td>Actions</td>
            </tr>;
        });
    };

    const renderInputColumnForType = (schedule, index, property, type = 'text') => {
        return <Input
            index={index}
            property={property}
            type={type}
            value={schedule[property] || ''}
            onChange={(event) => {handleInputValueChanged(index, property, event.target.value)}}
        />;
    };

    const handleInputValueChanged = (index, property, newValue) => {
        if (isLastEntry(index)) {
            dispatch({
                type: 'addNewSchedule',
                payload: {
                    schedule: buildEmptySchedule()
                }
            });
        }

        dispatch({
            type: 'updateInputValue',
            payload: {
                index,
                property,
                newValue
            }
        });
    };

    const isLastEntry = (index) => {
        return schedules.length - 1 === index;
    };

    return <Container>
        <form name={'stockExportSchedule'}>
            <table>
                <thead>{renderTableHeader()}</thead>
                <tbody>{renderRows(schedules, dispatch)}</tbody>
            </table>
        </form>
    </Container>;
};

Table.defaultProps = {
    stockExportSchedules: [],
    stockTemplateOptions: [],
    fromAccountOptions: [],
    toAccountOptions: []
};

export default Table;

const buildEmptySchedule = () => {
    return {
        active: false,
        date: null,
        day: null,
        filename: '{{type}}-{{date}}-{{time}}.csv',
        frequency: 'hourly',
        fromDataExchangeAccountId: null,
        fromDataExchangeAccountType: null,
        hour: null,
        id: null,
        name: '',
        operation: 'export',
        templateId: null,
        toDataExchangeAccountId: null,
        toDataExchangeAccountType: null
    }
};
