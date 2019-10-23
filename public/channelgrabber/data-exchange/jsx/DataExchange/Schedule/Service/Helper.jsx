const Helper = {
    hasScheduleChanged: (schedule) => {
        const scheduleCopy = {...schedule};
        delete scheduleCopy.initialValues;
        return !(Object.keys(scheduleCopy).reduce((isEqual, key) => {
            return isEqual && (scheduleCopy[key] === schedule.initialValues[key]);
        }, true));
    },
    validateSchedule: (schedule, validators) => {
        return validators.reduce((isValid, validatorCallback) => {
            return isValid && validatorCallback(schedule);
        }, true);
    }
};

export default Helper;