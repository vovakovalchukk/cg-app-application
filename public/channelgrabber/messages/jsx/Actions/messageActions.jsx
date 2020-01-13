const messageActions = {
    fetchMessages: (params) => {
        return async function (dispatch, getState) {
            let response = await fetchThreads(params, getState());
            dispatch({
                type: 'THREADS_FETCH_SUCCESS',
                payload: response.threads,
            })
        };
    },
    addMessage: (params) => {
        return async function (dispatch, getState) {
            let response = await addMessage(params, getState());
            dispatch({
                type: 'ADD_MESSAGE_SUCCESS',
                payload: response,
            })
        }
    },
    saveStatus: (params) => {
        return async function (dispatch, getState) {
            let response = await saveStatus(params, getState());
            dispatch({
                type: 'SAVE_STATUS_SUCCESS',
                payload: response,
            })
        }
    },
};

export default messageActions;

function fetchThreads(params, state) {
    const {filter} = params;

    const newFilter = {
        ...state.filter,
        ...filter
    };

    const newParams = {...params};
    delete newParams.filter;

    return $.ajax({
        url: '/messages/ajax',
        type: 'POST',
        data: {
            filter: newFilter, // TODO - see below
            page: 1, // TODO - pagination
            sortDescending: true, // TODO - date column sort order
            ...newParams
        }
    });
}

function addMessage(params, state) {
    const {threads} = state;

    const threadId = threads.viewing;

    const body = threads.replyText;

    const fakeResponse = {
        "messageEntity": {
            "id": "9-999999999999",
            "organisationUnitId": 2,
            "accountId": 1,
            "created": "10/01/20 10:01",
            "name": "Fake Response",
            "externalUsername": "eBay",
            "body": body,
            "threadId": threadId,
            "createdFuzzy": "0 days ago",
            "personType": "customer"
        }
    };

    return fakeResponse;

    // todo - remove the fake response when ready to test with real data
    return $.ajax({
        url: '/messages/ajax/addMessage',
        type: 'POST',
        data: {
            threadId: threadId,
            body: body
        }
    });
}

function saveStatus(params, state) {
    // todo:
    //   get the currently viewed thread id
    //   get the status chosen in the dropdown (resolved/awaiting reply/new)

    const threadId = '1-6d5f9a764ed0e67c196d2cdc3498a0d8aea56f32';
    const status = 'resolved';

    // the response will be the thread again (but with updated status)
    const fakeResponse = {
        "id": threadId,
        "channel": "ebay",
        "organisationUnitId": 2,
        "accountId": 1,
        "status": status,
        "created": "04/12/19 23:32",
        "updated": "04/12/19 23:32",
        "name": "eBay",
        "externalUsername": "eBay",
        "assignedUserId": null,
        "subject": "Your eBay invoice for November is now ready to view",
        "externalId": "",
        "messages": [
            "1-118607708187"
        ],
        "accountName": "wltd4371",
        "createdFuzzy": "1 month ago",
        "updatedFuzzy": "1 month ago",
        "ordersLink": "/orders?search=eBay&searchField%5B0%5D=order.externalUsername",
        "ordersCount": "?",
        "assignedUserName": "",
        "lastMessage": "\n\n\nThank you for using eBay, Michael Leung.\n\n\nThanks for using eBay! Here's your invoice.\n\nHi Michael Leung (wltd4371),Thanks for your business and for choosing eBay. Your eBay invoice for the period from 01 November 2019 through 30 November 2019 is now available to view in any web browser.\n\n\n\n\n\nTotal invoice amount due: -£0.54\n\n\n \n\n\n\n\n\nUseful information\nFee calculator \nFee illustrator\nLearn more about invoices\n\n\n Your account is up to date. No payment is required at this time.\n\n\n\n Automatic payment method: Credit cardNo payment due (credit or zero balance)\n\n\n"
    };

    return fakeResponse;

    return $.ajax({
        url: '/messages/ajax/save',
        type: 'POST',
        data: {
            threadId: threadId,
            status: status
        }
    });
}
