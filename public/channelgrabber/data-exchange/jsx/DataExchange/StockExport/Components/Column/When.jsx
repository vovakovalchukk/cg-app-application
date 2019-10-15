import React from 'react';
import styled from "styled-components";
import SelectComponent from "Common/Components/Select";
import {FREQUENCY_MONTHLY, FREQUENCY_WEEKLY} from "./Frequency";

const SelectContainer = styled.div`
    display: inline-flex;
    margin-right: 10px;
`;

const When = (props) => {
    const renderDayOfMonthSelect = () => {
        return <SelectContainer>
            <SelectComponent
                options={buildDayOfMonthOptions()}
            />
        </SelectContainer>
    };

    const buildDayOfMonthOptions = () => {
        const days = [...Array(29).keys()].filter(day => day !== 0);
        return days.map((day) => {
            return {
                value: day,
                name: day.toString()
            }
        });
    };

    const renderDayOfWeekSelect = () => {
        return <SelectContainer>
            <SelectComponent
                options={buildDayOfWeekOptions()}
            />
        </SelectContainer>
    };

    const buildDayOfWeekOptions = () => {
        const weekdayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        const days = [...Array(8).keys()].filter(day => day !== 0);
        return days.map((day) => {
            return {
                value: day,
                name: weekdayNames[day - 1]
            }
        });
    };

    const renderHourSelect = () => {
        return <SelectContainer>
            <SelectComponent
                options={buildHourOptions()}
            />
        </SelectContainer>
    };

    const buildHourOptions = () => {
        const hours = [...Array(24).keys()];
        return hours.map((hour) => {
            return {
                value: hour,
                name: `${hour}:00 - ${hour+=1}:00`
            }
        });
    };

    return <span>
        {props.schedule.frequency ===  FREQUENCY_MONTHLY ? renderDayOfMonthSelect() : null}
        {props.schedule.frequency ===  FREQUENCY_WEEKLY ? renderDayOfWeekSelect() : null}
        {renderHourSelect()}
    </span>;
};

When.defaultProps = {
    schedule: {},
    onChange: () => {}
};

export default When;
