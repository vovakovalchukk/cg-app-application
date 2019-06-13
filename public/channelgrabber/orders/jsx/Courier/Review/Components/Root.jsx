import React, {useState, useEffect} from 'react';
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
    let servicesState = useSelect();

    const courierAjaxRoute = props.courierAjaxRoute.toLowerCase();
    const servicesAjaxRoute = props.servicesAjaxRoute.toLowerCase();

    const ajaxRouteCallbackMap = {
        [courierAjaxRoute]: courierAjaxCallback,
        [servicesAjaxRoute]: serviceAjaxCallback
    };

    $(document).on('ajaxComplete', function getDataFromAjax(event, xhr, settings) {
        let url = settings.url.toLowerCase();
        if (url !== courierAjaxRoute && url !== servicesAjaxRoute) {
            return;
        }
        ajaxRouteCallbackMap[url](event, xhr, settings);
    });

    return (
        <BulkActions>
            <div className={"u-flex-center"}>
                <span>Bulk Apply</span>
                <div className={"u-inline-block u-margin-left-small"}>
                    <StyledSelect
                        filterable={true}
                        options={courierState.options}
                        onOptionChange={onCourierOptionChange}
                    />
                </div>

                {courierState.selected &&
                <div className={"u-inline-block u-margin-left-small"}>
                    <StyledSelect
                        filterable={true}
                    />
                </div>
                }
            </div>
        </BulkActions>
    );

    function useSelect(initialValue) {
        let [selected, setOption] = useState(initialValue);
        let [options, setOptions] = useState();

        let getOptionName = () => {
            if (!value || typeof value !== "object") {
                return null;
            }
            return value.name;
        };

        return {
            selected,
            setOption,
            getOptionName,
            options,
            setOptions
        };
    }

    function courierAjaxCallback(event, xhr, settings) {
        console.log('in courierAjaxCallback');
        let records = xhr.responseJSON.Records;
        if (!records) {
            return;
        }
        let allPossibleCourierOptions = getAllPossibleCourierOptions(records);
        allPossibleCourierOptions.map(option => option.name = option.title);
        courierState.setOptions(allPossibleCourierOptions);
    }

    function serviceAjaxCallback(event, xhr, settings) {
        console.log('in serviceAjaxCallback');
        debugger;
    }

    function onCourierOptionChange(option) {
        props.CourierReviewService.bulkChangeAllOrderCouriers(option.value);
    }
};

export default Root;

function getAllPossibleCourierOptions(records) {
    let allPossibleCourierOptions = [];
    for (let record of records) {
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
        let foundOptionIndex = allPossibleCourierOptions.findIndex(alreadySavedOption => {
            if (option.value === alreadySavedOption.value) {
                return true;
            }
        });

        if (foundOptionIndex >= 0) {
            continue;
        }

        allPossibleCourierOptions.push(option);
    }
    return allPossibleCourierOptions;
}