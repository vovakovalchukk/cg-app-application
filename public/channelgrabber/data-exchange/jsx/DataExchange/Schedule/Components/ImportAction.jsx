import React from 'react';
import SelectComponent from "Common/Components/Select";

const ImportAction = (props) => {

    const formatOptions = () => {
        return Object.keys(props.actionOptions).map((action) => {
            return {
                value: action,
                name: props.actionOptions[action]
            }
        });
    };

    const findSelectedOption = () => {
        const action = Object.keys(props.actionOptions).find((action) => {
            return action == props.schedule.action;
        });

        return {
            value: action,
            name: props.actionOptions[action]
        }
    };

    const onOptionChange = (option) => {
        props.onChange(option.value);
    };

    return <span>
        <SelectComponent
            className={'u-width-100pc'}
            options={formatOptions()}
            selectedOption={findSelectedOption()}
            onOptionChange={onOptionChange}
            autoSelectFirst={false}
        />
    </span>;
};

ImportAction.defaultProps = {
    schedule: {},
    actionOptions: {},
    onChange: () => {}
};

export default ImportAction;
