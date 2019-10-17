import Columns from "DataExchange/Schedule/Columns";

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
            templateId: null,
            toDataExchangeAccountId: null,
            toDataExchangeAccountType: null,
            savedFilterName: null
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
            Columns.savedFilters,
            Columns.actions
        ];
    },
    formatPostDataForSave: (schedule) => {
        return {
            ...schedule,
            toDataExchangeAccountId: `${schedule.toDataExchangeAccountType}-${schedule.toDataExchangeAccountId}`
        };
    },
    isScheduleValid: (schedule) => {
        if (schedule.name.toString().trim().length < 2) {
            return false;
        }

        if (!schedule.templateId) {
            return false;
        }

        if (!schedule.fromDataExchangeAccountType) {
            return false;
        }

        if (!schedule.fromDataExchangeAccountId) {
            return false;
        }

        if (schedule.fromDataExchangeAccountType == 'email' && !schedule.toDataExchangeAccountId) {
            return false;
        }

        if (schedule.filename.toString().trim().length < 2) {
            return false;
        }

        if (!schedule.frequency) {
            return false;
        }

        if (!schedule.savedFilterName) {
            return false;
        }

        return true;
    }
};

export default Service;