import React, {useState} from 'react';
import styled from 'styled-components';
import Select from 'Common/Components/Select.js';

const BulkActions = styled.span`
    margin-right: 4rem;
    display: inline-block;
    float: right;
`;

const StyledSelect = styled(Select)`
    display: inline-block;
    width: 160px;
`;

const Root = props => {
    let courierState = useSelect();
    let serviceState = useSelect();

    return (
        <BulkActions>
            <div className={"u-flex-center"}>
                <span>Bulk Apply</span>
                <div className={"u-inline-block u-margin-left-small"}>
                    <StyledSelect
                        filterable={true}
                        //                        selectedOption={courierState.selected}
                        //                        onOptionChange={option => {
                        //                            courierState.setOption(option);
                        //                        }}
                    />
                </div>

                {courierState.selected &&
                <div className={"u-inline-block u-margin-left-small"}>
                    <StyledSelect
                        filterable={true}
                        //                        selectedOption={serviceState.selected}
                        //                        onOptionChange={option => {
                        //                            serviceState.setOption(option);
                        //                        }}
                    />
                </div>
                }
            </div>
        </BulkActions>
    );

    function useSelect(initialValue) {
        let [selected, setOption] = useState(initialValue);
        let getOptionName = () => {
            if (!value || typeof value !== "object") {
                return null;
            }
            return value.name;
        };
        return {
            selected,
            setOption,
            getOptionName
        };
    }
};

export default Root;

function getAllPossibleCourierOptions() {
    let allPossibleCourierOptions = [];
    for (let record of data) {
        if (!record.courierOptions || !record.courierOptions.options) {
            continue;
        }
        let courierOptions = record.courierOptions.options;
        allPossibleCourierOptions = attachUniqueCourierOptions(allPossibleCourierOptions, courierOptions);
    }
    return allPossibleCourierOptions;
}

function attachUniqueCourierOptions(allPossibleCourierOptions, courierOptions) {
    for (let option of courierOptions) {
        if (allPossibleCourierOptions.findIndex(option) < 0) {
            continue;
        }
        allPossibleCourierOptions.push(option);
    }
    return allPossibleCourierOptions;
}