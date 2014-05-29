/**
 * Rend les tâches et les listes de tâches triables
 */
function setSortableTask() {
    $( ".sortableTasksContainer" ).sortable({
        connectWith: ".sortableTasksContainer",
        items: ".task , .blankTask",
        handle: ".task-header",
        placeholder: "task-placeholder",
        revert:true,
      	update: function(event, ui) {
          	taskId = ui.item.attr('id').replace('task-', '');
          	oldListId = 0;
          	oldPosition = 0;
          	newListId = 0;
          	newPosition = ui.item.index();
      		alert("Tache d'id "+taskId+" en position "+oldPosition+" dans la liste "+oldListId+" a bougé dans la liste "+newListId+" position "+newPosition);
      	}
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