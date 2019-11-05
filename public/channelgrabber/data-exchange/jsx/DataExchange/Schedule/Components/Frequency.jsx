import React from 'react';
import SelectComponent from "Common/Components/Select";

const FREQUENCY_HOURLY = 'hourly';
const FREQUENCY_DAILY = 'daily';
const FREQUENCY_WEEKLY = 'weekly';
const FREQUENCY_MONTHLY = 'monthly';

const OPTIONS = [
    FREQUENCY_HOURLY,
    FREQUENCY_DAILY,
    FREQUENCY_WEEKLY,
    FREQUENCY_MONTHLY
];

const Frequency = (props) => {

    const ucfirst = (string) => string.charAt(0).toUpperCase() + string.slice(1);

    const formatOptions = () => {
        return OPTIONS.map((value) => {
            return {
                value,
                name: ucfirst(value)
            };
        })
    };

    const findSelectedOption = () => {
        return {
            value: props.schedule.frequency,
            name: ucfirst(props.schedule.frequency)
        }
    };

    const onOptionChange = (option) => props.onChange(option.value);

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

Frequency.defaultProps = {
    schedule: {},
    onChange: () => {}
};

export default Frequency;
export {FREQUENCY_HOURLY, FREQUENCY_DAILY, FREQUENCY_WEEKLY, FREQUENCY_MONTHLY};
