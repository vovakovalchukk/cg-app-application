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
            this.getNewNote();
        },
        onEditNote: function (id, newContent) {
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
        onDeleteNote: function (id) {
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
        onNoteInput: function (e) {
            this.setState({
                noteInput: e.target.value
            });
        },
        getNewNote: function () {
            var notes = this.state.notes.slice();
            var newId = this.state.currentId + 1;

            var newNote = {
                id: newId,
                content: this.state.noteInput,
                timestamp: Date.now(),
                author: this.props.author
            };
            notes.push(newNote);
            this.setState({
                notes: notes,
                noteInput: "",
                currentId: newId
            });
            return newNote;
        },
        render: function () {
            return (
                <div className="note-root">
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
