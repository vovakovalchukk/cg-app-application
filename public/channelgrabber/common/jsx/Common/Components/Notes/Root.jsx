define([
    'react',
    'Common/Components/Notes/Note'
], function(
    React,
    Note
) {
    "use strict";

    var RootComponent = React.createClass({
        getInitialState: function () {
            return {
                currentId: 0,
                noteInput: "",
                notes: []
            }
        },
        onCreateNote: function (e) {
            var newNote = this.createNewNote();
            if (this.props.orderId == undefined) {
                this.addNoteToList(newNote);
                n.success("Note created.");
                return;
            }

            n.notice("Creating Note...");
            $.ajax({
                url: '/orders/' + this.props.orderId + '/note/create',
                type: 'POST',
                data: {
                    'note': newNote.content
                },
                dataType: 'json',
                success: function (data) {
                    this.addNoteToList(newNote);
                    n.success("Note created.");
                }.bind(this),
                error: function (error, textStatus, errorThrown) {
                    n.ajaxError(error, textStatus, errorThrown);
                }
            });
        },
        onEditNote: function (id, newContent, eTag) {
            if (this.props.orderId === undefined) {
                this.editNoteInList(id, newContent);
                n.success("Note saved.");
                return;
            }

            n.notice("Saving note...");
            $.ajax({
                url: '/orders/' + this.props.orderId + '/note/update',
                type: 'POST',
                data: {
                    'eTag': eTag,
                    'noteId': id,
                    'note': newContent
                },
                dataType: 'json',
                success: function(data) {
                    this.editNoteInList(id, newContent);
                    n.success("Note saved.");
                }.bind(this),
                error: function (error, textStatus, errorThrown) {
                    n.ajaxError(error, textStatus, errorThrown);
                }
            });
        },
        onDeleteNote: function (id, eTag) {
            if (this.props.orderId === undefined) {
                this.deleteNoteFromList(id);
                n.success("Note deleted.");
                return;
            }

            n.notice("Deleting note...");
            $.ajax({
                url: '/orders/' + this.props.orderId + '/note/delete',
                type: 'POST',
                data: {
                    'eTag': eTag,
                    'noteId': id
                },
                dataType: 'json',
                success: function (data) {
                    this.deleteNoteFromList(id);
                    n.success("Note deleted.");
                }.bind(this),
                error: function (error, textStatus, errorThrown) {
                   n.ajaxError(error, textStatus, errorThrown);
                }
            });
        },
        onNoteInput: function (e) {
            this.setState({
                noteInput: e.target.value
            });
        },
        createNewNote: function () {
            var newId = this.state.currentId + 1;
            this.setState({
                currentId: newId
            });

            return {
                id: newId,
                content: this.state.noteInput,
                timestamp: Date.now(),
                author: this.props.author
            };
        },
        addNoteToList: function (newNote) {
            var notes = this.state.notes.slice();
            notes.push(newNote);
            this.setState({
                notes: notes,
                noteInput: ""
            });
            return newNote;
        },
        editNoteInList: function (id, newContent) {
            var notes = this.state.notes.map(function (note) {
                if (note.id === id) {
                    note.content = newContent;
                }
                return note;
            });
            this.setState({
                notes: notes
            });
        },
        deleteNoteFromList: function (id) {
            var notes = this.state.notes.slice();
            for (var i = 0; i < notes.length; i++) {
                if(notes[i].id === id) {
                    notes.splice(i, 1);
                }
            }
            this.setState({
                notes: notes
            });
        },
        render: function () {
            return (
                <div className="note-root">
                    <span className="heading-large heading-spacing">Notes</span>
                    <div className="note-list">
                        {this.state.notes.map(function (note) {
                            return <Note data={note} onDelete={this.onDeleteNote} onEdit={this.onEditNote} />;
                        }.bind(this))}
                    </div>
                    <div className="note-form">
                        <textarea value={this.state.noteInput} onChange={this.onNoteInput}/>
                        <button className="save button" onClick={this.onCreateNote}>Create note</button>
                    </div>
                </div>
            );
        }
    });

    return RootComponent;
});
