import React from 'react';
import styled from 'styled-components';
import ButtonSelect from 'Common/Components/ButtonSelect';
import { connect } from 'react-redux';

const mapStateToProps = state => {
    return {
        reply: state.reply
    }
};

const buttonWidth = '16rem';

const SimpleButton = styled.button`
    width: ${buttonWidth} !important;
`;

const ComplexButton = styled(ButtonSelect)`
    width: ${buttonWidth};
    margin-bottom: 1rem;
`;

const StyledTextarea = styled.textarea`
    flex-grow: 1;
    height: 14rem;
    resize: none;
    box-sizing: border-box;
    padding: 1rem;
    margin-right: 1rem;
`;

const FlexDiv = styled.div`
    display: flex;
    flex-direction: column;
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

    const isThreadResolved = thread.status === 'resolved';

    return (
        <FlexDiv>
            <div>
                {isThreadResolved ?
                    <SimpleButton
                        type={`button`}
                        onClick={actions.addMessage}
                        className={'button'}
                    >
                        Send reply
                    </SimpleButton>
                    :
                    <ComplexButton
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
                            actions.replyOptionSelect(option.name);
                        }}
                    />
                }
            </div>
            <StyledTextarea
                value={reply.text}
                onChange={actions.replyOnChange}
                placeholder={'Compose your reply here'}
            />
        </FlexDiv>
    );
};

export default connect(mapStateToProps)(ReplyBox);
