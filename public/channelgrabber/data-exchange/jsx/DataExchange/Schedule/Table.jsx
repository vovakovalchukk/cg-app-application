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
import * as Columns from "./Columns";
import ImportAction from "DataExchange/Schedule/Components/ImportAction";
import SavedFilters from "DataExchange/Schedule/Components/SavedFilters";

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

    const renderActiveCheckbox = (schedule, index) => {
        return <td>
            <CheckboxContainer
                className={'u-flex-center'}
                isSelected={schedule.active}
                onSelect={() => {handleInputValueChanged(index, 'active', !schedule.active)}}
            />
        </td>;
    };

    const renderRuleNameCell = (schedule, index) => {
        return <td>
            {renderInputColumnForType(schedule, index, 'name')}
        </td>
    };

    const renderInputColumnForType = (schedule, index, property, type = 'text') => {
        return <Input
            property={property}
            type={type}
            value={schedule[property] || ''}
            onChange={(event) => {handleInputValueChanged(index, property, event.target.value)}}
        />
    };

    const renderTemplateColumn = (schedule, index) => {
        return <SelectDropDownCell>
            <TemplateColumn
                schedule={schedule}
                index={index}
                stockTemplateOptions={props.templateOptions}
                onChange={(templateId) => {handleInputValueChanged(index, 'templateId', templateId)}}
            />
        </SelectDropDownCell>
    };

    const renderSendToAccountColumn = (schedule, index) => {
        return <SelectDropDownCell>
            <SendToAccountColumn
                schedule={schedule}
                toAccountOptions={props.toAccountOptions}
                onChange={(sendToAccount) => {
                    const [accountType, accountId] = sendToAccount.split('-');
                    handleInputValueChanged(index, 'toDataExchangeAccountId', accountId);
                    handleInputValueChanged(index, 'toDataExchangeAccountType', accountType);
                }}
            />
        </SelectDropDownCell>
    };

    const renderSendFromAccountColumn = (schedule, index) => {
        if (schedule.toDataExchangeAccountType !== 'email') {
            return <td/>
        }

        return <SelectDropDownCell>
            <SendFromAccountColumn
                schedule={schedule}
                fromAccountOptions={props.fromAccountOptions}
                onChange={(sendFromAccountId) => {
                    handleInputValueChanged(index, 'fromDataExchangeAccountType', 'email');
                    handleInputValueChanged(index, 'fromDataExchangeAccountId', sendFromAccountId, false);
                }}
            />
        </SelectDropDownCell>
    };

    const renderReceiveFromColumn = (schedule, index) => {
        return <SelectDropDownCell>
            <SendFromAccountColumn
                schedule={schedule}
                fromAccountOptions={props.fromAccountOptions}
                onChange={(accountId) => handleInputValueChanged(index, 'fromDataExchangeAccountId', accountId)}
            />
        </SelectDropDownCell>;
    };

    const renderFileNameColumn = (schedule, index) => {
        return <td>{renderInputColumnForType(schedule, index, 'filename')}</td>;
    };

    const renderFrequencyColumn = (schedule, index) => {
        return <SelectDropDownCell>
            <FrequencyColumn
              schedule={schedule}
                onChange={(frequency) => {
                    handleInputValueChanged(index, 'frequency', frequency);
                }}
            />
        </SelectDropDownCell>
    };

    const renderWhenColumn = (schedule, index) => {
        return <SelectDropDownCell>
            <WhenColumn
                schedule={schedule}
                onDayOfMonthChange={(date) => {handleInputValueChanged(index, 'date', date)}}
                onDayOfWeekChange={(day) => {handleInputValueChanged(index, 'day', day)}}
                onHourChange={(hour) => {handleInputValueChanged(index, 'hour', hour)}}
            />
        </SelectDropDownCell>
    };

    const renderActions = (schedule, index) => {
        return <td>
            <ActionsColumn
                removeIconVisible={!isLastEntry(index)}
                saveIconDisabled={!props.isScheduleValid(schedule)}
                onSave={() => handleScheduleSave(index, schedule)}
                onDelete={() => handleScheduleDelete(index, schedule)}
            />
        </td>
    };

    const renderImportActionColumn = (schedule, index) => {
        return <SelectDropDownCell>
            <ImportAction
                schedule={schedule}
                actionOptions={props.actionOptions}
                onChange={(action) => {handleInputValueChanged(index, 'action', action)}}
            />
        </SelectDropDownCell>;
    };

    const renderSavedFiltersColumn = (schedule, index) => {
          return <SelectDropDownCell>
              <SavedFilters
                  schedule={schedule}
                  filterOptions={props.savedFilterOptions}
                  onChange={(filter) => {handleInputValueChanged(index, 'savedFilterName', filter)}}
              />
          </SelectDropDownCell>;
    };

    const COLUMN_MAP = {
        [Columns.KEY_ENABLED]: renderActiveCheckbox,
        [Columns.KEY_RULE_NAME]: renderRuleNameCell,
        [Columns.KEY_TEMPLATE]: renderTemplateColumn,
        [Columns.KEY_SEND_TO]: renderSendToAccountColumn,
        [Columns.KEY_SEND_FROM]: renderSendFromAccountColumn,
        [Columns.KEY_FILE_NAME]: renderFileNameColumn,
        [Columns.KEY_FREQUENCY]: renderFrequencyColumn,
        [Columns.KEY_WHEN]: renderWhenColumn,
        [Columns.KEY_ACTIONS]: renderActions,
        [Columns.KEY_RECEIVE_FROM]: renderReceiveFromColumn,
        [Columns.KEY_IMPORT_ACTION]: renderImportActionColumn,
        [Columns.KEY_SAVED_FILTERS]: renderSavedFiltersColumn
    };

    const renderTableHeader = () => {
        const headers = props.columns.map((column) => {
            return <TableHeader width={column.width || null}>{column.header}</TableHeader>
        });
        return <tr>{headers}</tr>;
    };

    const renderRows = () => {
        return schedules.map((schedule, index) => {
            const cells = props.columns.map((column) => {
                return COLUMN_MAP[column.key](schedule, index);
            });
            return <tr>{cells}</tr>
        });
    };

    const handleInputValueChanged = (index, property, newValue, createNewRowAllowed = true) => {
        if (createNewRowAllowed && isLastEntry(index)) {
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

    async function handleScheduleSave(index, schedule) {
        n.notice((schedule.id ? 'Updating' : 'Saving') + ` your <strong>${schedule.name}</strong> schedule...`, 2000);

        try {
            const response = await ActionsService.saveSchedule(props.formatPostDataForSave(schedule));
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