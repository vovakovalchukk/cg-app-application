define([
    'redux-form',
    'Redux/Components/ContactForm'
], function(
    ReduxForm,
    ContactFormComponent
) {
    var contactFormCreator = ReduxForm.reduxForm({form: "contactForm"});
    var ContactFormContainer = contactFormCreator(ContactFormComponent);
    return ContactFormContainer;
});