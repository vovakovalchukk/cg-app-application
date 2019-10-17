const Validators = {
    name: (schedule) => schedule.name.toString().trim().length > 2,
    template: (schedule) => !!schedule.templateId,
    fromAccount: (schedule) => {
        if (!schedule.fromDataExchangeAccountType) {
            return false;
        }

        if (!schedule.fromDataExchangeAccountId) {
            return false;
        }

        if (schedule.fromDataExchangeAccountType == 'email' && !schedule.toDataExchangeAccountId) {
            return false;
        }

        return true;
    },
    filename: (schedule) => schedule.filename.toString().trim().length > 2,
    frequency: (schedule) => {
        if (!schedule.frequency) {
            return false;
        }

        switch (schedule.frequency) {
            case 'hourly':
                return schedule.hour !== null && schedule.hour !== undefined && Number.isInteger(schedule.hour);
            case 'weekly':
                return schedule.day !== null && schedule.day !== undefined && schedule.day > 0;
            case 'monthly':
                return schedule.date !== null && schedule.date !== undefined && schedule.date > 0;
            default:
                return false;
        }
    },
    savedFilter: (schedule) => !!schedule.savedFilterName,
    action: (schedule) => !!schedule.action
};

export default Validators;