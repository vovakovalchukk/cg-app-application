import React from 'react';
import SelectComponent from "Common/Components/Select";

const Template = (props) => {

    const formatOptions = () => {
        return Object.keys(props.stockTemplateOptions).map((templateId) => {
            return {
                value: templateId,
                name: props.stockTemplateOptions[templateId]
            }
        });
    };

    const findSelectedOption = () => {
        const templateId = Object.keys(props.stockTemplateOptions).find((templateId) => {
            return templateId == props.schedule.templateId;
        });

        return {
            value: templateId,
            name: props.stockTemplateOptions[templateId]
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

Template.defaultProps = {
    schedule: {},
    index: 0,
    stockTemplateOptions: {},
    onChange: () => {}
};

export default Template;
