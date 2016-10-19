define([
    'react',
    'Common/Components/Notes/Note',
    'Common/Components/Notes/Compose'
], function(
    React,
    Note,
    Compose
) {
    "use strict";

    var RootComponent = React.createClass({
        getInitialState: function () {
            return {
                notes: this.props.existingNotes
            }
        },
        getDefaultProps: function () {
            return {
                existingNotes: []
            }
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
        addNoteToList: function (newNote) {
            var notes = this.state.notes.slice();
            notes.push(newNote);
            this.setState({
                notes: notes
            });
            return newNote;
        },
        editNoteInList: function (id, newContent) {
            var notes = this.state.notes.map(function (note) {
                if (note.id === id) {
                    note.note = newContent;
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
                    <Compose orderId={this.props.orderId} addNoteToList={this.addNoteToList}/>
                </div>
            );
        }
    });

    return RootComponent;
});
