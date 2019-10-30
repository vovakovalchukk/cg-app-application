import React from 'react';
import styled from "styled-components";
import SelectComponent from "Common/Components/Select";
import {FREQUENCY_HOURLY, FREQUENCY_MONTHLY, FREQUENCY_WEEKLY} from "./Frequency";

const SelectContainer = styled.div`
    display: inline-flex;
    margin-right: 10px;
`;

const DayOfMonthSelect = (props) => {
    const buildDayOfMonthOptions = () => {
        const days = [...Array(29).keys()].filter(day => day !== 0);
        return days.map((day) => {
            return {
                value: day,
                name: getOrdinal(day)
            }
        });
    };

    const getOrdinal = (number) => {
        const suffix = ["th", "st", "nd", "rd"];
        const v = number % 100;
        return number + (suffix[(v-20) % 10] || suffix[v] || suffix[0]);
    };

    const getSelectedOption = () => {
        if (!props.schedule.date) {
            return null;
        }

        return {
            value: props.schedule.date,
            name: getOrdinal(props.schedule.date)
        }
    };

    return <SelectContainer>
        <SelectComponent
            options={buildDayOfMonthOptions()}
            selectedOption={getSelectedOption()}
            onOptionChange={(option) => {props.onChange(option.value)}}
            autoSelectFirst={false}
        />
    </SelectContainer>
};

const DayOfWeekSelect = (props) => {
    const weekdayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    const buildDayOfWeekOptions = () => {
        const days = [...Array(8).keys()].filter(day => day !== 0);
        return days.map((day) => {
            return {
                value: day,
                name: weekdayNames[day - 1]
            }
        });
    };

    const getSelectedOption = () => {
        if (!props.schedule.day) {
            return null;
        }

        return {
            value: props.schedule.day,
            name: weekdayNames[props.schedule.day - 1]
        }
    };

    return <SelectContainer>
        <SelectComponent
            options={buildDayOfWeekOptions()}
            selectedOption={getSelectedOption()}
            onOptionChange={(option) => {props.onChange(option.value)}}
            autoSelectFirst={false}
        />
    </SelectContainer>
};

const HourSelect = (props) => {
    const buildHourOptions = () => {
        const hours = [...Array(24).keys()];
        return hours.map((hour) => {
            return {
                value: hour,
                name: buildHourDisplayName(hour)
            }
        });
    };

    const buildHourDisplayName = (hour) => {
        return `${hour}:00 - ${hour+=1}:00`;
    };

    const getSelectedOption = () => {
        if (props.schedule.hour === null) {
            return null;
        }

        return {
            value: props.schedule.hour,
            name: buildHourDisplayName(props.schedule.hour)
        }
    };

    return <SelectContainer>
        <SelectComponent
            options={buildHourOptions()}
            selectedOption={getSelectedOption()}
            onOptionChange={(option) => {props.onChange(option.value)}}
            autoSelectFirst={false}
        />
    </SelectContainer>
};

const TimePicker = (props) => {
    const renderDayOfMonthSelect = () => <DayOfMonthSelect schedule={props.schedule} onChange={props.onDayOfMonthChange}/>;
    const renderDayOfWeekSelect = () => <DayOfWeekSelect schedule={props.schedule} onChange={props.onDayOfWeekChange}/>;
    const renderHourSelect = () => <HourSelect schedule={props.schedule} onChange={props.onHourChange}/>;

    if (props.schedule.frequency === FREQUENCY_HOURLY) {
        return <span>Every hour</span>;
    }

    return <span>
        {props.schedule.frequency ===  FREQUENCY_MONTHLY ? renderDayOfMonthSelect() : null}
        {props.schedule.frequency ===  FREQUENCY_WEEKLY ? renderDayOfWeekSelect() : null}
        {renderHourSelect()}
    </span>;
};

TimePicker.defaultProps = {
    schedule: {},
    onChange: () => {}
};

export default TimePicker;
