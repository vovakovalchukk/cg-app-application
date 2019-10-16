import React, {useReducer} from 'react';
import styled from "styled-components";
import CheckboxContainer from "Common/Components/Checkbox--stateless";
import scheduleReducer from "./ScheduleReducer";
import ActionsColumn from "./Components/Actions";
import TemplateColumn from "./Components/Template";
import SendToAccountColumn from "./Components/SendToAccount";
import SendFromAccountColumn from "./Components/SendFromAccount";
import FrequencyColumn from "./Components/Frequency";
import WhenColumn from "./Components/When";
import ActionsService from "./ActionsService";

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
    const initialSchedules = [...props.schedules, props.buildEmptySchedule(props)];
    const [schedules, dispatch] = useReducer(scheduleReducer, initialSchedules);

    const renderTableHeader = () => {
        const headers = props.columns.map((column) => {
            return <TableHeader width={column.width || null}>{column.header}</TableHeader>
        });
        return <tr>{headers}</tr>;
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
                    schedule: props.buildEmptySchedule()
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
        n.notice((schedule.id ? 'Updating' : 'Saving') + ` your <strong>${schedule.name}</strong> schedule...`, 2000);

        try {
            const response = await ActionsService.saveSchedule(schedule);
            if (!response.success) {
                throw new Error({message: response.message || null});
            }

            n.success((schedule.id ? 'Update' : 'Save') + ' successful.');
            dispatch({
                type: 'scheduleSavedSuccessfully',
                payload: {
                    index,
                    response
                }
            });
        } catch (error) {
            const message = error.message || 'Couldn\'t ' + (schedule.id ? 'update' : 'save') + ` your <strong>${schedule.name}</strong> schedule, please try again or contact support if the problem persists.`;
            n.error(message);
        }
    }

    async function handleScheduleDelete(index, schedule) {
        if (schedule.id) {
            n.notice(`Deleting your schedule ${schedule.name}...`, 2000);

            const response = await ActionsService.deleteSchedule(schedule.id);
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
    schedules: [],
    stockTemplateOptions: [],
    fromAccountOptions: [],
    toAccountOptions: [],
    buildEmptySchedule: () => {}
};

export default Table;