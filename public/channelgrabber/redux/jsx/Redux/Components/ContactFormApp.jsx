define([
    'react',
    'Redux/Containers/ContactForm'
], function(
    React,
    ContactFormContainer
) {
    "use strict";

    var ContactFormApp = React.createClass({
        contactSubmit: function(values) {
            console.log(values);
        },
        render: function()
        {
            return (
                <div>
                    <h1>Contact Form</h1>
                    <ContactFormContainer onSubmit={this.contactSubmit} />
                </div>
            );
        }
    });

    return ContactFormApp;
});