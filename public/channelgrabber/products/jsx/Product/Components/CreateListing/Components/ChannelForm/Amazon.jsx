import React from 'react';
import {Field, FieldArray} from 'redux-form';
import TextFieldArray from 'Product/Components/CreateListing/Components/CreateListing/TextFieldArray';
import ConditionNote from 'Product/Components/ConditionNote';

class AmazonChannelFormComponent extends React.Component {
    render() {
        return (
            <section>
                {this.renderConditionNoteField()}

                {this.renderBulletPointFields()}

                {this.renderSearchTermFields()}
            </section>
        );
    }
    renderSearchTermFields() {
        return <div className="amazon-channel-form-container channel-form-container">
            <FieldArray
                component={TextFieldArray}
                name="searchTerm"
                displayTitle="Search Terms"
                itemPlaceholder={"search term"}
                itemLimit={5}
                maxCharLength={250}
                identifier={'searchTerm'}
            />
        </div>;
    }
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