define([
    'react'
], function(
    React
) {
    "use strict";

    var NoteComponent = React.createClass({
        getInitialState: function () {
            return {
                newContent: "",
                editing: false
            };
        },
        getDefaultProps: function () {
            return {
                data: {}
            }
        },
        onToggleEditMode: function (e) {
            this.setState({
                newContent: this.props.data.note,
                editing: !this.state.editing
            });
        },
        onEditInput: function (e) {
            this.setState({
                newContent: e.target.value
            });
        },
        onDelete: function (e) {
            this.props.onDelete(this.props.data.id, this.props.data.eTag)
        },
        onSaveChanges: function (e) {
            this.setState({
                editing: false
            }, function () {
                this.props.onEdit(this.props.data.id, this.state.newContent, this.props.data.eTag)
            });
        },
        getBody: function () {
            if (this.state.editing) {
                return (
                    <div className="note-body">
                        <textarea value={this.state.newContent} onChange={this.onEditInput} />
                        <button className="save button" onClick={this.onSaveChanges}>Save</button>
                    </div>
                );
            }

            return (
                <div className="note-body">
                    <div className="note-content">{this.props.data.note}</div>
                </div>
            );
        },
        getTimestamp: function () {
            if (! Number.isInteger(this.props.data.timestamp)) {
                return this.props.data.timestamp;
            }
            var date = new Date(this.props.data.timestamp);
            var minute = (date.getMinutes() < 10 ? '0' : '') + date.getMinutes();
            var hour = (date.getHours() < 10 ? '0' : '') + date.getHours();
            var day = date.getDate();
            var month = date.getMonth() + 1;
            var year = date.getFullYear();
            return day + "/" + month + "/" + year + " " + hour + ":" + minute;
        },
        render: function () {
            return (
                <div className="note clearfix noteEntity">
                    <span className="sprite-delete-20-black delete" onClick={this.onDelete}></span>
                    <span className="sprite-write-20-black edit-note" onClick={this.onToggleEditMode}></span>
                    <h3>{this.props.data.author} - {this.getTimestamp()}</h3>
                    {this.getBody()}
                </div>
            );
        }
    });

    return NoteComponent;
});
