define([
    'react'
], function(
    React
) {
    "use strict";

    var ComposeComponent = React.createClass({
        getDefaultProps: function () {
            return {
                author: "N/A"
            }
        },
        getInitialState: function () {
            return {
                currentId: 0,
                noteInput: ""
            };
        },
        onCreateNote: function (e) {
            if (! this.state.noteInput.length) {
                return;
            }
            var newNote = this.createNewNote();
            if (this.props.orderId == undefined) {
                this.onSuccessfulNoteCreation(newNote);
                return;
            }

            n.notice("Creating Note...");
            $.ajax({
                url: '/orders/' + this.props.orderId + '/note/create',
                type: 'POST',
                data: {
                    'note': newNote.note
                },
                dataType: 'json',
                success: function (data) {
                    this.onSuccessfulNoteCreation(data.note);
                }.bind(this),
                error: function (error, textStatus, errorThrown) {
                    n.ajaxError(error, textStatus, errorThrown);
                }
            });
        },
        onSuccessfulNoteCreation: function (newNote) {
            this.setState({
                noteInput: ""
            }, function() {
                this.props.onNoteCreated(newNote);
                n.success("Note created.");
            });
        },
        createNewNote: function () {
            var newId = this.state.currentId + 1;
            this.setState({
                currentId: newId
            });

            return {
                id: newId,
                note: this.state.noteInput,
                timestamp: Date.now(),
                author: this.props.author
            };
        },
        onNoteInput: function (e) {
            this.setState({
                noteInput: e.target.value
            });
        },
        render: function () {
            return (
                <div className="note-form">
                    <textarea value={this.state.noteInput} onChange={this.onNoteInput}/>
                    <button className={"save button" + (this.state.noteInput.length ? "" : " disabled ")} onClick={this.onCreateNote}>Create note</button>
                </div>
            );
        }
    });

    return ComposeComponent;
});
