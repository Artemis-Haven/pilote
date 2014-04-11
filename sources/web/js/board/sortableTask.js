/**
 * Rend les tâches et les listes de tâches triables
 */
function setSortableTask() {
    $( ".sortableTasksContainer" ).sortable({
        connectWith: ".sortableTasksContainer",
        items: ".task , .blankTask",
        handle: ".task-header",
        placeholder: "task-placeholder",
        revert:true
    });
    $( ".task" ).disableSelection();

    $( ".tab-pane" ).sortable({
        items: ".taskList",
        handle: ".task-header",
        handle: ".panel-heading",
        revert:true
    });
};

setSortableTask();