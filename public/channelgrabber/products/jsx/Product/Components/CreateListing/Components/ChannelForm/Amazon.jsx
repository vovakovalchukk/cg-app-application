import React from 'react';
import {Field} from 'redux-form';
import TextArea from 'Common/Components/TextArea';

class AmazonChannelFormComponent extends React.Component {
    renderConditionNote = (field) => {
        return <label className="input-container">
            <span className={"inputbox-label"}>{field.displayTitle}</span>
            <div className={"order-inputbox-holder"}>
                <TextArea
                    {...field.input}
                    className={"textarea-description"}
                />
            </div>
        </label>;
    };

    render() {
        console.log('in ChannelForm Amazon');
        
        
        return (
            <div className="amazon-channel-form-container channel-form-container">
                <Field name="conditionNote" component={this.renderConditionNote} displayTitle="Condition note"/>
            </div>
        );
    }
}

export default AmazonChannelFormComponent;
