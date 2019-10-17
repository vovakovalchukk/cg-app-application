const Helper = {
    hasScheduleChanged: (schedule) => {
        const scheduleCopy = {...schedule};
        delete scheduleCopy.initialValues;
        return !(Object.keys(scheduleCopy).reduce((isEqual, key) => {
            return isEqual && (scheduleCopy[key] === schedule.initialValues[key]);
        }, true));
    },
    validateSchedule: (schedule, validators) => {
        return validators.map((validatorCallback) => {
            return validatorCallback(schedule);
        });
    }
};

export default Helper;