import React, { useState } from 'react';
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

const TemplateButton = styled(ButtonSelect)`
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

const formatTemplatesForPicker = (templates, actions) => {
    return Object.values(templates.byId).map(template => {
        return {
            name: template.name,
            id: template.id,
            action: () => {actions.replyTemplateSelect(template.template)},
        };
    });
};

const ReplyBox = (props) => {
    const {actions, thread, reply, templates} = props;
    const [templateOptions] = useState(formatTemplatesForPicker(templates, actions));
    const [templateOption, setTemplateOption] = useState(templateOptions[0] || null);

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

    const hasTemplateOptions = templateOptions.length > 0;

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
                {hasTemplateOptions &&
                    <TemplateButton
                        options={templateOptions}
                        ButtonTitle={() => (
                            <span>Use template: {templateOption.name}</span>
                        )}
                        spriteClass={'sprite-picklist-21-black'}
                        multiSelect={false}
                        onButtonClick={id => {
                            const option = id.length === 0 ? templateOptions[0] : templateOptions.find(x => x.id === id[0]);
                            option.action();
                        }}
                        onSelect={id => {
                            const option = templateOptions.find(x => x.id === id[0]);
                            setTemplateOption(option || options[0]);
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
