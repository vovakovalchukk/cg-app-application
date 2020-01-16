import React from 'react';
import styled from 'styled-components';
import ButtonSelect from 'Common/Components/ButtonSelect';
import Textarea from 'MessageCentre/Components/Textarea';

const StyledButtonSelect = styled(ButtonSelect)`
    width: 16rem;
    margin-bottom: 6rem;
`

const LaggyTextArea = styled.textarea`
    width: 100%;
    height: 20rem;
    resize: vertical;
    box-sizing: border-box;
    padding: 1rem;
`;

const StyledTextarea = styled(Textarea)`
    width: 100%;
    height: 20rem;
    resize: vertical;
    box-sizing: border-box;
    padding: 1rem;
`;

const ReplyBox = (props) => {
    const {actions, thread, reply} = props;

    const options = [
        {
            name: 'Send and Resolve',
            id: 'send-and-resolve',
            action: actions.sendAndResolve,
        },
        {
            name: 'Send',
            id: 'send',
            action: actions.addMessage,
        },
    ];

    return (
        <div>

            <StyledTextarea
                className={`u-margin-top-med`}
                placeholder={'Compose your reply here'}
                id={`composedReply`}
            />

            {/*<LaggyTextArea*/}
            {/*    onChange={actions.replyInputType}*/}
            {/*    className={`u-margin-top-med`}*/}
            {/*    placeholder={'Compose your reply here'}*/}
            {/*/>*/}

            <div className={`u-clear-both u-margin-top-med`}>
                {thread.status === 'resolved' &&
                    <button
                        type={`button`}
                        onClick={actions.addMessage}
                    >Send</button>
                }
                {thread.status !== 'resolved' &&
                    <StyledButtonSelect
                        options={options}
                        ButtonTitle={() => (
                            <span>{reply.buttonSelectTitle}</span>
                        )}
                        spriteClass={'sprite-email-20-dblue'}
                        multiSelect={false}
                        onButtonClick={(id)=> {
                            let option = options.find(x => x.id === id[0]);
                            if (!option) option = options[0];
                            option.action();
                        }}
                        onSelect={(id) => {
                            const option = options.find(x => x.id === id[0]);
                            actions.replyOptionSelected(option.name);
                        }}
                    />
                }
            </div>
        </div>
    );
};

export default ReplyBox;