import React, {useState} from "react";
import Select from 'Common/Components/Select';

function TemplateEditor(props) {

    const formattedTemplateTags = props.listingTemplateTags.map(tag => {
        return {
            ...tag,
            value: tag.tag,
            name: tag.tag
        };
    });

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
                className={'u-margin-left-small'}
            >
                add tag
            </button>
        </div>
        <div className={"u-margin-top-small"}>
            <textarea
                style={{width: "600px", minHeight: '300px'}}
                value={props.templateHTML.value}
                onChange={props.templateHTML.onChange}
                onBlur={(e) => {
                    setTextEditorPosition(e.target.selectionStart);
                }
                }
            />
        </div>
    </div>);
}

TemplateEditor.defaultProps = {
    templateHTML: {}
};

export default TemplateEditor;