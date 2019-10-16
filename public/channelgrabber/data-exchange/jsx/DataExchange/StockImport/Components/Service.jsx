import Columns from "DataExchange/Schedule/Columns";

const Service = {
    buildEmptySchedule: () => {
        return {
            active: false,
            date: null,
            day: null,
            filename: '{{type}}-{{date}}-{{time}}.csv',
            frequency: 'hourly',
            fromDataExchangeAccountId: null,
            fromDataExchangeAccountType: null,
            hour: null,
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
            Columns.fileName,
            Columns.frequency,
            Columns.when,
            Columns.actions
        ];
    },
};

export default Service;
