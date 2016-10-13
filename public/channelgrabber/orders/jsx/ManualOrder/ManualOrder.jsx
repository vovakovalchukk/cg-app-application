define([
    'react',
    'react-dom',
    'ManualOrder/Components/Root',
    'Common/Components/Notes/Root'
], function(
    React,
    ReactDOM,
    RootComponent,
    NoteComponent
) {
    var ManualOrder = function(mountingNodes, utilities, currentUser)
    {
        this.manualOrderRef = ReactDOM.render(<RootComponent utilities={utilities}/>, mountingNodes.productInfo);
        this.noteRef = ReactDOM.render(<NoteComponent author={currentUser}/>, mountingNodes.orderNotes);

        this.collectFormData = function() {

        };

        this.submitFormData = function (formData) {
            console.log(formData);
        };

        this.listenForCreateOrderAction = function()
        {
            var self = this;
            $('#create-order-button').click(function(e) {
                var formData = self.collectFormData();
                self.submitFormData(formData);
            });
        };

        var init = function() {
            this.listenForCreateOrderAction();
        };
        init.call(this);
    };

    return ManualOrder;
});