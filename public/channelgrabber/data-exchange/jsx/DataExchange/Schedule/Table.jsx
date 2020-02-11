import React, {useReducer} from 'react';
import styled from "styled-components";
import CheckboxContainer from "Common/Components/Checkbox--stateless";
import ActionsColumn from "./Components/Actions";
import TemplateColumn from "./Components/Template";
import SendToAccountColumn from "./Components/SendToAccount";
import SendFromAccountColumn from "./Components/SendFromAccount";
import FrequencyColumn from "./Components/Frequency";
import TimePicker from "./Components/When";
import ImportAction from "./Components/ImportAction";
import SavedFilters from "./Components/SavedFilters";
import * as Columns from "./Columns";
import ActionsService from "./Service/ActionsService";
import scheduleReducer from "./Service/ScheduleReducer";
import Helper from "./Service/Helper";

const Container = styled.div`
    margin-top: 45px;
`;
const CellContainer = styled.td`
    overflow: visible;
`;
const HeaderCell = styled.th`
    width: ${props => props.width ? props.width : 'auto'};
`;
const Input = styled.input`
    max-width: 70%;
    float: none;
`;

const renderActiveCheckbox = ({schedule, index, onInputChange}) => {
    return <CheckboxContainer
        className={'u-flex-center'}
        isSelected={schedule.active}
        onSelect={() => {onInputChange(index, 'active', !schedule.active)}}
    />
};

const renderFrequencyColumn = ({schedule, index, onInputChange}) => {
    return <FrequencyColumn
        schedule={schedule}
        onChange={(frequency) => {
            onInputChange(index, 'frequency', frequency);
        }}
    />
};

const renderInputColumnForType = (schedule, index, property, onInputChange, type = 'text') => {
    return <Input
        property={property}
        type={type}
        value={schedule[property] || ''}
        onChange={(event) => {onInputChange(index, property, event.target.value)}}
    />
};

const renderRuleNameCell = ({schedule, index, onInputChange}) => {
    return renderInputColumnForType(schedule, index, 'name', onInputChange)
};

const renderTemplateColumn = ({schedule, index, onInputChange, templateOptions}) => {
    return <TemplateColumn
        schedule={schedule}
        index={index}
        stockTemplateOptions={templateOptions}
        onChange={(templateId) => {onInputChange(index, 'templateId', templateId)}}
    />
};

const renderSendFromAccountColumn = ({schedule, index, onInputChange, fromAccountOptions}) => {
    if (schedule.toDataExchangeAccountType !== 'email') {
        return null;
    }

    return <SendFromAccountColumn
        schedule={schedule}
        fromAccountOptions={fromAccountOptions}
        onChange={(sendFromAccountId) => {
            onInputChange(index, 'fromDataExchangeAccountType', 'email');
            onInputChange(index, 'fromDataExchangeAccountId', sendFromAccountId, false);
        }}
    />
};

const renderSendToAccountColumn = ({schedule, index, onInputChange, toAccountOptions}) => {
    return <SendToAccountColumn
        schedule={schedule}
        toAccountOptions={toAccountOptions}
        onChange={(sendToAccount) => {
            const [accountType, accountId] = sendToAccount.split('-');
            onInputChange(index, 'toDataExchangeAccountId', accountId);
            onInputChange(index, 'toDataExchangeAccountType', accountType, false);
        }}
    />
};

const renderReceiveFromColumn = ({schedule, index, onInputChange, fromAccountOptions}) => {
    return <SendFromAccountColumn
        schedule={schedule}
        fromAccountOptions={fromAccountOptions}
        onChange={(accountId) => onInputChange(index, 'fromDataExchangeAccountId', accountId)}
    />
};

const renderFileNameColumn = ({schedule, index, onInputChange}) => {
    return renderInputColumnForType(schedule, index, 'filename', onInputChange);
};

const renderWhenColumn = ({schedule, index, onInputChange}) => {
    return <TimePicker
        schedule={schedule}
        onDayOfMonthChange={(date) => {onInputChange(index, 'date', date)}}
        onDayOfWeekChange={(day) => {onInputChange(index, 'day', day)}}
        onHourChange={(hour) => {onInputChange(index, 'hour', hour)}}
    />
};

const renderActions = ({schedule, index, validators, handleScheduleSave, handleScheduleDelete, schedules}) => {
    return <ActionsColumn
        removeIconVisible={!isLastEntry(index, schedules)}
        saveIconDisabled={!Helper.hasScheduleChanged(schedule) || !Helper.validateSchedule(schedule, validators)}
        onSave={() => handleScheduleSave(index, schedule)}
        onDelete={() => handleScheduleDelete(index, schedule)}
    />
};

const renderImportActionColumn = ({schedule, index, onInputChange, actionOptions}) => {
    return <ImportAction
        schedule={schedule}
        actionOptions={actionOptions}
        onChange={(action) => {onInputChange(index, 'action', action)}}
    />
};

const renderSavedFiltersColumn = ({schedule, index, onInputChange, savedFilterOptions}) => {
    return <SavedFilters
        schedule={schedule}
        filterOptions={savedFilterOptions}
        onChange={(filter) => {onInputChange(index, 'savedFilterName', filter)}}
    />
};

const TableHeader = (props) => {
    const headers = props.columns.map((column) => {
        return <HeaderCell width={column.width || null}>{column.header}</HeaderCell>
    });
    return <tr>{headers}</tr>;
};

const COLUMN_CELL_MAP = {
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

const Table = (props) => {
    const initialSchedules = [...props.schedules, props.buildEmptySchedule(props)].map((schedule) => {
        return {...schedule, initialValues: {...schedule}};
    });
    const [schedules, dispatch] = useReducer(scheduleReducer, initialSchedules);

    const renderRows = (renderRow) => {
        return schedules.map((schedule, index) => {
            const cells = props.columns.map((column) => {
                return COLUMN_CELL_MAP[column.key];
            });
            return renderRow(cells, schedule, index);
        });
    };

    const handleInputValueChanged = (index, property, newValue, createNewRowAllowed = true) => {
        if (createNewRowAllowed && isLastEntry(index, schedules)) {
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

    const handleScheduleSave = async function(index, schedule) {
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
    };

    const handleScheduleDelete = async function(index, schedule) {
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
    };

    const renderCells = (cells, renderCell) => {
        let {
            templateOptions,
            toAccountOptions,
            fromAccountOptions,
            validators,
            actionOptions,
            savedFilterOptions,
        } = props;

        const cellProps = {
            templateOptions,
            toAccountOptions,
            fromAccountOptions,
            validators,
            actionOptions,
            savedFilterOptions,
            schedules,
            handleScheduleDelete,
            handleScheduleSave
        };

        return cells.map((Cell) => {
            return renderCell(Cell, cellProps);
        });
    };

    return <Container>
        <form name={'stockExportSchedule'}>
            <table>
                <thead>
                    <TableHeader
                        columns={props.columns}
                    />
                </thead>
                <tbody>{renderRows((cells, schedule, rowIndex) => (
                    <tr>
                        {renderCells(cells, (Cell, additionalCellProps) => {
                            return  (<CellContainer>
                                <Cell
                                    schedule={schedule}
                                    index={rowIndex}
                                    onInputChange={handleInputValueChanged}
                                    {...additionalCellProps}
                                />
                            </CellContainer>)
                        })}
                    </tr>
                ))}</tbody>
            </table>
        </form>
    </Container>;
};

Table.defaultProps = {
    schedules: [],
    stockTemplateOptions: [],
    fromAccountOptions: [],
    toAccountOptions: [],
    buildEmptySchedule: () => {},
    savedFilterOptions: [],
    validators: []
};

export default Table;

function isLastEntry(index, schedules) {
    return schedules.length - 1 === index;
}