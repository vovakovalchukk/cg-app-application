define([
    'redux-form',
    'Redux/Components/ContactForm'
], function(
    ReduxForm,
    ContactFormComponent
) {
    var contactFormCreator = ReduxForm.reduxForm({
        form: "contactForm",
        initialValues: {
            customFields: [{name: "", value: ""}]
        }
    });
    var ContactFormContainer = contactFormCreator(ContactFormComponent);
    return ContactFormContainer;
});