define(['react', 'react-redux', 'Redux/Actions/Todo', 'Redux/Components/AddTodo'], function (React, ReactRedux, Actions, AddTodoComponent) {
    var mapStateToProps = null;

    var mapDispatchToProps = {
        onAddClick: Actions.add
    };

    var AddTodoConnector = ReactRedux.connect(mapStateToProps, mapDispatchToProps);

    return AddTodoConnector(AddTodoComponent);
});
