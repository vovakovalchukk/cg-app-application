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

    useEffect(() => {
        $(document).on('ajaxComplete', function getDataFromAjax(event, xhr, settings) {
            let url = settings.url.toLowerCase();
            if (url !== props.courierAjaxRoute.toLowerCase() && url !== props.servicesAjaxRoute.toLowerCase()) {
                return;
            }
            ajaxRouteCallbackMap[url](event, xhr, settings);
        });
    }, []);

    const courierAjaxRoute = props.courierAjaxRoute.toLowerCase();
    const servicesAjaxRoute = props.servicesAjaxRoute.toLowerCase();

    const ajaxRouteCallbackMap = {
        [courierAjaxRoute]: courierAjaxCallback,
        [servicesAjaxRoute]: serviceAjaxCallback
    };

    let uniqueServices = [];
    let serviceEventsCount = 0;

    return (
        <BulkActions>
            <div className={"u-flex-center"}>
                <span>Bulk Apply</span>
                <div className={"u-inline-block u-margin-left-small"}>
                    <StyledSelect
                        filterable={true}
                        options={courierState.options}
                        onOptionChange={onCourierOptionChange}
                        selectedOption={courierState.selected}
                        autoSelectFirst={false}
                    />
                </div>

                <div className={"u-inline-block u-margin-left-small"}>
                    <StyledSelect
                        filterable={true}
                        options={servicesState.options}
                        autoSelectFirst={false}
                        onOptionChange={onServiceOptionChange}
                        selectedOption={servicesState.selected}
                        disabled={!courierState.selected}
                    />
                </div>
            </div>
        </BulkActions>
    );

    function courierAjaxCallback(event, xhr, settings) {
        let records = xhr.responseJSON.Records;
        if (!records) {
            return;
        }
        let allPossibleCourierOptions = getAllPossibleCourierOptions(records);
        allPossibleCourierOptions.forEach(option => option.name = option.title);
        courierState.setOptions(allPossibleCourierOptions);
    }

    function serviceAjaxCallback(event, xhr, settings) {
        let services = xhr.responseJSON.serviceOptions;
        serviceEventsCount++;
        if (!services) {
            return;
        }

        uniqueServices = addUniqueOptions(uniqueServices, services);

        if (serviceEventsCount === props.orderIds.length) {
            uniqueServices.forEach(service => service.name = service.title);
            servicesState.setOptions(uniqueServices);
            uniqueServices = [];
            serviceEventsCount = 0;
        }
    }

    function onCourierOptionChange(option) {
        props.CourierReviewService.bulkChangeAllOrderCouriers(option.value);
        courierState.setOption(option);
    }

    function onServiceOptionChange(option) {
        props.CourierReviewService.bulkChangeAllServices(option.value);
        servicesState.setOption(option);
    }
};

export default Root;

function useSelect(initialValue) {
    let [selected, setOption] = useState();
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

function getAllPossibleCourierOptions(records) {
    let allPossibleCourierOptions = [];
    for (let record of records) {
        if (!record.courierOptions || !record.courierOptions.options) {
            continue;
        }
        let courierOptions = record.courierOptions.options;
        allPossibleCourierOptions = addUniqueOptions(allPossibleCourierOptions, courierOptions);
    }
    return allPossibleCourierOptions;
}

function addUniqueOptions(existingOptions, newOptions) {
    for (let option of newOptions) {
        let foundOptionIndex = existingOptions.findIndex(existingOption => {
            if (option.value === existingOption.value) {
                return true;
            }
        });

        if (foundOptionIndex >= 0) {
            continue;
        }

        existingOptions.push(option);
    }
    return existingOptions;
}