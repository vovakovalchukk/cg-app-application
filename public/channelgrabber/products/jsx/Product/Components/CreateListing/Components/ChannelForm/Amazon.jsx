import React from 'react';
import {Field, FieldArray} from 'redux-form';
import TextFieldArray from 'Product/Components/CreateListing/Components/CreateListing/TextFieldArray';
import ConditionNote from 'Product/Components/ConditionNote';
import SearchTerms from 'Product/Components/CreateListing/Components/ChannelForm/Amazon/SearchTerms';
import Validators from 'Product/Components/CreateListing/Validators';

class AmazonChannelFormComponent extends React.Component {
    render() {
        return (
            <section>
                {this.renderConditionNoteField()}

                {this.renderBulletPointFields()}

                {this.renderSearchTermsField()}
            </section>
        );
    }
    renderSearchTermsField = () => {
        return <div className="amazon-channel-form-container channel-form-container">
            <Field
                name="searchTerms"
                component={ConditionNote}
                displayTitle="Search Terms"
                validate={Validators.maxLength(500)}
            />
        </div>;
    };
    renderBulletPointFields() {
        return <div className="amazon-channel-form-container channel-form-container">
            <FieldArray
                component={TextFieldArray}
                name="bulletPoint"
                displayTitle="Bullet Points"
                itemPlaceholder={"bullet"}
                itemLimit={5}
                maxCharLength={500}
                identifier={'bullet'}
            />
        </div>;
    }
    renderConditionNoteField() {
        return <div className="amazon-channel-form-container channel-form-container">
            <Field name="conditionNote" component={ConditionNote} displayTitle="Condition note"/>
        </div>;
    }
}

export default AmazonChannelFormComponent;