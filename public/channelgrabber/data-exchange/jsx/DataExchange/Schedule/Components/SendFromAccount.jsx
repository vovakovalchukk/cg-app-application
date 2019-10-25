import React from 'react';
import SelectComponent from "Common/Components/Select";

const SendFromAccount = (props) => {

    const formatOptions = () => {
        return Object.keys(props.fromAccountOptions).map((accountId) => {
            return {
                value: accountId,
                name: props.fromAccountOptions[accountId]
            }
        });
    };

    const findSelectedOption = () => {
        const account = Object.keys(props.fromAccountOptions).find((accountId) => {
            return accountId == props.schedule.fromDataExchangeAccountId;
        });

        return {
            value: account,
            name: props.fromAccountOptions[account]
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

SendFromAccount.defaultProps = {
    schedule: {},
    fromAccountOptions: {},
    onChange: () => {}
};

export default SendFromAccount;
