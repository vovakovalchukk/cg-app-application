const scheduleReducer = (state, action) => {
    switch (action.type) {
        case 'addNewSchedule': {
            const newState = [...state];
            newState.push(action.payload.schedule);
            return newState;
        }
        case 'updateInputValue': {
            const newState = [...state];
            newState[action.payload.index] = {
                ...newState[action.payload.index],
                [action.payload.property]: action.payload.newValue
            };
            return newState;
        }
        case 'scheduleDeletedSuccessfully': {
            const newState = [...state];
            newState.splice(action.payload.index, 1);
            return newState;
        }
        case 'scheduleSavedSuccessfully': {
            const newState = [...state];
            newState[action.payload.index] = {
                ...newState[action.payload.index],
                etag: action.payload.response.etag
            };
            return newState;
        }
        default: {
            throw new Error('No valid action passed to the schedule reducer');
        }
    }
};

export default scheduleReducer;
