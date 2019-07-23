import React, {useState} from "react";
import Select from 'Common/Components/Select';
import styled from 'styled-components';

const TextArea = styled.textarea`
  width: 600px;
  min-height: 300px;   
  resize: both;
`;

function TemplateEditor(props) {
    const formattedTemplateTags = formatTemplateTags(props.listingTemplateTags);

    const [textEditorPosition, setTextEditorPosition] = useState();
    const [tagSelectValue, setTagSelectValue] = useState(formattedTemplateTags[0]);
    
    return (<div className={"u-margin-top-med"}>
        <h3>Template Designer</h3>

        <div className={"u-margin-top-small u-flex-v-center"}>
            <Select
                autoSelectFirst={true}
                filterable={true}
                title={'choose your tag'}
                selectedOption={tagSelectValue}
                onOptionChange={(option) => {
                    setTagSelectValue(option);
                }
                }
                options={formattedTemplateTags}
            />
            <button onClick={() => {
                props.templateHTML.setTag(tagSelectValue.tag, textEditorPosition)
            }}
                className={'button u-margin-left-small'}
            >
                Add tag
            </button>
        </div>
        <fieldset className={"u-margin-top-small"}>
            <TextArea
                value={props.templateHTML.value}
                onChange={props.templateHTML.onChange}
                onBlur={(e) => {
                    setTextEditorPosition(e.target.selectionStart);
                }
                }
            />
        </fieldset>
    </div>);
}

TemplateEditor.defaultProps = {
    templateHTML: {}
};

export default TemplateEditor;

function formatTemplateTags(listingTemplateTags){
    if(!listingTemplateTags || !Array.isArray(listingTemplateTags)){
        return [];
    }
    return listingTemplateTags.map(tag => {
        return {
            ...tag,
            value: tag.tag,
            name: tag.tag
        };
    });
}