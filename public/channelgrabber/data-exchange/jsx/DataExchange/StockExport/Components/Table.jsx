import React, {useReducer} from 'react';
import styled from "styled-components";
import CheckboxContainer from "Common/Components/Checkbox--stateless";
import scheduleReducer from "../ScheduleReducer";
import ActionsColumn from "./Column/Actions";
import TemplateColumn from "./Column/Template";
import SendToAccountColumn from "./Column/SendToAccount";
import SendFromAccountColumn from "./Column/SendFromAccount";
import FrequencyColumn from "./Column/Frequency";
import WhenColumn from "./Column/When";

const Container = styled.div`
    margin-top: 45px;
`;
const SelectDropDownCell = styled.td`
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
    const initialSchedules = [...props.stockExportSchedules, buildEmptySchedule(props)];
    const [schedules, dispatch] = useReducer(scheduleReducer, initialSchedules);

    const renderTableHeader = () => {
        return <tr>
            <TableHeader width={'80px'}>Enabled</TableHeader>
            <TableHeader>Rule name</TableHeader>
            <TableHeader>Template</TableHeader>
            <TableHeader width={'250px'}>Send to</TableHeader>
            <TableHeader width={'250px'}>Send from</TableHeader>
            <TableHeader>File name</TableHeader>
            <TableHeader>Frequency</TableHeader>
            <TableHeader width={'230px'}>When</TableHeader>
            <TableHeader width={'80px'}>Actions</TableHeader>
        </tr>;
    };

    const renderRows = () => {
        return schedules.map((schedule, index) => {
            return <tr>
                <td>{renderActiveCheckbox(schedule, index)}</td>
                <td>{renderInputColumnForType(schedule, index, 'name')}</td>
                <SelectDropDownCell>{renderTemplateColumn(schedule, index)}</SelectDropDownCell>
                <SelectDropDownCell>{renderSendToAccountColumn(schedule, index)}</SelectDropDownCell>
                <SelectDropDownCell>{schedule.toDataExchangeAccountType === 'email' ? renderSendFromAccountColumn(schedule, index) : null}</SelectDropDownCell>
                <td>{renderInputColumnForType(schedule, index, 'filename')}</td>
                <SelectDropDownCell>{renderFrequencyColumn(schedule, index)}</SelectDropDownCell>
                <SelectDropDownCell>{renderWhenColumn(schedule, index)}</SelectDropDownCell>
                <td>{renderActions(index, schedule)}</td>
            </tr>;
        });
    };

    const renderActiveCheckbox = (schedule, index) => {
        return <CheckboxContainer
            isSelected={schedule.active}
            onSelect={() => {handleInputValueChanged(index, 'active', !schedule.active)}}
        />
    };

    const renderInputColumnForType = (schedule, index, property, type = 'text') => {
        return <Input
            property={property}
            type={type}
            value={schedule[property] || ''}
            onChange={(event) => {handleInputValueChanged(index, property, event.target.value)}}
        />;
    };

    const renderTemplateColumn = (schedule, index) => {
        return <TemplateColumn
            schedule={schedule}
            index={index}
            stockTemplateOptions={props.stockTemplateOptions}
            onChange={(templateId) => {handleInputValueChanged(index, 'templateId', templateId)}}
        />;
    };

    const renderSendToAccountColumn = (schedule, index) => {
        return <SendToAccountColumn
            schedule={schedule}
            toAccountOptions={props.toAccountOptions}
            onChange={(sendToAccount) => {
                const [accountType, accountId] = sendToAccount.split('-');
                handleInputValueChanged(index, 'toDataExchangeAccountId', accountId);
                handleInputValueChanged(index, 'toDataExchangeAccountType', accountType);
            }}
        />;
    };

    const renderSendFromAccountColumn = (schedule, index) => {
        return <SendFromAccountColumn
            schedule={schedule}
            fromAccountOptions={props.fromAccountOptions}
            onChange={(sendFromAccountId) => {
                handleInputValueChanged(index, 'fromDataExchangeAccountType', 'email');
                handleInputValueChanged(index, 'fromDataExchangeAccountId', sendFromAccountId);
            }}
        />;
    };

    const renderFrequencyColumn = (schedule, index) => {
        return <FrequencyColumn
            schedule={schedule}
            onChange={(frequency) => {
                handleInputValueChanged(index, 'frequency', frequency);
            }}
        />;
    };

    const renderWhenColumn = (schedule, index) => {
        return <WhenColumn
            schedule={schedule}
            onDayOfMonthChange={(date) => {handleInputValueChanged(index, 'date', date)}}
            onDayOfWeekChange={(day) => {handleInputValueChanged(index, 'day', day)}}
            onHourChange={(hour) => {handleInputValueChanged(index, 'hour', hour)}}
        />;
    };

    const handleInputValueChanged = (index, property, newValue) => {
        if (isLastEntry(index)) {
            dispatch({
                type: 'addNewSchedule',
                payload: {
                    schedule: buildEmptySchedule(props)
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

    const renderActions = (index, schedule) => {
        return <ActionsColumn
            removeIconVisible={!isLastEntry(index)}
            saveIconDisabled={false}
            onSave={() => handleScheduleSave(index, schedule)}
            onDelete={() => handleScheduleDelete(index, schedule)}
        />
    };

    async function handleScheduleSave(index, schedule) {
        n.notice((schedule.id ? 'Saving' : 'Updating') + ` your ${schedule.name} schedule...`, 2000);
        const response = await saveSchedule(schedule);
        if (!response.success) {
            n.error('Couldn\'t ' + (schedule.id ? 'save' : 'update') + ` your ${schedule.name} schedule, please try again or contact support if the problem persists`);
            return;
        }

        dispatch({
            type: 'scheduleSavedSuccessfully',
            payload: {
                index,
                response
            }
        });
    }

    async function handleScheduleDelete(index, schedule) {
        if (schedule.id) {
            n.notice(`Deleting your schedule ${schedule.name}...`, 2000);

            const response = await deleteSchedule(schedule.id);
            if (!response.success) {
                n.error('The schedule couldn\'t be deleted. Please try again or contact support if the problem persists');
                return;
            }

            n.success('The schedule was successfully deleted');
        }

        dispatch({
            type: 'scheduleDeletedSuccessfully',
            payload: {index}
        });
    }

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

const buildEmptySchedule = (props) => {
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

async function saveSchedule(schedule) {
    return $.ajax({
        url: window.location.href + '/save',
        type: 'POST',
        dataType: 'json',
        data: schedule,
        success: (response) => response,
        error: (error) => error
    });
}

async function deleteSchedule(id) {
    return $.ajax({
        url: window.location.href + '/remove',
        type: 'POST',
        dataType: 'json',
        data: {id},
        success: (response) => response,
        error: (error) => error
    });
}
