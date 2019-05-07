import React from 'react';
import {Field, FieldArray} from 'redux-form';
import TextArea from 'Common/Components/TextArea';
import TextFieldArray from 'Product/Components/CreateListing/Components/CreateListing/TextFieldArray';


const ConditionNote = (field) => {
    return <fieldset className="input-container">
        <span className={"inputbox-label"}>{field.displayTitle}</span>
        <div className={"order-inputbox-holder"}>
                <TextArea
                    {...field.input}
                    className={"textarea-description"}
                />
        </div>
    </fieldset>;
};

class AmazonChannelFormComponent extends React.Component {
    render() {
        return (
            <section>
                <div className="amazon-channel-form-container channel-form-container">
                    <Field name="conditionNote" component={ConditionNote} displayTitle="Condition note"/>
                </div>

                <div className="amazon-channel-form-container channel-form-container">
                    <FieldArray
                        component={TextFieldArray}
                        name="bulletPoint"
                        displayTitle="Bullet Points"
                        itemPlaceholder={"bullet"}
                        itemLimit={5}
                        maxCharLength={500}
                    />
                </div>

                <div className="amazon-channel-form-container channel-form-container">
                    <FieldArray
                        component={TextFieldArray}
                        name="searchTerm"
                        displayTitle="Search Terms"
                        itemPlaceholder={"search term"}
                        itemLimit={5}
                        maxCharLength={250}
                    />
                </div>
            </section>
        );
    }
}

export default AmazonChannelFormComponent;