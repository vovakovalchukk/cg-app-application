import React, {useState} from 'react';
import Table from "../Schedule/Table";
import Service from "./Components/Service";
import Select from "Common/Components/Select";
import Checkbox from "Common/Components/Checkbox--stateless"
import {formatOptionsFromMap} from 'CommonSrc/js-vanilla/Common/Utils/form';
import {useSelectState} from 'CommonSrc/js-vanilla/Common/Hooks/Form/select';
import {useCheckboxState} from 'CommonSrc/js-vanilla/Common/Hooks/Form/checkbox';
import {exportViaEmail, exportToBrowser} from 'DataExchange/Utils/export';

const exportUrl = '/dataExchange/stock/export/download';

const StockExportApp = (props) => {
    const {templateOptions} = props;

    const templateState = useSelectState({});
    const sendViaEmailState = useCheckboxState(false);

    const formattedTemplateOptions = formatOptionsFromMap(templateOptions);

    const onSubmit = async (event) => {
        event.preventDefault();
        const data = {
            templateId: templateState.selectedOption.value,
            sendViaEmail: sendViaEmailState.value
        };

        if (!data.sendViaEmail) {
            await exportToBrowser(data, exportUrl, 'stock');
            return;
        }

        exportViaEmail(data, exportUrl, 'stock')
    };

    return (
        <div>
            <div className="u-margin-top-xxlarge u-form-width-medium">
                <form id={"stock-import-form"} onSubmit={onSubmit}>
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

export default StockExportApp;