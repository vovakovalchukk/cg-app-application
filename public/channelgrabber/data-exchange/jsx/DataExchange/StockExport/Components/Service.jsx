import Columns from "DataExchange/Schedule/Columns";
import Validators from "DataExchange/Schedule/Service/Validators";

const Service = {
    buildEmptySchedule: () => {
        return {
            active: false,
            date: 1,
            day: 1,
            filename: '{{type}}-{{date}}-{{time}}.csv',
            frequency: 'hourly',
            fromDataExchangeAccountId: null,
            fromDataExchangeAccountType: null,
            hour: 0,
            id: null,
            name: '',
            operation: 'export',
            templateId: null,
            toDataExchangeAccountId: null,
            toDataExchangeAccountType: null
        }
    },
    getColumns: () => {
        return [
            Columns.enabled,
            Columns.ruleName,
            Columns.template,
            Columns.sendTo,
            Columns.sendFrom,
            Columns.fileName,
            Columns.frequency,
            Columns.when,
            Columns.actions
        ];
    },
    formatPostDataForSave: (schedule) => {
        return {
            ...schedule,
            toDataExchangeAccountId: `${schedule.toDataExchangeAccountType}-${schedule.toDataExchangeAccountId}`
        };
    },
    validators: () => {
        return [
            Validators.name,
            Validators.template,
            Validators.fromAccount,
            Validators.filename,
            Validators.frequency
        ];
    }
};

export default Service;