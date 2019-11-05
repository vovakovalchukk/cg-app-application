import React from 'react';
import SelectComponent from "Common/Components/Select";

const SavedFilters = (props) => {

    const formatOptions = () => {
        return Object.keys(props.filterOptions).map((filter) => {
            return {
                value: filter,
                name: props.filterOptions[filter]
            }
        });
    };

    const findSelectedOption = () => {
        const filter = Object.keys(props.filterOptions).find((filter) => {
            return filter == props.schedule.savedFilterName;
        });

        return {
            value: filter,
            name: props.filterOptions[filter]
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

SavedFilters.defaultProps = {
    schedule: {},
    filterOptions: {},
    onChange: () => {}
};

export default SavedFilters;
