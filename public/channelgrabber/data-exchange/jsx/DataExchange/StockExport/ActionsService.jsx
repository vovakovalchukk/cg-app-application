const ActionsService = {
    saveSchedule: async (schedule) => {
        const postData = {
            ...schedule,
            toDataExchangeAccountId: `${schedule.toDataExchangeAccountType}-${schedule.toDataExchangeAccountId}`
        };

        return $.ajax({
            url: window.location.href + '/save',
            type: 'POST',
            dataType: 'json',
            data: postData,
            success: (response) => response,
            error: (error) => error
        });
    },
    deleteSchedule: async (id) => {
        return $.ajax({
            url: window.location.href + '/remove',
            type: 'POST',
            dataType: 'json',
            data: {id},
            success: (response) => response,
            error: (error) => error
        });
    },
};

export default ActionsService;
