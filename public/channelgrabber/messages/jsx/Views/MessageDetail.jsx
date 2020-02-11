import React, { useEffect } from 'react';
import styled from 'styled-components';
import ButtonLink from 'MessageCentre/Components/ButtonLink';
import ThreadHeader from 'MessageCentre/Components/ThreadHeader';
import ShadowDomDiv from 'MessageCentre/Components/ShadowDomDiv';
import ReplyBox from 'MessageCentre/Components/ReplyBox';
import Select from 'Common/Components/Select';

const FlexDiv = styled.div`
    justify-content: space-between;
`;

const GridDiv = styled.div`
    display: grid;
    grid-template-columns: 1fr 200px;
    grid-gap: 1rem;
    min-height: 101vh;
`;

const MessageLi = styled.li`
    overflow: auto;
    padding-bottom: 1rem;
    border-bottom: 1px solid #ccc;
    margin-bottom: 1rem;
`;

const StyledSelect = styled.select`
    display: flex;
    max-width: 260px;
    width: 100%;
    margin: 0.5rem 0 1rem;
    background-color: #fff;
    border: 1px solid #c2c2c2;
    border-radius: 5px;
`;

const FlexColumn = styled.div`
    display: flex;
    flex-direction: column;
    background-color: rgb(239,238,238);
    padding: 1rem;
`;

const FlexAlignItemsCenter = styled.div`
    align-items: center;
`;

const printMessage = (message) => {
    const newWindow = window.open();
    newWindow.document.write(message.body);
    newWindow.print();
    newWindow.close();
};

const formatMessages = (thread, allMessages) => {
    const formattedMessages = [];
    thread.messages.forEach(messageId => {
        const message = allMessages.byId[messageId];
        formattedMessages.push(message);
    });
    return formattedMessages;
};

const getPersonSprite = (person) => {
    const staff = 'sprite-message-staff-21-blue';
    const customer = 'sprite-message-customer-21-red';
    return person === 'staff' ? staff : customer;
};

const formatUsers = (users) => {
    const formattedUsers = [];

    formattedUsers.push({
        value: null,
        name: 'Assign',
    });

    Object.entries(users).forEach(user => {
        formattedUsers.push({
            value: user[0],
            name: user[1],
        });
    });

    return formattedUsers;
};

const MessageDetail = (props) => {
    const {match, threads, actions, assignableUsers} = props;
    const {params} = match;
    const threadId = params.threadId.replace(':','');
    const thread = threads.byId[threadId];
    const messages = formatMessages(thread, props.messages);
    const headerProps = {
        thread: thread,
        threadIds: threads.allIds,
    }

    useEffect(() => {
        props.threads.viewing = thread.id;
    }, []);

    const formattedUsers = formatUsers(assignableUsers);

    const findAssignedUser = () => (
        formattedUsers.find(user => {
            return user.value === thread.assignedUserId;
        })
    );

    return (
        <GridDiv>
            <div className={`u-padding-left-small`}>
                <ThreadHeader {...headerProps} />
                <ol className={`u-padding-none`}>
                    {messages.map((message, index) => {
                        return (
                            <MessageLi key={message.id}>
                                <FlexDiv className={`u-display-flex`}>
                                    <FlexAlignItemsCenter className={`u-display-flex`}>
                                        <div className={getPersonSprite(message.personType)} />
                                        <div className={`u-margin-left-xsmall`}>{message.name}</div>
                                    </FlexAlignItemsCenter>
                                    <FlexAlignItemsCenter className={`u-display-flex`}>
                                        <div>{message.created}</div>
                                        <button
                                            className={`u-margin-left-xsmall`}
                                            type="button"
                                            onClick={() => printMessage(message)}
                                        >
                                            Print Message
                                        </button>
                                    </FlexAlignItemsCenter>
                                </FlexDiv>
                                <ShadowDomDiv body={message.body} />
                                <ReplyBox
                                    actions={actions}
                                    thread={thread}
                                />
                            </MessageLi>
                        )
                    })}
                </ol>
            </div>

            <FlexColumn>
                <label className={'heading-medium u-cursor-pointer'}>
                    Status:
                    <StyledSelect
                        value={thread.status}
                        onChange={props.actions.saveStatus}
                    >
                        <option value={'awaiting reply'}>Awaiting Reply</option>
                        <option value={'resolved'}>Resolved</option>
                        <option value={'new'}>New</option>
                    </StyledSelect>
                </label>
                <ButtonLink
                    className={`u-margin-bottom-small`}
                    to={thread.ordersLink}
                    text={`${thread.ordersCount} Orders from ${thread.externalUsername}`}
                />
                {formattedUsers.length > 2 ?
                    <Select
                        id={"assignableUserSelect"}
                        name={"assignableUserSelect"}
                        options={formattedUsers}
                        filterable={true}
                        autoSelectFirst={false}
                        selectedOption={findAssignedUser()}
                        onOptionChange={(option) => props.actions.assignThreadToUser(option.value)}
                        classNames={'u-width-100pc'}
                    />
                : null}
            </FlexColumn>
        </GridDiv>
    );
};

export default MessageDetail;
