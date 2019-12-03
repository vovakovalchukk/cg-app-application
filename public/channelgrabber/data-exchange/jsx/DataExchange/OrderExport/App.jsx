import React from 'react';
import Table from "../Schedule/Table";
import Service from "./Components/Service";
import Select from "Common/Components/Select";
import Checkbox from "Common/Components/Checkbox--stateless"
import {formatOptionsFromMap} from 'CommonSrc/js-vanilla/Common/Utils/form';
import {useSelectState} from 'CommonSrc/js-vanilla/Common/Hooks/Form/select';
import {useCheckboxState} from 'CommonSrc/js-vanilla/Common/Hooks/Form/checkbox';
import {exportViaEmail, exportToBrowser} from 'DataExchange/Utils/export';

const exportUrl = '/dataExchange/order/export/download';

const OrderExportApp = (props) => {
    const {templateOptions, savedFilterOptions} = props;

    const formattedTemplateOptions = formatOptionsFromMap(templateOptions);
    const formattedFilters = formatOptionsFromMap(savedFilterOptions)

    const templateState = useSelectState(formattedTemplateOptions);
    const filterState = useSelectState(formattedFilters);
    const sendViaEmailState = useCheckboxState(formattedFilters);

    const onSubmit = async (event) => {
        event.preventDefault();

        const data = {
            templateId: templateState.selectedOption.value,
            savedFilterName: filterState.selectedOption.value,
            sendViaEmail: sendViaEmailState.value
        };

        if (!data.sendViaEmail) {
            await exportToBrowser(data, exportUrl, "orders");
            return;
        }

        exportViaEmail(data, exportUrl, "orders");
    };

    return (
        <div>
            <div className="u-margin-top-xxlarge u-form-width-medium">
                <form id={"orders-export-form"} onSubmit={onSubmit}>
                    <div className="u-flex-v-center u-margin-top-small">
                        <label htmlFor="template" className="u-flex-1">Template:</label>
                        <div className="u-flex-4">
                            <Select
                                id={"template"}
                                name={"template"}
                                options={formattedTemplateOptions}
                                filterable={true}
                                autoSelectFirst={false}
                                selectedOption={templateState.selectedOption}
                                onOptionChange={templateState.onOptionChange}
                                classNames={'u-inline-block u-width-120px'}
                            />
                        </div>
                    </div>
                    <div className="u-flex-v-center u-margin-top-small">
                        <label htmlFor="template" className="u-flex-1">Saved Filter:</label>
                        <div className="u-flex-4">
                            <Select
                                id={"savedFilter"}
                                name={"saved filter"}
                                options={formattedFilters}
                                filterable={true}
                                autoSelectFirst={false}
                                selectedOption={filterState.selectedOption}
                                onOptionChange={filterState.onOptionChange}
                                classNames={'u-inline-block u-width-120px'}
                            />
                        </div>
                    </div>
                    <div className="u-flex-v-center u-margin-top-small">
                        <label htmlFor="sendViaEmail" className="u-flex-1">Send via email:</label>
                        <div className="u-flex-4">
                            <Checkbox
                                id={"sendViaEmail"}
                                name={"sendViaEmail"}
                                onSelect={sendViaEmailState.onSelect}
                                isSelected={sendViaEmailState.value}
                            />
                        </div>
                    </div>

                    <button type="submit" className={'u-margin-top-med button'}>Download</button>
                </form>
            </div>
            <div className={'u-margin-top-medium u-inline-block'}>
                <Table
                    {...props}
                    buildEmptySchedule={Service.buildEmptySchedule}
                    columns={Service.getColumns()}
                    formatPostDataForSave={Service.formatPostDataForSave}
                    validators={Service.validators()}
                />
            </div>
        </div>
    );
};

export default OrderExportApp;
